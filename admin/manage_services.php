<?php
session_start();
require_once '../connection/conn.php'; // Include database connection

// Ensure the user is logged in as an admin
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../auth/login.php");
    exit;
}

// Fetch all services
$sql = "SELECT * FROM service ORDER BY ServiceID ASC";
$result = mysqli_query($conn, $sql);

// Handle delete request
if (isset($_POST['delete_service'])) {
    $service_id = $_POST['service_id'];
    $delete_query = "DELETE FROM service WHERE ServiceID = ?";

    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $service_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Service deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting service.";
    }
    header("Location: manage_services.php");
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
                <h1 class="h3 mb-3">Manage Services</h1>

                <!-- Alert Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                <?php elseif (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                                    <i class="fa fa-plus"></i> Add Service
                                </button>
                            </div>
                            <div class="card-body">
                                <table id="example" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Service ID</th>
                                            <th>Image</th>
                                            <th>Service Name</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $row['ServiceID']; ?></td>
                                            <td><img src="../uploads/services/<?php echo $row['ImagePath']; ?>" width="80"></td>
                                            <td><?php echo $row['ServiceName']; ?></td>
                                            <td><?php echo $row['Description']; ?></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" onclick="editService(<?php echo $row['ServiceID']; ?>)" data-bs-toggle="modal" data-bs-target="#editServiceModal">
                                                    <i class="fa fa-edit"></i> Edit
                                                </button>
                                                <form action="manage_services.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="service_id" value="<?php echo $row['ServiceID']; ?>">
                                                    <button type="submit" name="delete_service" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this service?')">
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

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="save_service.php" method="POST" enctype="multipart/form-data"> <!-- FIXED HERE -->
        <div class="modal-header">
          <h5 class="modal-title" id="addServiceModalLabel">Add New Service</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
              <label for="serviceImage" class="form-label">Image</label>
              <input type="file" class="form-control" name="service_image" id="serviceImage" accept="image/*">
          </div>
          <div class="mb-3">
              <label for="serviceName" class="form-label">Service Name</label>
              <input type="text" class="form-control" name="service_name" id="serviceName" required>
          </div>
          <div class="mb-3">
              <label for="serviceDesc" class="form-label">Description</label>
              <textarea class="form-control" name="description" id="serviceDesc" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save Service</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editServiceModalLabel">Edit Service</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="editServiceData">
        <!-- AJAX content will be loaded here -->
      </div>
    </div>
  </div>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
function editService(serviceID) {
    fetch('edit_service.php?id=' + serviceID)
        .then(response => response.text())
        .then(data => document.getElementById("editServiceData").innerHTML = data);
}
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
