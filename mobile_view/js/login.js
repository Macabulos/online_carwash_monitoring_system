// add ingb if possible 


// Toggle password visibility
document.getElementById("togglePassword").addEventListener("click", function() {
    const passwordField = document.getElementById("password");
    const type = passwordField.type === "password" ? "text" : "password";
    passwordField.type = type;
    this.classList.toggle("fa-eye-slash");
});

// Client-side validation
document.getElementById("loginForm").addEventListener("submit", function(event) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    if (!email || !password) {
        event.preventDefault();
        alert("Please fill in both fields!");
    }
});

// Countdown for redirection
let seconds = 2;
const countdownEl = document.getElementById('countdown');
if (countdownEl) {
    const timer = setInterval(() => {
        countdownEl.textContent = `Redirecting in ${seconds} second${seconds !== 1 ? 's' : ''}...`;
        seconds--;
        if (seconds < 0) clearInterval(timer);
    }, 1000);
}