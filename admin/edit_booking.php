<?php
require_once '../connection/conn.php';

if (isset($_GET['id'])) {
    $customerID = intval($_GET['id']);

    // Fetch booking details
    $stmt = $conn->prepare("SELECT c.CustomerID, c.Username, s.ServiceName, c.BookingDate, c.Status 
                            FROM customer c 
                            LEFT JOIN service s ON c.ServiceID = s.ServiceID 
                            WHERE c.CustomerID = ?");
    $stmt->bind_param("i", $customerID);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();
}
?>

<form id="updateBookingForm" method="POST">
    <input type="hidden" name="customer_id" value="<?php echo $booking['CustomerID']; ?>">
    <label>Customer Name</label>
    <input type="text" class="form-control" value="<?php echo htmlspecialchars($booking['Username']); ?>" readonly>

    <label>Service</label>
    <select class="form-control" name="service_id">
        <?php
        $services = $conn->query("SELECT * FROM service");
        while ($service = $services->fetch_assoc()):
        ?>
            <option value="<?php echo $service['ServiceID']; ?>" <?php echo ($service['ServiceID'] == $booking['ServiceID']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($service['ServiceName']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Booking Date</label>
    <input type="datetime-local" class="form-control" name="booking_date" value="<?php echo date('Y-m-d\TH:i', strtotime($booking['BookingDate'])); ?>">

    <label>Status</label>
    <select class="form-control" name="status">
        <option value="Pending" <?php echo ($booking['Status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
        <option value="Completed" <?php echo ($booking['Status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
        <option value="Cancelled" <?php echo ($booking['Status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
    </select>

    <button type="submit" class="btn btn-success mt-2">Save Changes</button>
</form>

<script>
$("#updateBookingForm").submit(function(e) {
    e.preventDefault();
    $.ajax({
        url: "update_booking.php",
        type: "POST",
        data: $(this).serialize(),
        success: function(response) {
            alert("Booking updated successfully!");
            location.reload();
        }
    });
});
</script>
