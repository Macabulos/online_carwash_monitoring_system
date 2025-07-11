<!-- NAVBAR -->
<nav class="navbar navbar-expand navbar-light navbar-bg">
   <a class="sidebar-toggle d-flex">
      <i class="hamburger align-self-center"></i>
   </a>

   <div class="navbar-collapse collapse justify-content-end">
      <ul class="navbar-nav navbar-align d-flex align-items-center">

         <!-- Display Email -->
         <li class="nav-item me-3">
            <span class="nav-link text-dark">
               Hi, <?php echo $_SESSION["email"]; ?>
            </span>
         </li>

         <!-- Logout Button -->
         <li class="nav-item">
            <a href="#" class="btn btn-outline-secondary" id="logout-btn">
               <i class='bx bx-log-out'></i> Log out
            </a>
         </li>

      </ul>
   </div>
</nav>

<!-- LOGOUT MODAL -->
<div id="logoutModal" class="logout-modal">
   <div class="logout-modal-content">
      
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
    // const closeModalBtn = document.getElementById('close-logout-modal');
    const confirmLogoutBtn = document.getElementById('confirm-logout');
    const cancelLogoutBtn = document.getElementById('cancel-logout');

    // Open modal on logout click
    logoutBtn.addEventListener('click', function (event) {
        event.preventDefault();
        logoutModal.style.display = 'block';
    });

    // Close modal on cancel or close button
    cancelLogoutBtn.addEventListener('click', () => {
        logoutModal.style.display = 'none';
    });

    // closeModalBtn.addEventListener('click', () => {
    //     logoutModal.style.display = 'none';
    // });

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
