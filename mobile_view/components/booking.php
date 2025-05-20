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
$services = $conn->query("SELECT ServiceID, ServiceName, BasePrice FROM service");

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
    
    // Get current time
    $current_time = time();
    
    // Create time slots (every hour)
    for ($time = $start_time; $time < $end_time; $time += 3600) {
        // Skip if this is today and the time has passed
        if ($date == date('Y-m-d') && $time < $current_time) {
            continue;
        }
        
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
    $car_quantity = $_POST['car_quantity'] ?? 1;
    $car_type = $_POST['car_type'] ?? null;

    // Get service name to check if it's Carwash
    $service_check = $conn->query("SELECT ServiceName FROM service WHERE ServiceID = $service_id");
    $service_row = $service_check->fetch_assoc();
    $is_carwash = (strpos($service_row['ServiceName'], 'Carwash') !== false);

    // Validate all required fields
    $required_fields = [
        'service' => $service_id,
        'booking_date' => $booking_date,
        'booking_time' => $booking_time,
        'car_quantity' => $car_quantity
    ];

    // Only require car_type if it's Carwash service
    if ($is_carwash) {
        $required_fields['car_type'] = $car_type;
    }

    foreach ($required_fields as $field => $value) {
        if (empty($value)) {
            $_SESSION['message'] = 'All required fields must be filled.';
            $_SESSION['message_type'] = 'warning';
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    }

    // Validate car quantity
    if (!is_numeric($car_quantity) || $car_quantity < 1) {
        $_SESSION['message'] = 'Please enter a valid number of cars.';
        $_SESSION['message_type'] = 'warning';
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    $booking_datetime = date("Y-m-d H:i:s", strtotime("$booking_date $booking_time"));
    $status_id = 1; // "Pending"

    // Check if booking is in the past
    if (strtotime($booking_datetime) < time()) {
        $_SESSION['message'] = 'You cannot book a time slot that has already passed.';
        $_SESSION['message_type'] = 'warning';
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

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
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    if ($result_existing->num_rows > 0) {
        $_SESSION['message'] = 'You already have a pending booking. Please complete or cancel it before booking again.';
        $_SESSION['message_type'] = 'warning';
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    // Check time slot conflict
    $check_conflict_query = "SELECT * FROM bookings WHERE ServiceID = ? AND BookingDate = ?";
    $stmt_conflict = $conn->prepare($check_conflict_query);
    $stmt_conflict->bind_param("is", $service_id, $booking_datetime);
    $stmt_conflict->execute();
    $result_conflict = $stmt_conflict->get_result();

    if ($result_conflict->num_rows > 0) {
        $_SESSION['message'] = 'This time slot is already booked. Please choose another time.';
        $_SESSION['message_type'] = 'warning';
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    // Insert the new booking
    $stmt = $conn->prepare("INSERT INTO bookings (CustomerID, ServiceID, BookingDate, StatusID, CarQuantity, CarTypeID) VALUES (?, ?, ?, ?, ?, ?)");
    $car_type_value = $is_carwash ? $car_type : NULL; // Only insert car type if it's Carwash service
    $stmt->bind_param("iisiii", $CustomerID, $service_id, $booking_datetime, $status_id, $car_quantity, $car_type_value);

    if ($stmt->execute()) {
        $_SESSION['message'] = 'Booking successfully created!';
        $_SESSION['message_type'] = 'success';
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['message'] = 'Failed to book. Please try again.';
        $_SESSION['message_type'] = 'danger';
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include './semantic/head.php'; ?>
<body>
<?php include './semantic/navbar.php'; ?>

<div class="container mt-5">
    <h3 class="mb-4">Book a Service</h3>

    <!-- Flash message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                                <option value="<?= $row['ServiceID'] ?>" 
                                    data-is-carwash="<?= strpos($row['ServiceName'], 'Carwash') !== false ? 'true' : 'false' ?>">
                                    <?= htmlspecialchars($row['ServiceName']) ?>
                                    <?php if (strpos($row['ServiceName'], 'Carwash') === false): ?>
                                        (₱<?= number_format($row['BasePrice'], 2) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="car_quantity" class="form-label">How many cars?</label>
                    <input type="number" name="car_quantity" id="car_quantity" class="form-control" min="1" value="1" required>
                </div>
                
                <div class="mb-3" id="carTypeContainer" style="display: none;">
                    <label for="car_type" class="form-label">Select Car Type:</label>
                    <select name="car_type" id="car_type" class="form-control" required>
                        <option value="">-- Choose a Car Type --</option>
                        <?php
                        // Fetch car types from the database
                        $car_types_query = "SELECT * FROM car_types";
                        $car_types_result = $conn->query($car_types_query);

                        if ($car_types_result->num_rows > 0) {
                            while ($row = $car_types_result->fetch_assoc()) {
                                echo "<option value='" . $row['CarTypeID'] . "' 
                                    data-price='" . $row['BasePrice'] . "' 
                                    data-duration='" . $row['EstimatedDuration'] . "'>" . 
                                    htmlspecialchars($row['TypeName']) . " (₱" . number_format($row['BasePrice'], 2) . ")</option>";
                            }
                        }
                        ?>
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
                        <h5>Booking Availability & Pricing</h5>
                    </div>
                    <div class="card-body">
                        <div id="availabilityInfo">
                            <p class="text-muted">Select a service and date to see available time slots</p>
                        </div>
                        
                        <div id="priceSummary" class="card mt-3" style="display: none;">
                            <div class="card-header">
                                <h6>Price Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Service:</strong></p>
                                        <p class="mb-1"><strong>Car Type:</strong></p>
                                        <p class="mb-1"><strong>Quantity:</strong></p>
                                        <p class="mb-1"><strong>Duration:</strong></p>
                                    </div>
                                    <div class="col-6 text-end">
                                        <p class="mb-1" id="summaryService">-</p>
                                        <p class="mb-1" id="summaryCarType">-</p>
                                        <p class="mb-1" id="summaryQuantity">-</p>
                                        <p class="mb-1" id="summaryDuration">-</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Service Price:</strong></p>
                                        <p class="mb-1"><strong>Car Type Price:</strong></p>
                                    </div>
                                    <div class="col-6 text-end">
                                        <p class="mb-1" id="summaryServicePrice">₱0.00</p>
                                        <p class="mb-1" id="summaryCarTypePrice">₱0.00</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-6">
                                        <h5 class="mb-0"><strong>Total Price:</strong></h5>
                                    </div>
                                    <div class="col-6 text-end">
                                        <h5 class="mb-0" id="summaryTotalPrice">₱0.00</h5>
                                    </div>
                                </div>
                            </div>
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
    const carTypeContainer = document.getElementById('carTypeContainer');
    const carTypeSelect = document.getElementById('car_type');
    const carQuantityInput = document.getElementById('car_quantity');
    const dateInput = document.getElementById('booking_date');
    const timeSelect = document.getElementById('booking_time');
    const availabilityInfo = document.getElementById('availabilityInfo');
    const bookedSlots = document.getElementById('bookedSlots');
    const bookedSlotsList = document.getElementById('bookedSlotsList');
    const priceSummary = document.getElementById('priceSummary');
    
    // Initialize with car type hidden
    carTypeContainer.style.display = 'none';

    // Calculate and display prices when selections change
    function calculatePrice() {
        const serviceOption = serviceSelect.options[serviceSelect.selectedIndex];
        const carTypeOption = carTypeSelect.options[carTypeSelect.selectedIndex];
        const carQuantity = parseInt(carQuantityInput.value) || 1;
        const isCarwash = serviceOption.getAttribute('data-is-carwash') === 'true';
        
        // Hide price summary if no service selected
        if (!serviceOption.value) {
            priceSummary.style.display = 'none';
            return;
        }
        
        let totalPrice = 0;
        let duration = 0;
        
        // Update the price summary
        document.getElementById('summaryService').textContent = serviceOption.text.split('(')[0].trim();
        document.getElementById('summaryQuantity').textContent = carQuantity;
        
        // For carwash, only show car type price
        if (isCarwash) {
            document.getElementById('summaryServicePrice').textContent = 'Included in car type';
            
            if (carTypeOption.value) {
                const carTypeBasePrice = parseFloat(carTypeOption.getAttribute('data-price'));
                duration = parseInt(carTypeOption.getAttribute('data-duration'));
                totalPrice = carTypeBasePrice * carQuantity;
                
                document.getElementById('summaryCarType').textContent = carTypeOption.text.split('(')[0].trim();
                document.getElementById('summaryCarTypePrice').textContent = `₱${carTypeBasePrice.toFixed(2)}`;
                document.getElementById('summaryDuration').textContent = `${duration} minutes`;
            } else {
                document.getElementById('summaryCarType').textContent = '-';
                document.getElementById('summaryCarTypePrice').textContent = '₱0.00';
                document.getElementById('summaryDuration').textContent = '-';
            }
        } else {
            // For non-carwash services
            const serviceBasePrice = parseFloat(serviceOption.text.match(/₱([\d,]+\.\d{2})/)[1].replace(',', ''));
            totalPrice = serviceBasePrice * carQuantity;
            
            document.getElementById('summaryServicePrice').textContent = `₱${serviceBasePrice.toFixed(2)}`;
            document.getElementById('summaryCarType').textContent = '-';
            document.getElementById('summaryCarTypePrice').textContent = '₱0.00';
            document.getElementById('summaryDuration').textContent = '-';
        }
        
        document.getElementById('summaryTotalPrice').textContent = `₱${totalPrice.toFixed(2)}`;
        priceSummary.style.display = 'block';
    }

    // Show/hide car type based on service selection
    function handleServiceChange() {
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        const isCarwash = selectedOption.getAttribute('data-is-carwash') === 'true';
        
        // Show/hide car type container and make it required if carwash
        carTypeContainer.style.display = isCarwash ? 'block' : 'none';
        carTypeSelect.required = isCarwash;
        
        // Reset car type if not carwash
        if (!isCarwash) {
            carTypeSelect.value = '';
        }
        
        calculatePrice();
    }

    // Fetch available time slots for selected service and date
    function fetchAvailableSlots(serviceId, date) {
        if (!serviceId || !date) return;
        
        fetch(`get_available_slots.php?service_id=${serviceId}&date=${date}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                updateTimeSlots(data, date);
                updateBookedSlots(data);
            })
            .catch(error => {
                console.error('Error:', error);
                showAvailabilityError();
            });
    }

    // Update time slots dropdown
    function updateTimeSlots(data, date) {
        timeSelect.innerHTML = '';
        timeSelect.disabled = data.available_slots.length === 0;
        
        if (data.available_slots.length === 0) {
            timeSelect.innerHTML = '<option value="">No available slots</option>';
            availabilityInfo.innerHTML = `
                <div class="alert alert-warning">
                    No available time slots for this date. Please choose another date.
                </div>
            `;
            return;
        }

        timeSelect.innerHTML = '<option value="">-- Select a time --</option>';
        const now = new Date();
        const isToday = date === now.toISOString().split('T')[0];
        let availableCount = 0;

        data.available_slots.forEach(slot => {
            // Skip if this is today and the time has passed
            if (isToday) {
                const slotTime = new Date(`${date}T${slot.value}`);
                if (slotTime < now) return;
            }
            
            const time12 = convertTo12HourFormat(slot.value);
            const option = document.createElement('option');
            option.value = slot.value;
            option.textContent = time12;
            timeSelect.appendChild(option);
            availableCount++;
        });

        if (availableCount === 0) {
            timeSelect.innerHTML = '<option value="">No available slots</option>';
            availabilityInfo.innerHTML = `
                <div class="alert alert-warning">
                    All time slots for today have passed. Please choose another date.
                </div>
            `;
        } else {
            availabilityInfo.innerHTML = `
                <div class="alert alert-success">
                    ${availableCount} available time slot(s) for this date
                </div>
            `;
        }
    }

    // Update booked slots display
    function updateBookedSlots(data) {
        if (!data.booked_slots || data.booked_slots.length === 0) {
            bookedSlots.style.display = 'none';
            return;
        }

        bookedSlots.style.display = 'block';
        bookedSlotsList.innerHTML = '';

        data.booked_slots.forEach(slot => {
            const time12 = convertTo12HourFormat(slot.time);
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = `
                ${time12}
                <span class="badge bg-danger rounded-pill">Booked</span>
            `;
            bookedSlotsList.appendChild(li);
        });
    }

    // Convert 24-hour time to 12-hour format
    function convertTo12HourFormat(time24) {
        const [hours, minutes] = time24.split(':');
        const period = hours >= 12 ? 'PM' : 'AM';
        const hours12 = hours % 12 || 12;
        return `${hours12}:${minutes} ${period}`;
    }

    // Show error message
    function showAvailabilityError() {
        availabilityInfo.innerHTML = `
            <div class="alert alert-danger">
                Error loading availability data. Please try again.
            </div>
        `;
    }

    // Event listeners
    serviceSelect.addEventListener('change', function() {
        handleServiceChange();
        if (dateInput.value) {
            fetchAvailableSlots(this.value, dateInput.value);
        }
    });

    carTypeSelect.addEventListener('change', calculatePrice);
    carQuantityInput.addEventListener('input', calculatePrice);

    dateInput.addEventListener('change', function() {
        if (serviceSelect.value) {
            fetchAvailableSlots(serviceSelect.value, this.value);
        } else {
            alert('Please select a service first');
            this.value = '';
        }
    });

    // Auto-close alerts after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }
    }, 5000);

    // Initialize price calculation if there are pre-selected values
    calculatePrice();
});
</script>

</body>
</html>