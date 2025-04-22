<?php
session_start();
require_once '../connection/conn.php';

// Ensure the user is logged in as an admin
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../auth/login.php");
    exit;
}

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

// Fetch all customers
// Pagination setup
$limit = 10; // number of users per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

// Count total users
$countSql = "SELECT COUNT(*) AS total FROM customer";
$countResult = mysqli_query($conn, $countSql);
$totalUsers = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalUsers / $limit);

// Fetch users with LIMIT
$sql = "SELECT * FROM customer ORDER BY CustomerID ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

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
                    <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php elseif (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
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
                                            <th>Age</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?php echo $row['CustomerID']; ?></td>
                                                <td><?php echo $row['Username']; ?></td>
                                                <td><?php echo $row['EmailAddress']; ?></td>
                                                <td><?php echo $row['Age']; ?></td>
                                                <td>
                                                    <button type="button"
                                                            class="btn btn-danger btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal"
                                                            data-id="<?php echo $row['CustomerID']; ?>">
                                                        <i class="fa fa-trash"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <!-- Pagination Controls -->
                                <nav>
                                <ul class="pagination justify-content-end mt-3">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Delete User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete this user?
                            <input type="hidden" name="customer_id" id="deleteCustomerId">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="delete_user" class="btn btn-danger">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
    $(document).ready(function () {
        $('#userTable').DataTable();

    });
    setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      bsAlert.close();
    }
  }, 3000);
    // Populate delete modal with user ID
    $('#deleteModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const customerId = button.data('id');
        $(this).find('#deleteCustomerId').val(customerId);
    });
</script>
</body>
</html>
