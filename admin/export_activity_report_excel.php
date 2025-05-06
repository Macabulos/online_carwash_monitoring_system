<?php
require_once '../connection/conn.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=activity_report.xls");

echo "Booking ID\tCustomer Name\tEmail\tService\tBooking Date\tStatus\n";

$query = "
SELECT 
    b.BookingID,
    c.Username,
    c.EmailAddress,
    s.ServiceName,
    b.BookingDate,
    st.StatusName
FROM bookings b
JOIN customer c ON b.CustomerID = c.CustomerID
JOIN service s ON b.ServiceID = s.ServiceID
JOIN status st ON b.StatusID = st.StatusID
ORDER BY b.BookingDate DESC
";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    echo $row['BookingID'] . "\t" .
         $row['Username'] . "\t" .
         $row['EmailAddress'] . "\t" .
         $row['ServiceName'] . "\t" .
         date('Y-m-d h:i A', strtotime($row['BookingDate'])) . "\t" .
         $row['StatusName'] . "\n";
}
?>
