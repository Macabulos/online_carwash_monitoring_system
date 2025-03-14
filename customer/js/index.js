 // JavaScript to toggle password visibility
 const togglePassword = document.querySelector('#togglePassword');
 const password = document.querySelector('#password');

 const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
 const confirmPassword = document.querySelector('#c_password');

 togglePassword.addEventListener('click', function () {
   // Toggle the type attribute
   const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
   password.setAttribute('type', type);

   // Toggle the eye icon
   this.classList.toggle('fa-eye-slash');
 });

 toggleConfirmPassword.addEventListener('click', function () {
   // Toggle the type attribute
   const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
   confirmPassword.setAttribute('type', type);

   // Toggle the eye icon
   this.classList.toggle('fa-eye-slash');
 });



 
 // login js here 
//  document.querySelector('form').addEventListener('submit', function(event) {
//     event.preventDefault(); // Prevent the form from submitting immediately

//     // Show the loading animation
//     document.querySelector('.loading').style.display = 'block';

//     // Simulate a successful login (replace this with your actual login logic)
//     setTimeout(function() {
//         window.location.href = 'dashboard.php'; // Redirect to dashboard after successful login
//     }, 2000); // Simulate a 2-second delay
// });