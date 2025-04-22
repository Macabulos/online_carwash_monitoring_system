<script>
  const notificationModal = document.getElementById('notificationModal');

  notificationModal.addEventListener('show.bs.modal', function () {
    fetch('clear_notifications.php')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Remove red badge after clearing
          const badge = document.querySelector('.badge.bg-danger');
          if (badge) {
            badge.remove();
          }
        }
      })
      .catch(error => console.error('Error clearing notifications:', error));
  });
</script>
