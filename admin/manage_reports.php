<?php
include '../connection/conn.php';
include 'includes/head.php';
?>

<body>
<div class="wrapper">
    <?php include 'includes/nav.php'; ?>
    <div class="main">
        <?php include 'includes/navtop.php'; ?>
        <main class="content">
            <div class="container-fluid p-0">
                <h1 class="h3 mb-3">Manage Reports</h1>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Generate Reports</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="reportType">Select Report Type:</label>
                                    <select class="form-control" name="reportType" required>
                                        <option value="">-- Select --</option>
                                        <option value="bookings">Bookings</option>
                                        <option value="customers">Customers</option>
                                        <option value="feedback">Feedback</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="startDate">Start Date:</label>
                                    <input type="date" class="form-control" name="startDate" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="endDate">End Date:</label>
                                    <input type="date" class="form-control" name="endDate" required>
                                </div>
                            </div>
                            <br>
                            <button type="submit" name="generate" class="btn btn-primary">Generate Report</button>
                        </form>
                    </div>
                </div>

                <?php
                if (isset($_POST['generate'])) {
                    $reportType = $_POST['reportType'];
                    $startDate = $_POST['startDate'];
                    $endDate = $_POST['endDate'];

                    echo '<div class="card mt-4">';
                    echo '<div class="card-header"><h5>Report Results</h5></div>';
                    echo '<div class="card-body">';

                    if ($reportType == 'bookings') {
                        $query = "SELECT b.BookingID, c.Username, s.ServiceName, b.Date, b.Status FROM booking b
                                  JOIN customer c ON b.CustomerID = c.CustomerID
                                  JOIN service s ON b.ServiceID = s.ServiceID
                                  WHERE b.Date BETWEEN '$startDate' AND '$endDate' ORDER BY b.Date DESC";
                    } elseif ($reportType == 'customers') {
                        $query = "SELECT CustomerID, Username, EmailAddress, Age FROM customer
                                  WHERE DATE_FORMAT(NOW(), '%Y-%m-%d') BETWEEN '$startDate' AND '$endDate'";
                    } elseif ($reportType == 'feedback') {
                        $query = "SELECT f.FeedbackID, c.Username, f.Comments, f.Ratings FROM feedback f
                                  JOIN booking b ON f.BookingID = b.BookingID
                                  JOIN customer c ON b.CustomerID = c.CustomerID
                                  WHERE DATE_FORMAT(NOW(), '%Y-%m-%d') BETWEEN '$startDate' AND '$endDate'";
                    }

                    $result = mysqli_query($conn, $query);
                    if (mysqli_num_rows($result) > 0) {
                        echo '<table class="table table-striped">';
                        echo '<thead><tr>';
                        while ($field = mysqli_fetch_field($result)) {
                            echo '<th>' . $field->name . '</th>';
                        }
                        echo '</tr></thead>';
                        echo '<tbody>';
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>';
                            foreach ($row as $value) {
                                echo '<td>' . htmlspecialchars($value) . '</td>';
                            }
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                    } else {
                        echo '<p class="text-danger">No records found for the selected date range.</p>';
                    }
                    echo '</div></div>';
                }
                ?>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>
