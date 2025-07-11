<?php
session_start();
require_once '../connection/conn.php';

if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../auth/login.php");
    exit;
}


$sql = "SELECT f.FeedbackID, c.Username AS customer_name, s.ServiceName, f.Comments, f.Ratings, f.Response
        FROM feedback f
        JOIN customer c ON f.CustomerID = c.CustomerID
        LEFT JOIN service s ON f.ServiceID = s.ServiceID
        ORDER BY f.FeedbackID DESC";


$result = mysqli_query($conn, $sql);
if (!$result) {
    die('Error fetching feedback data: ' . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>
<body>
<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
    <?php include 'includes/navbar.php'; ?>
        <main class="content">
            <div class="container-fluid p-0">
                <h1 class="h3 mb-3">Manage Feedback</h1>
                     <!-- Alerts -->
                     <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                       
                    </div>
                <?php elseif (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                       
                    </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Customer</th>
                                            <th>Service</th>
                                            <th>Comments</th>
                                            <th>Ratings</th>
                                            <!-- <th>Response</th> -->
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['FeedbackID']) ?></td>
                                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                            <td><?= htmlspecialchars($row['ServiceName'] ?? 'N/A') ?></td>
                                            <td class="comments-column"><?= htmlspecialchars($row['Comments']) ?></td>
                                            <td><?= htmlspecialchars($row['Ratings']) ?> / 5</td>
                                            <!-- <td><?= $row['Response'] ? htmlspecialchars($row['Response']) : '<em>No response yet</em>' ?></td> -->
                                            <td>
                                                <!-- <button class="btn btn-primary mb-1"
                                                        onclick="openResponseModal(<?= $row['FeedbackID'] ?>, `<?= htmlspecialchars(addslashes($row['Response'])) ?>`)">
                                                    Respond
                                                </button> -->
                                                <button class="btn btn-danger mb-1"
                                                        onclick="openDeleteModal(<?= $row['FeedbackID'] ?>)">
                                                    Delete
                                                </button>
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
                <!-- <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button> -->
            </div>
            <form action="process_feedback.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="feedback_id" id="feedback_id">
                    <label for="response">Your Response:</label>
                    <textarea name="response" id="response" class="form-control" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Submit</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Feedback</h5>
                <!-- <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button> -->
            </div>
            <form id="deleteFeedbackForm" action="delete_feedback.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="feedback_id" id="delete_feedback_id">
                    <p>Are you sure you want to delete this feedback?</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Include Bootstrap JS + jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function openResponseModal(feedbackID, response = '') {
    document.getElementById('feedback_id').value = feedbackID;
    document.getElementById('response').value = response;
    const modal = new bootstrap.Modal(document.getElementById('responseModal'));
    modal.show();
}

// Open the response modal (unchanged)
function openResponseModal(feedbackID, response = '') {
    document.getElementById('feedback_id').value = feedbackID;
    document.getElementById('response').value = response;
    const modal = new bootstrap.Modal(document.getElementById('responseModal'));
    modal.show();
}

// Open the delete confirmation modal
function openDeleteModal(feedbackID) {
    document.getElementById('delete_feedback_id').value = feedbackID;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

</script>
</body>
</html>
