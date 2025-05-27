<?php
session_start();
require_once '../connection/conn.php';

// Ensure the user is logged in as an admin
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Pagination setup
$limit = 5; // services per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total services
$totalResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM service");
$totalRow = mysqli_fetch_assoc($totalResult);
$totalPages = ceil($totalRow['total'] / $limit);

// Fetch paginated services
$sql = "SELECT * FROM service ORDER BY ServiceID ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// Handle delete request
if (isset($_POST['delete_service'])) {
    $service_id = $_POST['service_id'];
    
    // Delete the service
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

// Handle update of car type base prices
if (isset($_POST['update_car_type_prices'])) {
    foreach ($_POST['prices'] as $carTypeID => $newPrice) {
        $stmt = $conn->prepare("UPDATE car_types SET BasePrice = ? WHERE CarTypeID = ?");
        $stmt->bind_param("di", $newPrice, $carTypeID);
        $stmt->execute();
    }
    
    $_SESSION['success_message'] = "Car type base prices updated successfully!";
    header("Location: manage_services.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
    <style>
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <?php include 'includes/navbar.php'; ?>
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
                                <button class="btn btn-secondary ms-2" data-bs-toggle="modal" data-bs-target="#carTypePricesModal">
                                    <i class="fa fa-car"></i> Manage Car Type Prices
                                </button>
                            </div>
                            <div class="card-body">
                                <table id="example" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Service ID</th>
                                            <th>Image</th>
                                            <th>Service Name</th>
                                            <th>Base Price</th>
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
                                            <td>₱<?php echo number_format($row['BasePrice'], 2); ?></td>
                                            <td title="<?php echo htmlspecialchars($row['Description']); ?>">
                                                <?php echo strlen($row['Description']) > 50 ? substr($row['Description'], 0, 50) . '...' : $row['Description']; ?>
                                            </td>
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

        <?php include 'includes/footer.php'; ?>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="save_service.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="addServiceModalLabel">Add New Service</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
              <label for="serviceImage" class="form-label">Image</label>
              <input type="file" class="form-control" name="service_image" id="serviceImage" accept="image/*" required>
          </div>
          <div class="mb-3">
              <label for="serviceName" class="form-label">Service Name</label>
              <input type="text" class="form-control" name="service_name" id="serviceName" required>
          </div>
          <div class="mb-3">
              <label for="basePrice" class="form-label">Base Price</label>
              <input type="number" class="form-control" name="base_price" id="basePrice" step="0.01" min="0" required>
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

<!-- Car Type Prices Modal -->
<div class="modal fade" id="carTypePricesModal" tabindex="-1" aria-labelledby="carTypePricesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="manage_services.php">
        <div class="modal-header">
          <h5 class="modal-title" id="carTypePricesModalLabel">Manage Car Type Base Prices</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Car Type</th>
                  <th>Description</th>
                  <th>Current Price</th>
                  <th>New Price</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $carTypes = mysqli_query($conn, "SELECT * FROM car_types ORDER BY CarTypeID");
                while ($type = mysqli_fetch_assoc($carTypes)): ?>
                <tr>
                  <td><?php echo htmlspecialchars($type['TypeName']); ?></td>
                  <td><?php echo htmlspecialchars($type['Description']); ?></td>
                  <td>₱<?php echo number_format($type['BasePrice'], 2); ?></td>
                  <td>
                    <input type="number" name="prices[<?php echo $type['CarTypeID']; ?>]" 
                           value="<?php echo $type['BasePrice']; ?>" step="0.01" min="0" 
                           class="form-control" required>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update_car_type_prices" class="btn btn-primary">Update All Prices</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
// Auto-close alerts
setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      bsAlert.close();
    }
}, 3000);

// Edit service function
function editService(serviceID) {
    fetch('edit_service.php?id=' + serviceID)
        .then(response => response.text())
        .then(data => document.getElementById("editServiceData").innerHTML = data);
}
</script>
</body>
</html>