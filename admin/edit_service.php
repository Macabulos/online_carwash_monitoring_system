<?php
require_once '../connection/conn.php';

$service_id = $_GET['id'];
$service = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM service WHERE ServiceID = $service_id"));
?>

<form action="update_service.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="service_id" value="<?php echo $service['ServiceID']; ?>">
    <div class="mb-3">
        <label class="form-label">Current Image</label><br>
        <img src="../uploads/services/<?php echo $service['ImagePath']; ?>" width="100" class="mb-2">
        <input type="file" class="form-control" name="service_image" accept="image/*">
        <small class="text-muted">Leave blank to keep current image</small>
    </div>
    <div class="mb-3">
        <label class="form-label">Service Name</label>
        <input type="text" class="form-control" name="service_name" value="<?php echo htmlspecialchars($service['ServiceName']); ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Base Price</label>
        <input type="number" class="form-control" name="base_price" step="0.01" min="0" value="<?php echo $service['BasePrice']; ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" rows="3" required><?php echo htmlspecialchars($service['Description']); ?></textarea>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update Service</button>
    </div>
</form>