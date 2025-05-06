<?php
require '../../connection/conn.php';

// Get parameters
$service_id = $_GET['service_id'] ?? null;
$date = $_GET['date'] ?? null;

if (!$service_id || !$date) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Missing parameters']);
    exit();
}

// Business hours configuration
$business_hours = [
    'start' => '09:00:00',
    'end' => '17:00:00',
    'closed_days' => ['Sunday']
];

// Get all booked slots for this service and date
$booked_slots_query = "SELECT TIME(BookingDate) as booked_time 
                      FROM bookings 
                      WHERE ServiceID = ? 
                      AND DATE(BookingDate) = ?
                      AND StatusID != 3";
$stmt = $conn->prepare($booked_slots_query);
$stmt->bind_param("is", $service_id, $date);
$stmt->execute();
$result = $stmt->get_result();

$booked_slots = [];
while ($row = $result->fetch_assoc()) {
    $booked_slots[] = $row['booked_time'];
}

// Check if date is a closed day
$day_name = date('l', strtotime($date));
$is_closed_day = in_array($day_name, $business_hours['closed_days']);

// Generate available slots
$available_slots = [];
$booked_slots_display = [];

if (!$is_closed_day) {
    $start_time = strtotime($date . ' ' . $business_hours['start']);
    $end_time = strtotime($date . ' ' . $business_hours['end']);
    
    // Create time slots (every hour)
    for ($time = $start_time; $time < $end_time; $time += 3600) {
        $slot_time = date('H:i:s', $time);
        $is_available = true;
        
        // Check if this slot is booked
        foreach ($booked_slots as $booked_time) {
            if (abs(strtotime($booked_time) - strtotime($slot_time)) < 3600) {
                $is_available = false;
                $booked_slots_display[] = [
                    'time' => date('h:i A', strtotime($booked_time))
                ];
                break;
            }
        }
        
        if ($is_available) {
            $available_slots[] = [
                'time' => date('h:i A', $time),
                'value' => date('H:i:s', $time)
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode([
    'available_slots' => $available_slots,
    'booked_slots' => $booked_slots_display,
    'is_closed_day' => $is_closed_day
]);