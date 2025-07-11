<?php
session_start();
require '../../connection/conn.php';

// Check if user is logged in
if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php");
    exit();
}

$CustomerID = $_SESSION['CustomerID'];

// Fetch all available services with their base prices
$services = $conn->query("SELECT ServiceID, ServiceName, Description FROM service");

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
    $car_quantity = $_POST['car_quantity'] ?? 1;
    $car_type = $_POST['car_type'] ?? null;

    if ($service_id && $booking_date && $booking_time && $car_type) {
        $booking_datetime = date("Y-m-d H:i:s", strtotime("$booking_date $booking_time"));
        $status_id = 1; // "Pending"

        // Get service and car type details for price calculation
        $service_query = $conn->prepare("SELECT ServiceName FROM service WHERE ServiceID = ?");
        $service_query->bind_param("i", $service_id);
        $service_query->execute();
        $service_result = $service_query->get_result();
        $service_data = $service_result->fetch_assoc();
        
        $car_type_query = $conn->prepare("SELECT BasePrice FROM car_types WHERE CarTypeID = ?");
        $car_type_query->bind_param("i", $car_type);
        $car_type_query->execute();
        $car_type_result = $car_type_query->get_result();
        $car_type_data = $car_type_result->fetch_assoc();
        
        if (!$service_data || !$car_type_data) {
            $_SESSION['message'] = 'Invalid service or car type selected';
            $_SESSION['message_type'] = 'danger';
            header("Location: booking.php");
            exit();
        }

        // Calculate total price (service base price + car type base price) * quantity
        // Note: In a real system, you might have a more complex pricing structure
        $total_price = ($car_type_data['BasePrice']) * $car_quantity;

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

            if (!is_numeric($car_quantity) || $car_quantity < 1) {
                $_SESSION['message'] = 'Please enter a valid number of cars.';
                $_SESSION['message_type'] = 'warning';
            }
            
            if ($result_conflict->num_rows > 0) {
                $_SESSION['message'] = 'This time slot is already booked. Please choose another time.';
                $_SESSION['message_type'] = 'warning';
            } else {
                $stmt = $conn->prepare("INSERT INTO bookings (CustomerID, ServiceID, BookingDate, StatusID, CarQuantity, CarTypeID) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisiii", $CustomerID, $service_id, $booking_datetime, $status_id, $car_quantity, $car_type);

                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Booking successfully created! Total price: $' . number_format($total_price, 2);
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
                                <option value="<?= $row['ServiceID'] ?>" data-serviceid="<?= $row['ServiceID'] ?>">
                                    <?= htmlspecialchars($row['ServiceName']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                    <div id="serviceDescription" class="mt-2 text-muted"></div>
                </div>

                <div class="mb-3">
                    <label for="car_quantity" class="form-label">How many cars?</label>
                    <input type="number" name="car_quantity" id="car_quantity" class="form-control" min="1" value="1" required>
                </div>
                
                <div class="mb-3">
                    <label for="car_type" class="form-label">Select Car Type:</label>
                    <select name="car_type" id="car_type" class="form-control" required>
                        <option value="">-- Choose a Car Type --</option>
                        <?php
                        $car_types_query = "SELECT * FROM car_types";
                        $car_types_result = $conn->query($car_types_query);

                        if ($car_types_result->num_rows > 0) {
                            while ($row = $car_types_result->fetch_assoc()) {
                                echo "<option value='" . $row['CarTypeID'] . "' data-price='" . $row['BasePrice'] . "' data-duration='" . $row['EstimatedDuration'] . "'>" . 
                                     htmlspecialchars($row['TypeName']) . " ($" . number_format($row['BasePrice'], 2) . ")</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div id="priceSummary" class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h5>Price Summary</h5>
                    </div>
                    <div class="card-body">
                        <div id="servicePrice" class="mb-2">Service: --</div>
                        <div id="carTypePrice" class="mb-2">Car Type: --</div>
                        <div id="quantityInfo" class="mb-2">Quantity: 1</div>
                        <hr>
                        <div id="totalPrice" class="fw-bold">Total: $0.00</div>
                    </div>
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
                        <h5>Booking Details</h5>
                    </div>
                    <div class="card-body">
                        <div id="serviceDetails">
                            <p class="text-muted">Select a service to see details</p>
                        </div>
                        <div id="availabilityInfo" class="mt-3">
                            <p class="text-muted">Select a service and date to see available time slots</p>
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
    const carTypeSelect = document.getElementById('car_type');
    const carQuantityInput = document.getElementById('car_quantity');
    const dateInput = document.getElementById('booking_date');
    const timeSelect = document.getElementById('booking_time');
    const serviceDescription = document.getElementById('serviceDescription');
    const serviceDetails = document.getElementById('serviceDetails');
    const priceSummary = document.getElementById('priceSummary');
    const servicePrice = document.getElementById('servicePrice');
    const carTypePrice = document.getElementById('carTypePrice');
    const quantityInfo = document.getElementById('quantityInfo');
    const totalPrice = document.getElementById('totalPrice');
    const availabilityInfo = document.getElementById('availabilityInfo');
    
    // Service descriptions (would normally come from database)
    const serviceDescriptions = {
        1: "Standard car wash with exterior cleaning, wheel cleaning, and drying",
        8: "Full car detailing including interior and exterior deep cleaning",
        9: "Quick exterior wash and polish",
        10: "Quick interior vacuuming and cleaning",
        11: "Engine bay cleaning and detailing"
    };
    
    // Service base prices (would normally come from database)
    const servicePrices = {
        1: 25.00,  // Carwash
        8: 100.00, // Car Detailing
        9: 40.00,  // Quick Exterior
        10: 50.00, // Quick Interior
        11: 75.00  // Signature Engine
    };

    // When service changes
    serviceSelect.addEventListener('change', function() {
        const serviceId = this.value;
        
        if (serviceId) {
            // Update service description
            serviceDescription.textContent = serviceDescriptions[serviceId] || 'No description available';
            
            // Update service details in the card
            const selectedOption = this.options[this.selectedIndex];
            serviceDetails.innerHTML = `
                <h5>${selectedOption.text}</h5>
                <p>${serviceDescriptions[serviceId] || 'No description available'}</p>
                <p class="fw-bold">Base Price: $${servicePrices[serviceId].toFixed(2)}</p>
            `;
            
            // Update price summary
            updatePriceSummary();
            
            // If date is already selected, fetch available slots
            if (dateInput.value) {
                fetchAvailableSlots(serviceId, dateInput.value);
            }
        } else {
            serviceDescription.textContent = '';
            serviceDetails.innerHTML = '<p class="text-muted">Select a service to see details</p>';
            priceSummary.style.display = 'none';
        }
    });
    
    // When car type or quantity changes
    carTypeSelect.addEventListener('change', updatePriceSummary);
    carQuantityInput.addEventListener('input', updatePriceSummary);
    
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
    
    function updatePriceSummary() {
        const serviceId = serviceSelect.value;
        const carTypeOption = carTypeSelect.options[carTypeSelect.selectedIndex];
        const quantity = parseInt(carQuantityInput.value) || 1;
        
        if (serviceId && carTypeOption.value) {
            const carTypePriceValue = parseFloat(carTypeOption.dataset.price);
            const servicePriceValue = servicePrices[serviceId];
            const total = (servicePriceValue + carTypePriceValue) * quantity;
            
            servicePrice.textContent = `Service: $${servicePriceValue.toFixed(2)}`;
            carTypePrice.textContent = `Car Type: $${carTypePriceValue.toFixed(2)} (${carTypeOption.text.split('(')[0].trim()})`;
            quantityInfo.textContent = `Quantity: ${quantity}`;
            totalPrice.textContent = `Total: $${total.toFixed(2)}`;
            
            priceSummary.style.display = 'block';
        } else {
            priceSummary.style.display = 'none';
        }
    }
    
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