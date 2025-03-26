<?php
require_once '../connection/conn.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p class='text-danger'>Invalid request.</p>";
    exit;
}

$customerID = intval($_GET['id']);

// Fetch booking details with status name
$stmt = $conn->prepare("SELECT c.CustomerID, c.Username, c.ServiceID, c.BookingDate, s.ServiceName, c.StatusID 
                        FROM customer c 
                        LEFT JOIN service s ON c.ServiceID = s.ServiceID
                        WHERE c.CustomerID = ?");
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) {
    echo "<p class='text-danger'>Booking not found.</p>";
    exit;
}

// Fetch available statuses
$statusQuery = "SELECT StatusID, StatusName FROM status";
$statusResult = mysqli_query($conn, $statusQuery);
?>

<!-- ✅ Edit Booking Form (Only Status Editable) -->
<form id="updateBookingForm">
    <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($booking['CustomerID']); ?>">

    <label>Customer Name</label>
    <input type="text" class="form-control" value="<?php echo htmlspecialchars($booking['Username']); ?>" readonly>

    <label>Service</label>
    <input type="text" class="form-control" value="<?php echo htmlspecialchars($booking['ServiceName'] ?? 'N/A'); ?>" readonly>

    <label>Booking Date</label>
    <input type="datetime-local" class="form-control" value="<?php echo !empty($booking['BookingDate']) ? date('Y-m-d\TH:i', strtotime($booking['BookingDate'])) : ''; ?>" readonly>

    <label>Status</label>
    <select class="form-control" name="status_id" required>
        <?php while ($status = mysqli_fetch_assoc($statusResult)): ?>
            <option value="<?php echo $status['StatusID']; ?>" <?php echo ($status['StatusID'] == $booking['StatusID']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($status['StatusName']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit" class="btn btn-success mt-2">Save Changes</button>
</form>

<!-- ✅ JavaScript to handle form submission -->
<script>
document.getElementById("updateBookingForm").addEventListener("submit", function (e) {
    e.preventDefault();

    let formData = new FormData(this);

    fetch("update_booking.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        if (result.trim() === "success") {
            alert("Booking status updated successfully!");
            document.getElementById("editBookingModal").click(); // Close modal
            location.reload(); // Reload page
        } else {
            alert("Error updating status: " + result);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Error updating status.");
    });
});
</script>
