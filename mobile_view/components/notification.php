<?php

 if (session_status() === PHP_SESSION_NONE) {
     session_start();
 }
 
 include '../../connection/conn.php';
 require_once '../../admin/includes/reference.php';
 
  $notifications = [];
  $notificationCount = 0;

  // Reset notification session if not already set
  if (isset($_SESSION['CustomerID'])) {

      $customerId = $_SESSION['CustomerID'];

      $query = "SELECT b.BookingID, b.BookingDate, s.ServiceName, st.StatusName
                FROM bookings b
                JOIN service s ON b.ServiceID = s.ServiceID
                JOIN status st ON b.StatusID = st.StatusID
                WHERE b.CustomerID = ? AND b.StatusID IN (2, 3, 4)
                ORDER BY b.BookingDate DESC
                LIMIT 5";

      $stmt = $conn->prepare($query);
      $stmt->bind_param("i", $customerId);
      $stmt->execute();
      $result = $stmt->get_result();

      while ($row = $result->fetch_assoc()) {
          $bookingDate = date("F j, Y", strtotime($row['BookingDate']));
          $serviceName = htmlspecialchars($row['ServiceName']);
          $statusName = $row['StatusName'];
          $bookingReference = generateBookingReference($row['BookingID']);

          switch ($statusName) {
              case 'Completed':
                  $notifications[] = "✅ Booking <strong>$bookingReference</strong> for <strong>$serviceName</strong> on <strong>$bookingDate</strong> is completed!";
                  break;
              case 'Cancelled':
                  $notifications[] = "❌ Booking <strong>$bookingReference</strong> for <strong>$serviceName</strong> on <strong>$bookingDate</strong> has been cancelled.";
                  break;
              case 'In Progress':
                  $notifications[] = "🔧 Booking <strong>$bookingReference</strong> for <strong>$serviceName</strong> on <strong>$bookingDate</strong> is now in progress.";
                  break;
          }
      }

      $_SESSION['customer_notifications'] = $notifications;
      $_SESSION['notification_count'] = count($notifications);
  }

  $hasNotification = !empty($_SESSION['customer_notifications']);
  $notificationCount = $_SESSION['notification_count'];
  ?>