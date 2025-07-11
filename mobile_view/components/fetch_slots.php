<?php
require '../../connection/conn.php';

$service_id = $_GET['service_id'] ?? null;
$date = $_GET['date'] ?? null;

$response = ['available' => [], 'booked' => []];

if ($service_id && $date) {
    // Business hours
    $business_hours = [
        'start' => '09:00:00',
        'end' => '17:00:00',
        'closed_days' => ['Sunday']
    ];

    // Check if the selected date is a closed day
    $day_name = date('l', strtotime($date));
    if (in_array($day_name, $business_hours['closed_days'])) {
        echo json_encode($response);
        exit;
    }

    // Fetch all bookings for this date and service
    $stmt = $conn->prepare("SELECT TIME(BookingDate) as booked_time FROM bookings 
                            WHERE DATE(BookingDate) = ? AND ServiceID = ? AND StatusID != 3");
    $stmt->bind_param("si", $date, $service_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $booked_times = [];
    while ($row = $result->fetch_assoc()) {
        $booked_times[] = $row['booked_time'];
        $response['booked'][] = date('H:i', strtotime($row['booked_time']));
    }

    // Generate available slots
    $start_time = strtotime("$date " . $business_hours['start']);
    $end_time = strtotime("$date " . $business_hours['end']);
    $current_time = time();

    for ($time = $start_time; $time < $end_time; $time += 3600) {
        if ($date == date('Y-m-d') && $time < $current_time) continue;

        $slot_time = date('H:i:s', $time);
        if (!in_array($slot_time, $booked_times)) {
            $response['available'][] = [
                'time' => date('H:i', $time),
                'value' => $slot_time
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
