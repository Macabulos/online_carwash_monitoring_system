<?php
session_start();
require_once '../connection/conn.php'; // Include DB connection

// Check admin login
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../auth/login.php");
    exit;
}

// Total Customers with Bookings
$totalCustomersQuery = "
    SELECT COUNT(DISTINCT CustomerID) AS total 
    FROM bookings
";
$totalCustomersResult = mysqli_query($conn, $totalCustomersQuery);
$totalCustomers = mysqli_fetch_assoc($totalCustomersResult)['total'] ?? 0;

// Total Feedbacks
$totalFeedbackQuery = "SELECT COUNT(*) AS total FROM feedback";
$totalFeedbackResult = mysqli_query($conn, $totalFeedbackQuery);
$totalFeedback = mysqli_fetch_assoc($totalFeedbackResult)['total'] ?? 0;

// Total Reports
$totalReportsQuery = "SELECT COUNT(*) AS total FROM report";
$totalReportsResult = mysqli_query($conn, $totalReportsQuery);
$totalReports = mysqli_fetch_assoc($totalReportsResult)['total'] ?? 0;

// Total Users
$totalUsersQuery = "SELECT COUNT(*) AS total FROM customer"; // Assuming 'customer' is your users table
$totalUsersResult = mysqli_query($conn, $totalUsersQuery);
$totalUsers = mysqli_fetch_assoc($totalUsersResult)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Admin Dashboard</title>
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
            opacity: 0;
            animation: fadeInUp 0.8s forwards;
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

        /* Animation for Fade-In and Up */
        @keyframes fadeInUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Hover effect */
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.1);
        }

    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/nav.php'; ?>
        <div class="main">
            <?php include 'includes/navtop.php'; ?>
            <main class="content">
                <div class="container-fluid">
                    <h1 class="h3 mb-3">Admin Dashboard</h1>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h4>Total Users</h4>
                                    <p class="display-4"><?php echo $totalUsers; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h4>Total Bookings</h4>
                                    <p class="display-4"><?php echo $totalCustomers; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h4>Total Feedbacks</h4>
                                    <p class="display-4"><?php echo $totalFeedback; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h4>Total Reports</h4>
                                    <p class="display-4"><?php echo $totalReports; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
