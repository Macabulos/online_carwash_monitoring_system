<?php
include '../connection/conn.php'; // Include database connection

// Ensure admin is logged in

// Fetch total bookings
$totalBookingsQuery = "SELECT COUNT(*) AS total FROM booking";
$totalBookingsResult = mysqli_query($conn, $totalBookingsQuery);
$totalBookings = mysqli_fetch_assoc($totalBookingsResult)['total'];

// Fetch total feedbacks
$totalFeedbackQuery = "SELECT COUNT(*) AS total FROM feedback";
$totalFeedbackResult = mysqli_query($conn, $totalFeedbackQuery);
$totalFeedback = mysqli_fetch_assoc($totalFeedbackResult)['total'];

// Fetch total reports
$totalReportsQuery = "SELECT COUNT(*) AS total FROM report";
$totalReportsResult = mysqli_query($conn, $totalReportsQuery);
$totalReports = mysqli_fetch_assoc($totalReportsResult)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Admin Dashboard</title>
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
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h4>Total Bookings</h4>
                                    <p class="display-4"><?php echo $totalBookings; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h4>Total Feedbacks</h4>
                                    <p class="display-4"><?php echo $totalFeedback; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
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
