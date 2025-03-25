<?php
include '../connection/conn.php';

if (isset($_GET['id'])) {
    $service_id = $_GET['id'];
    $sql = "SELECT * FROM service WHERE ServiceID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();
?>
    <form action="update_service.php" method="POST">
        <input type="hidden" name="service_id" value="<?php echo $service['ServiceID']; ?>">
        <div class="form-group">
            <label>Service Name</label>
            <input type="text" name="service_name" class="form-control" value="<?php echo $service['ServiceName']; ?>" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="3" required><?php echo $service['Description']; ?></textarea>
        </div>
        <button type="submit" class="btn btn-warning">Update Service</button>
    </form>
<?php } ?>
