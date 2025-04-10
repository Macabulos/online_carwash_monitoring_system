"# online_carwash_monitoring_system" 
"update of this code "


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