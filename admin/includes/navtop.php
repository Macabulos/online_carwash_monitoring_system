<nav class="navbar navbar-expand navbar-light navbar-bg">
   <a class="sidebar-toggle d-flex">
      <i class="hamburger align-self-center"></i>
   </a>
   <!-- <form method="POST" action="" class="form-inline d-none d-sm-inline-block">
      <div class="input-group input-group-navbar">
         <input type="text" class="form-control" placeholder="Search Status…" aria-label="Search Status" name="track">
         <div class="input-group-append">
            <button class="btn" type="submit">
               <i class="align-middle" data-feather="search"></i>
            </button>
         </div>
      </div>
   </form> -->
   <div class="navbar-collapse collapse">
      <ul class="navbar-nav navbar-align">
         <li class="nav-item dropdown">
            <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-toggle="dropdown">
               <i class="align-middle" data-feather="settings"></i>
            </a>
            <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-toggle="dropdown">
               <span class="text-dark">Hi, <?php echo $_SESSION["email"]; ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
               <!-- <a class="dropdown-item" href="pages-profile.html">
                  <i class="align-middle mr-1" data-feather="user"></i> Update Profile
               </a> -->
               <div class="dropdown-menu dropdown-menu-end"></div>
               <a class="dropdown-item" href="#" id="logout-btn">
                  <i class='bx bx-log-out'></i> Log out
               </a>
            </div>
         </li>
      </ul>
   </div>
</nav>
<style>
/* Updated Modal Styles */
.logout-modal {
    display: none;
    position: fixed;
    z-index: 100;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.6); /* Darker background */
    transition: opacity 0.3s ease-in-out; /* Smooth fade in/out */
}

.logout-modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 30px;
    border-radius: 12px;
    width: 90%;
    max-width: 450px;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out;
}

.logout-modal-content h2 {
    margin-top: 0;
    font-family: 'Arial', sans-serif;
    font-size: 24px;
    color: #333;
}

.logout-modal-content p {
    font-family: 'Arial', sans-serif;
    font-size: 16px;
    color: #666;
    margin-bottom: 30px;
}

.logout-modal-content .close {
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 20px;
    color: #888;
    cursor: pointer;
}

.logout-modal-content .confirm-btn {
    padding: 12px 25px;
    background-color: #e74c3c;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-family: 'Arial', sans-serif;
    font-size: 16px;
    margin-right: 10px;
    transition: background-color 0.2s ease-in-out;
}

.logout-modal-content .cancel-btn {
    padding: 12px 25px;
    background-color: #bdc3c7;
    color: #333;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-family: 'Arial', sans-serif;
    font-size: 16px;
    transition: background-color 0.2s ease-in-out;
}

.logout-modal-content .confirm-btn:hover {
    background-color: #c0392b;
}

.logout-modal-content .cancel-btn:hover {
    background-color: #95a5a6;
}

</style>

<!-- Modal Structure -->
<div id="logoutModal" class="logout-modal">
   <div class="logout-modal-content">
      <span class="close" id="close-logout-modal">&times;</span>
      <h2>Logout Confirmation</h2>
      <p>Are you sure you want to log out?</p>
      <button class="confirm-btn" id="confirm-logout">Yes</button>
      <button class="cancel-btn" id="cancel-logout">No</button>
   </div>
</div>

<script>
   document.addEventListener('DOMContentLoaded', function() {
      const logoutBtn = document.getElementById('logout-btn');  // Button to trigger the modal
      const logoutModal = document.getElementById('logoutModal'); // Modal element
      const closeModalBtn = document.getElementById('close-logout-modal'); // Close button in modal
      const confirmLogoutBtn = document.getElementById('confirm-logout'); // Yes (confirm) button
      const cancelLogoutBtn = document.getElementById('cancel-logout'); // No (cancel) button

      // Open modal when logout is clicked
      logoutBtn.addEventListener('click', function(event) {
         event.preventDefault();
         logoutModal.style.display = 'block';
      });

      // Close modal when clicking "No" or the close button
      cancelLogoutBtn.addEventListener('click', function() {
         logoutModal.style.display = 'none';
      });

      closeModalBtn.addEventListener('click', function() {
         logoutModal.style.display = 'none';
      });

      // Confirm logout when "Yes" button is clicked
      confirmLogoutBtn.addEventListener('click', function() {
         window.location.href = 'logout.php'; // Redirect to logout page
      });

      // Close modal if clicked outside of modal content
      window.addEventListener('click', function(event) {
         if (event.target === logoutModal) {
            logoutModal.style.display = 'none';
         }
      });
   });


   document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebar");
    const toggleButton = document.querySelector(".sidebar-toggle");

    // Check Local Storage for Sidebar State
    if (localStorage.getItem("sidebar-collapsed") === "true") {
        sidebar.classList.add("collapsed");
    }

    // Toggle Sidebar on Click
    toggleButton.addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");

        // Save State in Local Storage
        if (sidebar.classList.contains("collapsed")) {
            localStorage.setItem("sidebar-collapsed", "true");
        } else {
            localStorage.setItem("sidebar-collapsed", "false");
        }
    });
});

</script>
