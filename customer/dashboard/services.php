<?php
session_start();
require_once '../../connection/conn.php'; // Database connection

// Fetch available services
$query = "SELECT * FROM service";
$result = $conn->query($query);
?>

<?php include './components/header.php'; ?>
<body>
<?php include './components/sidebar.php'; ?>
<div class="content">
    <?php include './components/navbar.php'; ?>
    <h2>Available Services</h2>

    <div class="service-cards">
        <?php while ($service = $result->fetch_assoc()): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($service['ServiceName']); ?></h3>
                <p><?php echo htmlspecialchars($service['Description']); ?></p>
                <button class="book-btn">Book Now</button>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
<script src="./js/dash.js"></script>
</html>
