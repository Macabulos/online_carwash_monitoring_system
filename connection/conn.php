<?php
$host = "localhost"; // Database host
$dbUsername = "root"; // Database username
$dbPassword = ""; // Database password
$dbName = "dbcarwash"; // Database name

// Create connection
$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>