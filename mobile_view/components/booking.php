<?php
session_start();
require '../../connection/conn.php';

// Check if user is logged in
if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php");
    exit();
}

$CustomerID = $_SESSION['CustomerID'];

// Fetch all available services
$services = $conn->query("SELECT ServiceID, ServiceName FROM service");

// Get all booked dates from ALL users to show availability
$booked_slots_query = "SELECT 
    DATE(BookingDate) as booked_date, 
    TIME(BookingDate) as booked_time,
    ServiceID,
    COUNT(*) as booking_count
    FROM bookings 
    WHERE StatusID != 3  -- Exclude cancelled bookings
    GROUP BY DATE(BookingDate), TIME(BookingDate), ServiceID
    ORDER BY BookingDate";

$booked_slots_result = $conn->query($booked_slots_query);
$booked_slots = [];

while ($row = $booked_slots_result->fetch_assoc()) {
    $booked_slots[] = [
        'date' => $row['booked_date'],
        'time' => $row['booked_time'],
        'service_id' => $row['ServiceID'],
        'count' => $row['booking_count']
    ];
}

// Business hours configuration
$business_hours = [
    'start' => '09:00:00',
    'end' => '17:00:00',
    'closed_days' => ['Sunday'] // Days when business is closed
];

// Function to generate available time slots
function getAvailableSlots($service_id, $date, $booked_slots, $business_hours) {
    $available_slots = [];
    
    // Check if date is a closed day
    $day_name = date('l', strtotime($date));
    if (in_array($day_name, $business_hours['closed_days'])) {
        return []; // No available slots on closed days
    }
    
    $start_time = strtotime($date . ' ' . $business_hours['start']);
    $end_time = strtotime($date . ' ' . $business_hours['end']);
    
    // Create time slots (every hour)
    for ($time = $start_time; $time < $end_time; $time += 3600) {
        $slot_time = date('H:i:s', $time);
        $is_available = true;
        
        // Check if this slot is booked for the selected service
        foreach ($booked_slots as $booking) {
            if ($booking['service_id'] == $service_id && 
                $booking['date'] == $date && 
                abs(strtotime($booking['time']) - strtotime($slot_time)) < 3600) {
                $is_available = false;
                break;
            }
        }
        
        if ($is_available) {
            $available_slots[] = [
                'time' => date('H:i', $time),
                'value' => date('H:i:s', $time)
            ];
        }
    }
    
    return $available_slots;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service'] ?? null;
    $booking_date = $_POST['booking_date'] ?? null;
    $booking_time = $_POST['booking_time'] ?? null;

    if ($service_id && $booking_date && $booking_time) {
        $booking_datetime = date("Y-m-d H:i:s", strtotime("$booking_date $booking_time"));
        $status_id = 1; // "Pending"

        // Check if the user already has a future pending booking
        $existing_booking_query = "SELECT * FROM bookings 
                                 WHERE CustomerID = ? 
                                 AND BookingDate > NOW()
                                 AND StatusID = 1";
        $stmt_existing = $conn->prepare($existing_booking_query);
        $stmt_existing->bind_param("i", $CustomerID);
        $stmt_existing->execute();
        $result_existing = $stmt_existing->get_result();

        // Check if the customer already booked this service before
        $check_service_repeat_query = "SELECT * FROM bookings 
                                     WHERE CustomerID = ? 
                                     AND ServiceID = ? 
                                     AND StatusID IN (1, 2)";
        $stmt_repeat = $conn->prepare($check_service_repeat_query);
        $stmt_repeat->bind_param("ii", $CustomerID, $service_id);
        $stmt_repeat->execute();
        $result_repeat = $stmt_repeat->get_result();

        if ($result_repeat->num_rows > 0) {
            $_SESSION['message'] = 'You already have a booking for this service. Please wait until it is completed or cancelled.';
            $_SESSION['message_type'] = 'warning';
        } elseif ($result_existing->num_rows > 0) {
            $_SESSION['message'] = 'You already have a pending booking. Please complete or cancel it before booking again.';
            $_SESSION['message_type'] = 'warning';
        } else {
            // Check time slot conflict
            $check_conflict_query = "SELECT * FROM bookings WHERE ServiceID = ? AND BookingDate = ?";
            $stmt_conflict = $conn->prepare($check_conflict_query);
            $stmt_conflict->bind_param("is", $service_id, $booking_datetime);
            $stmt_conflict->execute();
            $result_conflict = $stmt_conflict->get_result();

            if ($result_conflict->num_rows > 0) {
                $_SESSION['message'] = 'This time slot is already booked. Please choose another time.';
                $_SESSION['message_type'] = 'warning';
            } else {
                $stmt = $conn->prepare("INSERT INTO bookings (CustomerID, ServiceID, BookingDate, StatusID) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iisi", $CustomerID, $service_id, $booking_datetime, $status_id);

                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Booking successfully created!';
                    $_SESSION['message_type'] = 'success';
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $_SESSION['message'] = 'Failed to book. Please try again.';
                    $_SESSION['message_type'] = 'danger';
                }
            }
        }
    } else {
        $_SESSION['message'] = 'All fields are required.';
        $_SESSION['message_type'] = 'warning';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include './semantic/head.php'; ?>
<body>
<?php include './semantic/navbar.php'; ?>

<div class="container mt-5">
    <h3 class="mb-4">Book a Car Wash Service</h3>
    
    <!-- Flash message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?>">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <form method="POST" action="" id="bookingForm">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="service" class="form-label">Select Service:</label>
                    <select name="service" id="service" class="form-control" required>
                        <option value="">-- Choose a Service --</option>
                        <?php if ($services->num_rows > 0): ?>
                            <?php while ($row = $services->fetch_assoc()): ?>
                                <option value="<?= $row['ServiceID'] ?>">
                                    <?= htmlspecialchars($row['ServiceName']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="booking_date" class="form-label">Choose Date:</label>
                    <input type="date" name="booking_date" id="booking_date" class="form-control" required 
                           min="<?= date('Y-m-d') ?>">
                </div>

                <div class="mb-3">
                    <label for="booking_time" class="form-label">Choose Time:</label>
                    <select name="booking_time" id="booking_time" class="form-control" required disabled>
                        <option value="">-- Select a date first --</option>
                    </select>
                    <small class="text-muted">Available time slots will appear after selecting a date</small>
                </div>

                <button type="submit" class="btn btn-primary">Book Now</button>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5>Booking Availability</h5>
                    </div>
                    <div class="card-body">
                        <div id="availabilityInfo">
                            <p class="text-muted">Select a service and date to see available time slots</p>
                        </div>
                        <div id="bookedSlots" class="mt-3" style="display:none;">
                            <h6>Booked Time Slots:</h6>
                            <ul id="bookedSlotsList" class="list-group"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceSelect = document.getElementById('service');
    const dateInput = document.getElementById('booking_date');
    const timeSelect = document.getElementById('booking_time');
    const availabilityInfo = document.getElementById('availabilityInfo');
    const bookedSlots = document.getElementById('bookedSlots');
    const bookedSlotsList = document.getElementById('bookedSlotsList');
    
    // When date changes, fetch available time slots
    dateInput.addEventListener('change', function() {
        const serviceId = serviceSelect.value;
        const date = this.value;
        
        if (!serviceId) {
            alert('Please select a service first');
            this.value = '';
            return;
        }
        
        fetchAvailableSlots(serviceId, date);
    });
    
    // When service changes with a date already selected
    serviceSelect.addEventListener('change', function() {
        const serviceId = this.value;
        const date = dateInput.value;
        
        if (date) {
            fetchAvailableSlots(serviceId, date);
        }
    });
    
    function fetchAvailableSlots(serviceId, date) {
        fetch(`get_available_slots.php?service_id=${serviceId}&date=${date}`)
            .then(response => response.json())
            .then(data => {
                // Update time select
                timeSelect.innerHTML = '';
                timeSelect.disabled = data.available_slots.length === 0;
                
                if (data.available_slots.length === 0) {
                    timeSelect.innerHTML = '<option value="">No available slots</option>';
                    availabilityInfo.innerHTML = `
                        <div class="alert alert-warning">
                            No available time slots for this date. Please choose another date.
                        </div>
                    `;
                } else {
                    timeSelect.innerHTML = '<option value="">-- Select a time --</option>';
                    data.available_slots.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = slot.value;
                        option.textContent = slot.time;
                        timeSelect.appendChild(option);
                    });
                    
                    availabilityInfo.innerHTML = `
                        <div class="alert alert-success">
                            ${data.available_slots.length} available time slot(s) for this date
                        </div>
                    `;
                }
                
                // Update booked slots display
                if (data.booked_slots.length > 0) {
                    bookedSlots.style.display = 'block';
                    bookedSlotsList.innerHTML = '';
                    
                    data.booked_slots.forEach(slot => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item d-flex justify-content-between align-items-center';
                        li.innerHTML = `
                            ${slot.time}
                            <span class="badge bg-danger rounded-pill">Booked</span>
                        `;
                        bookedSlotsList.appendChild(li);
                    });
                } else {
                    bookedSlots.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                availabilityInfo.innerHTML = `
                    <div class="alert alert-danger">
                        Error loading availability data. Please try again.
                    </div>
                `;
            });
    }
});
setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      bsAlert.close();
    }
  }, 3000);
</script>

</body>
</html>