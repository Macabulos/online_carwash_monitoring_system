<?php
session_start();
session_unset(); // Clear session variables
session_destroy(); // Destroy the session
header("Location: admin_login.php"); // Redirect to login page
exit();
?>
