<?php
session_start();
require_once '../connection/conn.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    die("Unauthorized access");
}

// Set timezone if needed
date_default_timezone_set('Asia/Manila');

// Get and sanitize month/year
$month = isset($_POST['month']) ? (int)$_POST['month'] : (int)date('m');
$year = isset($_POST['year']) ? (int)$_POST['year'] : (int)date('Y');

// SQL query to retrieve booking data
$query = "
SELECT 
    b.BookingID,
    c.Username,
    c.EmailAddress,
    s.ServiceName,
    b.BookingDate,
    st.StatusName,
    ct.TypeName,
    b.CarQuantity,
    (CASE 
        WHEN s.ServiceName LIKE '%Carwash%' THEN ct.BasePrice * b.CarQuantity
        ELSE s.BasePrice * b.CarQuantity
    END) AS TotalPrice
FROM bookings b
JOIN customer c ON b.CustomerID = c.CustomerID
JOIN service s ON b.ServiceID = s.ServiceID
JOIN status st ON b.StatusID = st.StatusID
LEFT JOIN car_types ct ON b.CarTypeID = ct.CarTypeID
WHERE MONTH(b.BookingDate) = ? AND YEAR(b.BookingDate) = ?
ORDER BY b.BookingDate DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$result = $stmt->get_result();

// Set headers to prompt Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="booking_report_' . date('F_Y', mktime(0, 0, 0, $month, 1, $year)) . '.xls"');

// Output table
echo "<table border='1'>";
echo "<tr>
        <th>Booking ID</th>
        <th>Customer</th>
        <th>Email</th>
        <th>Service</th>
        <th>Booking Date</th>
        <th>Car Type</th>
        <th>Quantity</th>
        <th>Status</th>
        <th>Amount</th>
      </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>" . htmlspecialchars($row['BookingID']) . "</td>
            <td>" . htmlspecialchars($row['Username']) . "</td>
            <td>" . htmlspecialchars($row['EmailAddress']) . "</td>
            <td>" . htmlspecialchars($row['ServiceName']) . "</td>
            <td>" . date('M j, Y h:i A', strtotime($row['BookingDate'])) . "</td>
            <td>" . htmlspecialchars($row['TypeName'] ?? 'N/A') . "</td>
            <td>" . htmlspecialchars($row['CarQuantity']) . "</td>
            <td>" . htmlspecialchars($row['StatusName']) . "</td>
            <td>₱" . number_format((float)$row['TotalPrice'], 2) . "</td>
          </tr>";
}
echo "</table>";
exit;
?>
