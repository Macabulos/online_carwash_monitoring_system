<?php

session_start();
require_once '../connection/conn.php'; // Database connection

// Ensure the user is logged in as an admin
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../auth/login.php");
    exit;
}
include 'includes/head.php';

// Fetch all feedback entries
$sql = "SELECT f.FeedbackID, c.Username AS customer_name, s.ServiceName, f.Comments, f.Ratings, f.Response
        FROM feedback f
        JOIN booking b ON f.BookingID = b.BookingID
        JOIN customer c ON b.CustomerID = c.CustomerID
        JOIN service s ON b.ServiceID = s.ServiceID
        ORDER BY f.FeedbackID DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<body>
<div class="wrapper">
    <?php include 'includes/nav.php'; ?>
    <div class="main">
        <?php include 'includes/navtop.php'; ?>
        <main class="content">
            <div class="container-fluid p-0">
                <h1 class="h3 mb-3">Manage Feedback</h1>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Customer</th>
                                            <th>Service</th>
                                            <th>Comments</th>
                                            <th>Ratings</th>
                                            <th>Response</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $row['FeedbackID']; ?></td>
                                            <td><?php echo $row['customer_name']; ?></td>
                                            <td><?php echo $row['ServiceName']; ?></td>
                                            <td><?php echo $row['Comments']; ?></td>
                                            <td><?php echo $row['Ratings']; ?> / 5</td>
                                            <td><?php echo $row['Response'] ?: 'No response yet'; ?></td>
                                            <td>
                                                <button class="btn btn-primary" onclick="openResponseModal(<?php echo $row['FeedbackID']; ?>, '<?php echo htmlspecialchars($row['Response']); ?>')">Respond</button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
</div>

<!-- Response Modal -->
<div id="responseModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Respond to Feedback</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="process_feedback.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="feedback_id" id="feedback_id">
                    <label for="response">Your Response:</label>
                    <textarea name="response" id="response" class="form-control" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Submit</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openResponseModal(feedbackID, response) {
    document.getElementById('feedback_id').value = feedbackID;
    document.getElementById('response').value = response;
    $('#responseModal').modal('show');
}
</script>
</body>
</html>
