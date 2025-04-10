<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <!-- Brand -->
    <h1 class="navbar-brand">Online Booking Transaction Monitoring System</h1>

    <!-- Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Collapsible Content -->
    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      <!-- Links -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="./dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="./available.php">Available Bookings</a></li>
        <li class="nav-item"><a class="nav-link" href="./booking.php">Book a Service</a></li>
      </ul>

      <!-- Right side buttons -->
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#notificationModal">
          🔔 Notifications
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

<!-- NOTIFICATION MODAL -->
<div class="modal fade" id="notificationModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Notifications</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>No new notifications</p>
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
      <div class="modal-body">
        Are you sure you want to log out?
      </div>
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
