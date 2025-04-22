<?php
session_start();
include '../connection/conn.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get input values
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // SQL query to select admin based on email
    $sql_query = "SELECT AdminID, Email, Password FROM admin WHERE Email = ?";
    
    if ($stmt = $conn->prepare($sql_query)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if email exists
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Verify password (not using hashing since stored password is in plain text)
            if ($password === $row['Password']) { 

                // Start session and store admin info
                $_SESSION['admin_id'] = $row['AdminID'];
                $_SESSION['email'] = $row['Email'];
                ?>
                
                <!-- Success Popup -->
                <div class="popup popup--icon -success js_success-popup popup--visible" id="popup">
                    <div class="popup__background"></div>
                    <div class="popup__content">
                        <h3 class="popup__content__title" id="popup-title">Success</h3>
                        <p id="popup-message">Login Successfully</p>
                        <script>
                            setTimeout(() => { 
                                window.location.href = "dashboard.php"; 
                            }, 1500);
                        </script>
                    </div>
                </div>

                <?php
            } else {
                ?>
                <!-- Error Popup: Invalid Password -->
                <div class="popup popup--icon -error js_error-popup popup--visible" id="popup">
                    <div class="popup__background"></div>
                    <div class="popup__content">
                        <h3 class="popup__content__title" id="popup-title">Error</h3>
                        <p id="popup-message">Invalid Email or Password</p>
                        <p><a href="admin_login.php"><button class="button button--error">Close</button></a></p>
                    </div>
                </div>
                <?php
            }
        } else {
            ?>
            <!-- Error Popup: Email Not Found -->
            <div class="popup popup--icon -error js_error-popup popup--visible" id="popup">
                <div class="popup__background"></div>
                <div class="popup__content">
                    <h3 class="popup__content__title" id="popup-title">Error</h3>
                    <p id="popup-message">No account found with this email</p>
                    <p><a href="admin_login.php"><button class="button button--error">Close</button></a></p>
                </div>
            </div>
            <?php
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/pop.css">
    <link rel="stylesheet" href="css/login.css">
    <title>Admin Login</title>
</head>
<body>
    <form action="" method="POST">
        <div class="container d-flex justify-content-center align-items-center min-vh-100">
            <div class="row border rounded-5 p-3 bg-white shadow box-area">
                <div class="col-md-6 d-flex justify-content-center align-items-center flex-column left-box">
                    <div class="featured-image mb-3">
                        <img src="../img/NEW1.png" class="img-fluid" style="width: 400px; border-radius: 10px;">
                    </div>
                </div>
                <div class="col-md-6 right-box">
                    <div class="row align-items-center">
                        <div class="header-text mb-4">
                            <h2>SIGN IN</h2>
                            <p>Welcome back! Please log in.</p>
                        </div>
                        <div class="input-group mb-3">
                            <input type="email" name="email" class="form-control form-control-lg bg-light fs-6" required placeholder="Email">
                        </div>
                        <div class="input-group mb-1">
                            <input type="password" name="password" id="password" class="form-control form-control-lg bg-light fs-6" required placeholder="Password">
                        </div>
                        <div class="input-group mb-5 d-flex justify-content-between">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="chk">
                                <label for="chk" class="form-check-label text-secondary"><small>Show Password</small></label>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <button type="submit" class="btn btn-lg btn-primary w-100 fs-6">Sign In</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        // Show Password Toggle
        document.getElementById("chk").addEventListener("change", function() {
            let passwordInput = document.getElementById("password");
            passwordInput.type = this.checked ? "text" : "password";
        });

        // Function to close the popup
        function closePopup() {
            const popup = document.getElementById('popup');
            if (popup) {
                popup.classList.remove('popup--visible');
            }
        }

        // Optional: Automatically close the popup after 5 seconds
        setTimeout(() => {
            closePopup();
        }, 5000);
    </script>
</body>
</html>
