<?php
session_start();
require_once '../connection/conn.php';

// Ensure the user is logged in as an admin
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../auth/login.php");
    exit;
}

// Fetch all customers
$sql = "SELECT * FROM customer ORDER BY CustomerID ASC";
$result = mysqli_query($conn, $sql);

// Handle delete request
if (isset($_POST['delete_user'])) {
    $customer_id = $_POST['customer_id'];
    $delete_query = "DELETE FROM customer WHERE CustomerID = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $customer_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Customer deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting customer.";
    }
    header("Location: manage_user.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>
<body>
<div class="wrapper">
    <?php include 'includes/nav.php'; ?>
    <div class="main">
        <?php include 'includes/navtop.php'; ?>
        <main class="content">
            <div class="container-fluid p-0">
                <h1 class="h3 mb-3">Manage Customers</h1>

                <!-- Alert Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                <?php elseif (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table id="example" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Customer ID</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Age</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                     <tr>
                                <td><?php echo $row['CustomerID']; ?></td>
                                <td><?php echo htmlspecialchars($row['Username']); ?></td>
                                <td><?php echo htmlspecialchars($row['EmailAddress']); ?></td>
                                <td><?php echo $row['Age']; ?></td>
                                    <td>
                                        <?php
                                            if ($row['StatusID'] == 1) {
                                                echo '<span class="badge bg-warning text-dark">Pending</span>';
                                            } elseif ($row['StatusID'] == 2) {
                                                echo '<span class="badge bg-success">Completed</span>';
                                            } elseif ($row['StatusID'] == 3) {
                                                echo '<span class="badge bg-danger">Cancelled</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">Unknown</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row['CustomerID']; ?>">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal<?php echo $row['CustomerID']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $row['CustomerID']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title" id="deleteModalLabel<?php echo $row['CustomerID']; ?>">Confirm Deletion</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Are you sure you want to delete <strong><?php echo htmlspecialchars($row['Username']); ?></strong>?
                                                            <input type="hidden" name="customer_id" value="<?php echo $row['CustomerID']; ?>">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="delete_user" class="btn btn-danger">Yes, Delete</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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

<?php include 'includes/scripts.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
