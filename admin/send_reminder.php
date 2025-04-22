<?php
session_start();
require_once '../connection/conn.php';

if (isset($_POST['booking_id']) && is_numeric($_POST['booking_id'])) {
    $booking_id = (int) $_POST['booking_id'];

    $query = "SELECT c.EmailAddress, c.Username, s.ServiceName, b.BookingDate
              FROM bookings b
              JOIN customer c ON b.CustomerID = c.CustomerID
              JOIN service s ON b.ServiceID = s.ServiceID
              WHERE b.BookingID = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $booking = $result->fetch_assoc();

        $to = $booking['EmailAddress'];
        $username = htmlspecialchars($booking['Username']);
        $service = htmlspecialchars($booking['ServiceName']);
        $bookingTime = date("F j, Y g:i A", strtotime($booking['BookingDate']));

        $subject = "⏰ Booking Reminder – Duck’z Auto Detailing";
        $message = "Hi $username,\n\n"
                 . "This is a friendly reminder that your booking for \"$service\" is scheduled on $bookingTime.\n\n"
                 . "We look forward to serving you!\n\n"
                 . "- Duck’z Auto Detailing & Car Wash";

        $headers = "From: Duck’z Auto Detailing <noreply@yourdomain.com>\r\n";
        $headers .= "Reply-To: support@yourdomain.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/plain; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        if (mail($to, $subject, $message, $headers)) {
            $_SESSION['success_message'] = "Reminder successfully sent to <strong>$to</strong>.";
        } else {
            $_SESSION['error_message'] = "Failed to send reminder email. Please try again.";
        }
    } else {
        $_SESSION['error_message'] = "Booking not found.";
    }

    $stmt->close();
} else {
    $_SESSION['error_message'] = "Invalid booking ID.";
}

header("Location: manage_bookings.php");
exit();
