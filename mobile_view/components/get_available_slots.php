<?php
require '../../connection/conn.php';

$service_id = $_GET['service_id'] ?? null;
$date = $_GET['date'] ?? null;
$duration = $_GET['duration'] ?? 60; // Default to 60 minutes if not specified

if (!$service_id || !$date) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Service ID and date are required']);
    exit();
}

// Business hours configuration (should match your main file)
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
    $booked_slots[] = [
        'time' => $row['booked_time']
    ];
}

// Generate available slots
$available_slots = [];
$day_name = date('l', strtotime($date));

if (!in_array($day_name, $business_hours['closed_days'])) {
    $start_time = strtotime($date . ' ' . $business_hours['start']);
    $end_time = strtotime($date . ' ' . $business_hours['end']);
    
    // Create time slots based on duration
    for ($time = $start_time; $time <= ($end_time - $duration * 60); $time += 1800) { // 30-minute intervals
        $slot_time = date('H:i:s', $time);
        $is_available = true;
        
        // Check for conflicts with booked slots
        foreach ($booked_slots as $booking) {
            $booking_time = strtotime($booking['time']);
            $booking_end_time = $booking_time + $duration * 60;
            
            if ($time < $booking_end_time && ($time + $duration * 60) > $booking_time) {
                $is_available = false;
                break;
            }
        }
        
        if ($is_available) {
           // In the getAvailableSlots function, change these lines:
            $available_slots[] = [
                'time' => date('h:i A', $time), // Changed from 'H:i' to 'h:i A'
                'value' => date('H:i:s', $time) // Keep value in 24-hour format for database
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode([
    'available_slots' => $available_slots,
    'booked_slots' => $booked_slots
]);
?>