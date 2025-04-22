<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pricing - Duck’z Auto Detailing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
        }
        .pricing-card {
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .pricing-card:hover {
            transform: scale(1.03);
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .price {
            font-size: 2.5rem;
            color: #0d6efd;
        }
        .badge-top {
            position: absolute;
            top: -10px;
            right: -10px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<!-- <?php include 'navbar.php'; ?> -->

<!-- Pricing Section -->
<section class="py-5 text-center">
    <div class="container">
        <h2 class="mb-4">Our Pricing Packages</h2>
        <p class="mb-3 text-muted">Choose the package that fits your car’s needs. We provide high-quality detailing at an affordable price.</p>

        <!-- Back Button -->
        <a href="index.php" class="btn btn-outline-secondary mb-4">
            ← Back to Home
        </a>

        <div class="row g-4">
            <!-- Basic -->
            <div class="col-md-4">
                <div class="card pricing-card p-4 position-relative">
                    <h5>Basic Wash</h5>
                    <p class="price">₱299</p>
                    <ul class="list-unstyled">
                        <li>✔ Exterior Hand Wash</li>
                        <li>✔ Tire Shine</li>
                        <li>✔ Quick Interior Vacuum</li>
                    </ul>
                    <a href="../mobile_view/login.php" class="btn btn-outline-primary mt-3">Choose Plan</a>
                </div>
            </div>

            <!-- Premium -->
            <div class="col-md-4">
                <div class="card pricing-card p-4 border-primary position-relative">
                    <span class="badge bg-primary badge-top">Most Popular</span>
                    <h5>Premium Detailing</h5>
                    <p class="price">₱699</p>
                    <ul class="list-unstyled">
                        <li>✔ Full Exterior & Interior Detail</li>
                        <li>✔ Wax & Polish</li>
                        <li>✔ Engine Cleaning</li>
                        <li>✔ Scent Application</li>
                    </ul>
                    <a href="../mobile_view/login.php" class="btn btn-primary mt-3">Choose Plan</a>
                </div>
            </div>

            <!-- Ultimate -->
            <div class="col-md-4">
                <div class="card pricing-card p-4">
                    <h5>Ultimate Care</h5>
                    <p class="price">₱999</p>
                    <ul class="list-unstyled">
                        <li>✔ Premium Package +</li>
                        <li>✔ Ceramic Coating</li>
                        <li>✔ Under Chassis Wash</li>
                        <li>✔ Paint Protection</li>
                    </ul>
                    <a href="../mobile_view/login.php" class="btn btn-outline-primary mt-3">Choose Plan</a>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Footer -->
<!-- <?php include 'footer.php'; ?> -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
