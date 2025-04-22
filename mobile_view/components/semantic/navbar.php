

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">

    <div class="container-fluid">
      <h1 class="navbar-brand">Online Booking Transaction Monitoring System</h1>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="./dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="./available.php">Available Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="./booking.php">Book a Service</a></li>
        </ul>

        <div class="d-flex align-items-center gap-2">
          <!-- Notification Button with Red Dot -->
          <button class="btn btn-outline-light position-relative"
        data-bs-toggle="modal" data-bs-target="#notificationModal"
        aria-label="View Notifications">
  <i class="bi bi-bell fs-5"></i>
  <?php if (!empty($_SESSION['notification_count']) && $_SESSION['notification_count'] > 0): ?>

    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
    <?= $_SESSION['notification_count'] ?>

      <span class="visually-hidden">unread messages</span>
    </span>
  <?php endif; ?>
</button>

          <!-- Profile Dropdown -->
          <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              👤 Profile
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
              <li><a class="dropdown-item" href="./profile.php">Edit Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">🚪 Logout</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- Notification Modal -->
  <div class="modal fade" id="notificationModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Notifications</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php if (!empty($_SESSION['customer_notifications'])): ?>
            <ul class="list-group list-group-flush">
              <?php foreach ($_SESSION['customer_notifications'] as $message): ?>
                <li class="list-group-item"><?= $message ?></li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p>No new notifications</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- LOGOUT MODAL -->
  <div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Logout</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">Are you sure you want to log out?</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
          <a href="../logout.php" class="btn btn-danger">Yes, Logout</a>
        </div>
      </div>
    </div>
  </div>

  <!-- COLLAPSE HANDLER SCRIPT -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
      const navbarCollapse = document.getElementById('navbarNav');

      navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
          const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
          if (bsCollapse) {
            bsCollapse.hide();
          }
        });
      });
    });
  </script>


