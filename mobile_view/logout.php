<?php
session_start();

// Destroy all session data
$_SESSION = array();
session_destroy();

// Optional: Clear the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page or homepage
header("Location: login.php"); // Change to index.php if needed
exit();
?>
