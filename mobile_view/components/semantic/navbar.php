<?php 
require_once './image.php';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="./dashboard.php">
      <img src="./img/NEW1.png" alt="Logo" style="height: 50px;">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto d-flex align-items-center gap-3">
        <li class="nav-item"><a class="nav-link" href="./dashboard.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="./available.php">Services</a></li>
        <li class="nav-item"><a class="nav-link" href="./booking.php">Book a Service</a></li>

        <div class="navbar-actions">
          <li class="nav-item">
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
          </li>

          <li class="nav-item dropdown">
            <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <?php if (!empty($customer['ProfilePicture'])): ?>
                <img src="<?= $customer['ProfilePicture'] ?>" alt="Profile" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">
              <?php else: ?>
                👤
              <?php endif; ?>
              Profile
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
              <li><a class="dropdown-item" href="./profile.php">Edit Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">🚪 Logout</a></li>
            </ul>
          </li>


        </div>
      </ul>
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
          <p class="text-muted">No new notifications</p>
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

<style>
/* === Navbar Styling === */
.navbar {
  padding: 10px;
}

.navbar-brand img {
  height: 45px;
  width: auto;
  object-fit: contain;
  border-radius: 10px;
}

.navbar-nav {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: flex-end;
  width: 100%;
  gap: 15px;
}

.navbar-nav .nav-link {
  font-size: 1rem;
  padding: 8px 12px;
  color: #fff;
}

.navbar .btn-outline-light {
  padding: 6px 12px;
  font-size: 0.9rem;
  white-space: nowrap;
}

.dropdown-menu {
  border-radius: 10px;
}

/* Notification and Profile Container */
.navbar-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* === Responsive Adjustments === */
@media (max-width: 992px) {
  .navbar-nav {
    gap: 12px;
  }

  .nav-link {
    font-size: 0.9rem;
  }

  .navbar .btn-outline-light {
    padding: 5px 10px;
    font-size: 0.85rem;
  }

  .navbar-actions {
    gap: 8px;
  }
}

@media (max-width: 768px) {
  .navbar-nav {
    flex-wrap: wrap;
    justify-content: center;
  }

  .navbar-actions {
    order: 1;
    margin-left: auto;
  }
}

@media (max-width: 576px) {
  .navbar-nav {
    gap: 8px;
  }

  .navbar .btn-outline-light {
    padding: 4px 8px;
    font-size: 0.8rem;
  }

  .navbar-actions {
    gap: 6px;
  }

  .badge {
    font-size: 0.6rem;
    padding: 0.2em 0.4em;
  }
}

@media (max-width: 400px) {
  .navbar-nav {
    justify-content: space-between;
  }

  .navbar-actions {
    margin-left: 0;
  }

  .navbar .btn-outline-light {
    padding: 3px 6px;
    font-size: 0.75rem;
  }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
  const navbarCollapseEl = document.getElementById('navbarNav');
  const bsCollapse = new bootstrap.Collapse(navbarCollapseEl, {
    toggle: false
  });

  navLinks.forEach(function (link) {
    link.addEventListener('click', function () {
      if (navbarCollapseEl.classList.contains('show')) {
        bsCollapse.hide();
      }
    });
  });

  const notificationModal = document.getElementById('notificationModal');
  notificationModal.addEventListener('hidden.bs.modal', () => {
    fetch('clear_notifications.php');
  });
});
</script>