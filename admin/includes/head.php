<?php 
include './auth.php';
include '../connection/conn.php'; // Include database connection

// Ensure admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['email'])) {
    header("Location: admin_login.php");
    exit;
}

?>

<head>
<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Check your car wash status online!">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:description" content="Check your car wash status online!">
    <meta property="og:image" content="../../assets/banner.jpg">
    
    <!-- Twitter Meta -->
    <meta property="twitter:description" content="Check your car wash status online!">
    <meta property="twitter:image" content="../../assets/banner.jpg">

	<link rel="shortcut icon" href="../../admin/img/ico.png" />
    <!-- <link rel="stylesheet" href="css/admin-style.css"> -->

	<title>Duck'z Auto Detailing & Car Wash</title>

	<!-- Stylesheets -->
	<link href="css/app.css" rel="stylesheet">
	<!-- <link rel="stylesheet" type="text/css" href="css/datatables.min.css"/> -->

	<!-- FontAwesome & Chart.js -->
	<!-- <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->

    <!-- Required JS -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome (optional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    

</head>
