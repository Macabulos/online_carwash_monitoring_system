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
    'closed_days' => ['Sunday'], // Days when business is closed
    'max_simultaneous' => 3 // Maximum simultaneous bookings per time slot
];

// Function to generate available time slots
function getAvailableSlots($service_id, $date, $booked_slots, $business_hours) {
    $available_slots = [];
    
    // Check if date is a closed day
    $day_name = date('l', strtotime($date));
    if (in_array($day_name, $business_hours['closed_days'])) {
        return ['status' => 'closed', 'message' => 'Business is closed on this day']; 
    }
    
    $start_time = strtotime($date . ' ' . $business_hours['start']);
    $end_time = strtotime($date . ' ' . $business_hours['end']);
    
    // Get current time
    $current_time = time();
    
    // Create time slots (every hour)
    $booked_info = [];
    
    foreach ($booked_slots as $booking) {
        if ($booking['date'] == $date && $booking['service_id'] == $service_id) {
            $booked_info[$booking['time']] = $booking['count'];
        }
    }
    
    for ($time = $start_time; $time < $end_time; $time += 3600) {
        // Skip if this is today and the time has passed
        if ($date == date('Y-m-d') && $time < $current_time) {
            continue;
        }
        
        $slot_time = date('H:i:s', $time);
        $booking_count = $booked_info[$slot_time] ?? 0;
        
        if ($booking_count < $business_hours['max_simultaneous']) {
            $available_slots[] = [
                'time' => date('H:i', $time),
                'value' => date('H:i:s', $time),
                'count' => $booking_count
            ];
        }
    }
    
    return [
        'status' => 'success',
        'available_slots' => $available_slots,
        'booked_slots' => $booked_slots
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service'] ?? null;
    $booking_date = $_POST['booking_date'] ?? null;
    $booking_time = $_POST['booking_time'] ?? null;
    $car_quantity = $_POST['car_quantity'] ?? 1;
    $car_type = $_POST['car_type'] ?? null;
    $employee_id = $_POST['employee'] ?? null;

    // Get service name to check if it's Carwash
    $service_check = $conn->query("SELECT ServiceName FROM service WHERE ServiceID = $service_id");
    $service_row = $service_check->fetch_assoc();
    $is_carwash = (strpos($service_row['ServiceName'], 'Carwash') !== false);

    // Validate all required fields
    $required_fields = [
        'service' => $service_id,
        'booking_date' => $booking_date,
        'booking_time' => $booking_time,
        'car_quantity' => $car_quantity,
        'employee' => $employee_id // Employee is now required for all services
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
    if (!is_numeric($car_quantity) || $car_quantity < 1 || $car_quantity > 10) {
        $_SESSION['message'] = 'Please enter a valid number of cars (1-10).';
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

    // Check if the user already has a future pending booking for this service
    $existing_booking_query = "SELECT * FROM bookings 
                            WHERE CustomerID = ? 
                            AND ServiceID = ?
                            AND BookingDate > NOW()
                            AND StatusID = 1";
    $stmt_existing = $conn->prepare($existing_booking_query);
    $stmt_existing->bind_param("ii", $CustomerID, $service_id);
    $stmt_existing->execute();
    $result_existing = $stmt_existing->get_result();

    if ($result_existing->num_rows > 0) {
        $_SESSION['message'] = 'You already have a pending booking for this service. Please complete or cancel it before booking again.';
        $_SESSION['message_type'] = 'warning';
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    // Check time slot availability
    $check_availability_query = "SELECT COUNT(*) as booking_count FROM bookings 
                               WHERE ServiceID = ? 
                               AND BookingDate = ?
                               AND StatusID != 3";
    $stmt_availability = $conn->prepare($check_availability_query);
    $stmt_availability->bind_param("is", $service_id, $booking_datetime);
    $stmt_availability->execute();
    $result_availability = $stmt_availability->get_result();
    $availability_data = $result_availability->fetch_assoc();

    if ($availability_data['booking_count'] >= $business_hours['max_simultaneous']) {
        $_SESSION['message'] = 'This time slot is already fully booked. Please choose another time.';
        $_SESSION['message_type'] = 'warning';
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    // Update employee availability
    $conn->query("UPDATE employees SET Availability = 'Assigned' WHERE EmployeeID = $employee_id");
    
    // Insert the new booking with conditional fields
    if ($is_carwash) {
        $stmt = $conn->prepare("INSERT INTO bookings (CustomerID, ServiceID, BookingDate, StatusID, CarQuantity, CarTypeID, EmployeeID) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisiiii", $CustomerID, $service_id, $booking_datetime, $status_id, $car_quantity, $car_type, $employee_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO bookings (CustomerID, ServiceID, BookingDate, StatusID, CarQuantity, EmployeeID) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisiii", $CustomerID, $service_id, $booking_datetime, $status_id, $car_quantity, $employee_id);
    }

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

// Fetch available employees for the form
$availableEmployees = $conn->query("SELECT * FROM employees WHERE Availability = 'Available'");
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

    <form method="POST" action="" id="bookingForm" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="service" class="form-label">Select Service:</label>
                    <select name="service" id="service" class="form-control" required>
                        <option value="">-- Choose a Service --</option>
                        <?php if ($services->num_rows > 0): ?>
                            <?php while ($row = $services->fetch_assoc()): ?>
                                <option value="<?= $row['ServiceID'] ?>" 
                                    data-is-carwash="<?= strpos($row['ServiceName'], 'Carwash') !== false ? 'true' : 'false' ?>"
                                    data-price="<?= strpos($row['ServiceName'], 'Carwash') === false ? $row['BasePrice'] : '0' ?>">
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
                    <input type="number" name="car_quantity" id="car_quantity" class="form-control" min="1" max="10" value="1" required>
                </div>
                
                <div class="mb-3" id="carTypeContainer" style="display: none;">
                    <label for="car_type" class="form-label">Select Car Type:</label>
                    <select name="car_type" id="car_type" class="form-control">
                        <option value="">-- Choose a Car Type --</option>
                        <?php
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
                    <label for="employee" class="form-label">Select Employee:</label>
                    <select name="employee" id="employee" class="form-control" required>
                        <option value="">-- Choose an Employee --</option>
                        <?php if ($availableEmployees->num_rows > 0): ?>
                            <?php while ($employee = $availableEmployees->fetch_assoc()): ?>
                                <option value="<?= $employee['EmployeeID'] ?>">
                                    <?= htmlspecialchars($employee['FirstName'] . ' ' . $employee['LastName']) ?> 
                                    (<?= htmlspecialchars($employee['Position']) ?>)
                                </option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option value="" disabled>No available employees</option>
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
                                        <p class="mb-1" id="carTypeLabel" style="display: none;"><strong>Car Type:</strong></p>
                                        <p class="mb-1"><strong>Assigned Employee:</strong></p>
                                        <p class="mb-1"><strong>Quantity:</strong></p>
                                        <p class="mb-1" id="durationLabel" style="display: none;"><strong>Duration:</strong></p>
                                    </div>
                                    <div class="col-6 text-end">
                                        <p class="mb-1" id="summaryService">-</p>
                                        <p class="mb-1" id="summaryCarType" style="display: none;">-</p>
                                        <p class="mb-1" id="summaryEmployee">-</p>
                                        <p class="mb-1" id="summaryQuantity">-</p>
                                        <p class="mb-1" id="summaryDuration" style="display: none;">-</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-6">
                                        <p class="mb-1" id="servicePriceLabel" style="display: none;"><strong>Service Price:</strong></p>
                                        <p class="mb-1" id="carTypePriceLabel" style="display: none;"><strong>Car Type Price:</strong></p>
                                    </div>
                                    <div class="col-6 text-end">
                                        <p class="mb-1" id="summaryServicePrice" style="display: none;">₱0.00</p>
                                        <p class="mb-1" id="summaryCarTypePrice" style="display: none;">₱0.00</p>
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
    
    // Elements to show/hide based on service type
    const carTypeLabel = document.getElementById('carTypeLabel');
    const employeeLabel = document.getElementById('employeeLabel');
    const durationLabel = document.getElementById('durationLabel');
    const servicePriceLabel = document.getElementById('servicePriceLabel');
    const carTypePriceLabel = document.getElementById('carTypePriceLabel');
    const summaryCarType = document.getElementById('summaryCarType');
    const summaryEmployee = document.getElementById('summaryEmployee');
    const summaryDuration = document.getElementById('summaryDuration');
    const summaryServicePrice = document.getElementById('summaryServicePrice');
    const summaryCarTypePrice = document.getElementById('summaryCarTypePrice');

    // Initialize with car type container hidden
    carTypeContainer.style.display = 'none';

    // Calculate and display prices when selections change
    function calculatePrice() {
        const serviceOption = serviceSelect.options[serviceSelect.selectedIndex];
        const carTypeOption = carTypeSelect.options[carTypeSelect.selectedIndex];
        const employeeOption = document.getElementById('employee').options[document.getElementById('employee').selectedIndex];
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
        document.getElementById('summaryEmployee').textContent = employeeOption.value ? employeeOption.text : 'Not assigned';
        
        // For carwash services
        if (isCarwash) {
            // Hide service price elements and show car type elements
            servicePriceLabel.style.display = 'none';
            summaryServicePrice.style.display = 'none';
            
            carTypeLabel.style.display = 'block';
            durationLabel.style.display = 'block';
            carTypePriceLabel.style.display = 'block';
            summaryCarType.style.display = 'block';
            summaryDuration.style.display = 'block';
            summaryCarTypePrice.style.display = 'block';

            if (carTypeOption.value) {
                const carTypeBasePrice = parseFloat(carTypeOption.getAttribute('data-price'));
                duration = parseInt(carTypeOption.getAttribute('data-duration'));
                totalPrice = carTypeBasePrice * carQuantity;
                
                summaryCarType.textContent = carTypeOption.text.split('(')[0].trim();
                summaryCarTypePrice.textContent = `₱${carTypeBasePrice.toFixed(2)}`;
                summaryDuration.textContent = `${duration} minutes`;
            } else {
                summaryCarType.textContent = '-';
                summaryCarTypePrice.textContent = '₱0.00';
                summaryDuration.textContent = '-';
            }
        } else {
            // For non-carwash services
            // Show service price elements and hide car type elements
            servicePriceLabel.style.display = 'block';
            summaryServicePrice.style.display = 'block';
            
            carTypeLabel.style.display = 'none';
            durationLabel.style.display = 'none';
            carTypePriceLabel.style.display = 'none';
            summaryCarType.style.display = 'none';
            summaryDuration.style.display = 'none';
            summaryCarTypePrice.style.display = 'none';

            const serviceBasePrice = parseFloat(serviceOption.getAttribute('data-price'));
            totalPrice = serviceBasePrice * carQuantity;
            
            summaryServicePrice.textContent = `₱${serviceBasePrice.toFixed(2)}`;
        }
        
        document.getElementById('summaryTotalPrice').textContent = `₱${totalPrice.toFixed(2)}`;
        priceSummary.style.display = 'block';
    }

    // Show/hide fields based on service selection
    function handleServiceChange() {
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        const isCarwash = selectedOption.getAttribute('data-is-carwash') === 'true';
        
        // Show/hide car type container only
        carTypeContainer.style.display = isCarwash ? 'block' : 'none';
        
        // Set required fields
        carTypeSelect.required = isCarwash;
        
        // Reset car type selection if not carwash
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
                if (data.status === 'closed') {
                    updateTimeSlots([], date);
                    availabilityInfo.innerHTML = `
                        <div class="alert alert-warning">
                            Business is closed on ${date}. Please choose another day.
                        </div>
                    `;
                    return;
                }
                updateTimeSlots(data.available_slots, date);
                updateBookedSlots(data.booked_slots);
            })
            .catch(error => {
                console.error('Error:', error);
                showAvailabilityError();
            });
    }

    // Update time slots dropdown
    function updateTimeSlots(slots, date) {
        timeSelect.innerHTML = '';
        timeSelect.disabled = slots.length === 0;
        
        if (slots.length === 0) {
            timeSelect.innerHTML = '<option value="">No available slots</option>';
            return;
        }

        timeSelect.innerHTML = '<option value="">-- Select a time --</option>';
        const now = new Date();
        const isToday = date === now.toISOString().split('T')[0];
        let availableCount = 0;

        slots.forEach(slot => {
            // Skip if this is today and the time has passed
            if (isToday) {
                const slotTime = new Date(`${date}T${slot.value}`);
                if (slotTime < now) return;
            }
            
            const time12 = convertTo12HourFormat(slot.value);
            const option = document.createElement('option');
            option.value = slot.value;
            option.textContent = time12;
            
            // Show how many slots are left (for carwash)
            if (slot.count > 0) {
                option.textContent += ` (${<?= $business_hours['max_simultaneous'] ?> - slot.count} slots left)`;
            }
            
            timeSelect.appendChild(option);
            availableCount++;
        });

        if (availableCount === 0) {
            timeSelect.innerHTML = '<option value="">No available slots</option>';
            availabilityInfo.innerHTML = `
                <div class="alert alert-warning">
                    ${isToday ? 'All time slots for today have passed.' : 'No available time slots for this date.'} 
                    Please choose another date.
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
    function updateBookedSlots(bookedSlotsData) {
        if (!bookedSlotsData || bookedSlotsData.length === 0) {
            bookedSlots.style.display = 'none';
            return;
        }

        bookedSlots.style.display = 'block';
        bookedSlotsList.innerHTML = '';

        // Group by time
        const groupedSlots = {};
        bookedSlotsData.forEach(slot => {
            const time12 = convertTo12HourFormat(slot.time);
            if (!groupedSlots[time12]) {
                groupedSlots[time12] = {
                    count: slot.count,
                    services: []
                };
            }
            groupedSlots[time12].services.push(slot.service_id);
        });

        // Display grouped slots
        for (const [time, data] of Object.entries(groupedSlots)) {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = `
                ${time}
                <span class="badge bg-danger rounded-pill">${data.count} booked</span>
            `;
            bookedSlotsList.appendChild(li);
        }
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
    document.getElementById('employee').addEventListener('change', calculatePrice);
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