<!-- NAVBAR -->
<nav class="navbar navbar-expand navbar-light navbar-bg">
   <a class="sidebar-toggle d-flex">
      <i class="hamburger align-self-center"></i>
   </a>
   <form method="POST" action="" class="form-inline d-none d-sm-inline-block">
      <div class="input-group input-group-navbar">
         <input type="text" class="form-control" placeholder="Search Status…" aria-label="Search Status" name="track">
         <div class="input-group-append">
            <button class="btn" type="submit">
               <i class="align-middle" data-feather="search"></i>
            </button>
         </div>
      </div>
   </form>
   <div class="navbar-collapse collapse">
      <ul class="navbar-nav navbar-align">
         <li class="nav-item dropdown">
            <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
               <i class="align-middle" data-feather="settings"></i>
            </a>
            <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
               <span class="text-dark">Hi, <?php echo $_SESSION["email"]; ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-end">
               <a class="dropdown-item" href="#" id="logout-btn">
                  <i class='bx bx-log-out'></i> Log out
               </a>
            </div>
         </li>
      </ul>
   </div>
</nav>

<!-- LOGOUT MODAL -->
<div id="logoutModal" class="logout-modal">
   <div class="logout-modal-content">
      <span class="close" id="close-logout-modal">&times;</span>
      <h2>Logout Confirmation</h2>
      <p>Are you sure you want to log out?</p>
      <button class="confirm-btn" id="confirm-logout">Yes</button>
      <button class="cancel-btn" id="cancel-logout">No</button>
   </div>
</div>



<!-- SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const logoutBtn = document.getElementById('logout-btn');
    const logoutModal = document.getElementById('logoutModal');
    const closeModalBtn = document.getElementById('close-logout-modal');
    const confirmLogoutBtn = document.getElementById('confirm-logout');
    const cancelLogoutBtn = document.getElementById('cancel-logout');

    // Open modal on logout click
    logoutBtn.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation(); // Prevent dropdown from closing
        logoutModal.style.display = 'block';
    });

    // Close modal on cancel or close button
    cancelLogoutBtn.addEventListener('click', () => {
        logoutModal.style.display = 'none';
    });

    closeModalBtn.addEventListener('click', () => {
        logoutModal.style.display = 'none';
    });

    // Confirm logout
    confirmLogoutBtn.addEventListener('click', () => {
        window.location.href = 'logout.php';
    });

    // Close modal on outside click
    window.addEventListener('click', function (event) {
        if (event.target === logoutModal) {
            logoutModal.style.display = 'none';
        }
    });

    // Optional: Close modal on ESC key
    document.addEventListener('keydown', function (event) {
        if (event.key === "Escape") {
            logoutModal.style.display = 'none';
        }
    });

    // Sidebar toggle functionality
    const sidebar = document.getElementById("sidebar");
    const toggleButton = document.querySelector(".sidebar-toggle");

    if (localStorage.getItem("sidebar-collapsed") === "true") {
        sidebar.classList.add("collapsed");
    }

    toggleButton.addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");
        localStorage.setItem("sidebar-collapsed", sidebar.classList.contains("collapsed"));
    });
});
</script>

<!-- Required JS -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
