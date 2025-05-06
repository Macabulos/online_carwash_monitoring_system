// Toggle password visibility
document.getElementById("togglePassword").addEventListener("click", function() {
    const passwordField = document.getElementById("password");
    const type = passwordField.type === "password" ? "text" : "password";
    passwordField.type = type;
    this.classList.toggle("fa-eye-slash");
});

document.getElementById("toggleConfirmPassword").addEventListener("click", function() {
    const confirmPasswordField = document.getElementById("confirm_password");
    const type = confirmPasswordField.type === "password" ? "text" : "password";
    confirmPasswordField.type = type;
    this.classList.toggle("fa-eye-slash");
});

// Client-side validation
function validateForm() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        alert("Passwords do not match!");
        return false;
    }
    
    if (password.length < 8 || !/[a-zA-Z]/.test(password) || !/[0-9]/.test(password)) {
        alert("Password must be at least 8 characters long and contain both letters and numbers.");
        return false;
    }

    return true;
}