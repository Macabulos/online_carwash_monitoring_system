<?php
// Start the session at the top of your file
session_start();

// Include database connection
include '../../connection/conn.php'; // Ensure this connects and assigns to $conn

// Check if the user is logged in
if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php");
    exit();
}

// Fetch the logged-in user's ID
$CustomerID = $_SESSION['CustomerID'];

// Prepare the query
$query = "SELECT b.BookingID, b.BookingDate, b.StatusID, s.ServiceName, c.Username, st.StatusName
          FROM bookings b
          JOIN customer c ON b.CustomerID = c.CustomerID
          JOIN service s ON b.ServiceID = s.ServiceID
          JOIN status st ON b.StatusID = st.StatusID
          WHERE b.CustomerID = ?  -- Filter by logged-in customer
          ORDER BY b.BookingDate DESC";

// Prepare and execute the query with parameter binding
$stmt = $conn->prepare($query); // Changed $mysqli to $conn
if ($stmt === false) {
    die("Error preparing query: " . $conn->error); // Changed $mysqli to $conn
}

$stmt->bind_param("i", $CustomerID); // Ensure this is the same variable as above
$stmt->execute();
$result = $stmt->get_result();

// Check if there were any bookings found
if (!$result) {
    die("Error: " . $conn->error); // Changed $mysqli to $conn
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include './semantic/head.php'; ?>
<style>
    /* General Styles */
    body {
        background-color: #f8f9fa;
        font-family: Arial, sans-serif;
    }

    /* Card Styling */
    .card {
        border: 1px solid #ddd;
        margin-bottom: 20px;
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease;
        border-radius: 10px;
    }

    .card-body {
        padding: 20px;
        background-color: #ffffff;
        border-radius: 10px;
    }

    .card-title {
        font-size: 1.2rem;
        font-weight: bold;
        color: #007bff;
    }

    .card-text {
        font-size: 1rem;
    }

    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.1);
    }

    /* Badge Color */
    .badge-warning {
        background-color: #ffc107;
    }

    .badge-success {
        background-color: #28a745;
    }

    .badge-danger {
        background-color: #dc3545;
    }

    .badge-secondary {
        background-color: #6c757d;
    }

    /* Animation for Cards */
    .fadeIn {
        animation: fadeIn 1s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    /* Buttons */
    .btn-custom {
        background-color: #007bff;
        color: white;
        border-radius: 25px;
        padding: 10px 20px;
        text-align: center;
        transition: background-color 0.3s;
    }

    .btn-custom:hover {
        background-color: #0056b3;
    }
</style>

<body>

<!-- Navbar -->
<?php include './semantic/navbar.php'; ?>

<!-- Dashboard Section -->
<section id="dashboard" class="container mt-5">
    <h2>Your Booking Dashboard</h2>

    <!-- Booking Cards -->
    <div class="row">
        <?php while ($row = $result->fetch_assoc()) : ?>
            <div class="col-md-4 mb-4">
                <!-- Single Booking Card with fade-in animation -->
                <div class="card fadeIn">
                    <div class="card-body">
                        <h5 class="card-title">Booking #<?php echo $row['BookingID']; ?></h5>
                        <p class="card-text"><strong>Service:</strong> <?php echo htmlspecialchars($row['ServiceName']); ?></p>
                        <p class="card-text"><strong>Customer:</strong> <?php echo htmlspecialchars($row['Username']); ?></p>
                        <p class="card-text"><strong>Booking Date:</strong> <?php echo date('Y-m-d H:i', strtotime($row['BookingDate'])); ?></p>
                        <p class="card-text">
                            <strong>Status:</strong>
                            <span class="badge 
                            <?php
                                switch ($row['StatusID']) {
                                    case 1: echo 'badge-warning'; break; // Pending
                                    case 2: echo 'badge-success'; break; // Completed
                                    case 3: echo 'badge-danger'; break; // Cancelled
                                    default: echo 'badge-secondary'; break;
                                }
                            ?>"><?php echo htmlspecialchars($row['StatusName']); ?></span>
                        </p>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Add Custom Button -->
    <!-- <div class="text-center mt-4">
        <a href="booking.php" class="btn-custom">Create New Booking</a>
    </div>
</section> -->

<!-- Footer -->

<!-- JavaScript -->
<!-- <script>
    // Optional: Add some interactive elements via JS if needed
    // For instance, a confirmation alert when creating a new booking.
    document.querySelector('.btn-custom').addEventListener('click', function() {
        alert('Redirecting to booking creation page...');
    });
</script> -->

</body>
</html>

<?php
// Close the database connection
$conn->close(); // Changed $mysqli to $conn
?>
