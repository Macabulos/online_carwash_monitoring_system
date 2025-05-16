<?php
session_start();
require_once '../connection/conn.php';

// Ensure the user is logged in as an admin
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../auth/login.php");
    exit;
}

// Handle block user request
if (isset($_POST['block_user'])) {
    $customer_id = $_POST['customer_id'];
    $stmt = $conn->prepare("UPDATE customer SET Status = 'Blocked' WHERE CustomerID = ?");
    $stmt->bind_param("i", $customer_id);
    $_SESSION['success_message'] = $stmt->execute() ? "Customer account blocked." : "Failed to block the customer.";
    header("Location: manage_user.php");
    exit();
}

// Handle unblock user request
if (isset($_POST['unblock_user'])) {
    $customer_id = $_POST['customer_id'];
    $stmt = $conn->prepare("UPDATE customer SET Status = 'Active' WHERE CustomerID = ?");
    $stmt->bind_param("i", $customer_id);
    $_SESSION['success_message'] = $stmt->execute() ? "Customer account unblocked." : "Failed to unblock the customer.";
    header("Location: manage_user.php");
    exit();
}
// Handle delete user request
if (isset($_POST['delete_user'])) {
    $customer_id = $_POST['customer_id'];
    $stmt = $conn->prepare("DELETE FROM customer WHERE CustomerID = ?");
    $stmt->bind_param("i", $customer_id);
    $_SESSION['success_message'] = $stmt->execute() ? "Customer account deleted." : "Failed to delete the customer.";
    header("Location: manage_user.php");
    exit();
}


$result = $conn->query("SELECT * FROM customer ORDER BY CustomerID ASC");
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
                <h1 class="h3 mb-3">Manage Users</h1>

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
                            <div class="card-body">
                                <table id="userTable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $row['CustomerID']; ?></td>
                                                <td><?= $row['Username']; ?></td>
                                                <td><?= $row['EmailAddress']; ?></td>
                                                <td><?= $row['Status']; ?></td>
                                                <td>
                                                    <!-- Block/Unblock Button -->
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to perform this action?');">
                                                        <input type="hidden" name="customer_id" value="<?= $row['CustomerID']; ?>">
                                                        <?php if ($row['Status'] === 'Blocked'): ?>
                                                            <button type="submit" name="unblock_user" class="btn btn-success btn-sm">
                                                                <i class="fa fa-unlock"></i> Unblock
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="submit" name="block_user" class="btn btn-warning btn-sm">
                                                                <i class="fa fa-ban"></i> Block
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="submit" name="delete_user" class="btn btn-danger btn-sm">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                    </form>

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


</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function () {
        $('#userTable').DataTable();

        // Auto dismiss alert after 3 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }
        }, 3000);
    });
</script>

</html>
