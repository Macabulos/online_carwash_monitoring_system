<?php
session_start();
require '../../connection/conn.php';

// Redirect if the user is not logged in
if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php");
    exit();
}

$CustomerID = $_SESSION['CustomerID'];

// Fetch available services and check if the customer has completed them
$bookings = $conn->query("
    SELECT 
        s.ServiceID, 
        s.ServiceName, 
        s.Description, 
        s.ImagePath,
        EXISTS (
            SELECT 1 FROM bookings b
            JOIN status st ON b.StatusID = st.StatusID
            WHERE b.CustomerID = $CustomerID
              AND b.ServiceID = s.ServiceID
              AND st.StatusName = 'Completed'
        ) AS IsCompleted
    FROM service s
");
?>
<!DOCTYPE html>
<html lang="en">
<?php include './semantic/head.php'; ?>
<body>
<?php include './semantic/navbar.php'; ?>

<main class="container mt-4">
    <section id="dashboard">
        <h2>Available Bookings</h2>
        <div class="row">
            <?php 
            if ($bookings->num_rows > 0) {
                while ($row = $bookings->fetch_assoc()) {
                    $serviceName = htmlspecialchars($row['ServiceName']);
                    $description = htmlspecialchars($row['Description']);
                    $imagePath = !empty($row['ImagePath']) ? '../../uploads/services/' . htmlspecialchars($row['ImagePath']) : 'images/default.jpg';
                    $isCompleted = $row['IsCompleted'];
            ?>
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo $imagePath; ?>" class="card-img-top" alt="Service Image" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $serviceName; ?></h5>
                            <p class="card-text" title="<?php echo $description; ?>">
                                <?php echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description; ?>
                            </p>

                            <?php if ($isCompleted): ?>
                                <!-- Feedback Form -->
                                <form action="submit_feedback.php" method="POST" class="mt-3">
                                    <input type="hidden" name="CustomerID" value="<?php echo $CustomerID; ?>">
                                    <input type="hidden" name="ServiceID" value="<?php echo $row['ServiceID']; ?>">
                                    <div class="mb-2">
                                        <textarea name="Comments" class="form-control" placeholder="Leave a feedback..." required></textarea>
                                    </div>
                                    <div class="mb-2">
                                        <select name="Ratings" class="form-select" required>
                                            <option value="">Rate the service</option>
                                            <option value="5">5 - Excellent</option>
                                            <option value="4">4 - Very Good</option>
                                            <option value="3">3 - Good</option>
                                            <option value="2">2 - Fair</option>
                                            <option value="1">1 - Poor</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">Submit Feedback</button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-warning p-2 mt-3 text-center">
                                    You can only rate this service after completing it.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p class='text-muted'>No available bookings at the moment.</p>";
            }
            ?>
        </div>
    </section>
</main>
</body>
</html>
