<?php
    require_once "includes/session_init.php";
    
    // Check for messages
    $error = isset($_SESSION['forgot_error']) ? $_SESSION['forgot_error'] : '';
    $success = isset($_SESSION['forgot_success']) ? $_SESSION['forgot_success'] : '';
    
    if (!empty($error)) unset($_SESSION['forgot_error']);
    if (!empty($success)) unset($_SESSION['forgot_success']);
    
    // If user is already logged in, redirect them
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard.php");
        die();
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>WIL Connect | Forgot Password</title>
        <link rel="stylesheet" href="css/auth_pages.css">
        <style>
            .error-message {
                border: 1px solid #dc3545;
                border-radius: 6px;
                padding: 10px 14px;
                color: #dc3545;
                background-color: #ffe2ea;
                font-size: 13px;
                margin: 10px 0;
            }
            
            .success-message {
                border: 1px solid #28a745;
                border-radius: 6px;
                padding: 10px 14px;
                color: #155724;
                background-color: #d4edda;
                font-size: 13px;
                margin: 10px 0;
            }
            
            .info-text {
                font-size: 13px;
                color: #48557a;
                text-align: center;
                margin: 10px 0 18px;
                line-height: 1.5;
            }
        </style>
    </head>
    <body>
        <main>
            <div class="main-container">
                <form class="form-content" action="includes/forgot_password_handler.inc.php" method="POST" id="forgotForm" novalidate>
                    
                    <!-- Go Back -->
                    <a href="login.php" class="goBack">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="16px">
                            <path fill="#0b1f4d" d="M73.4 297.4C60.9 309.9 60.9 330.2 73.4 342.7L233.4 502.7C245.9 515.2 266.2 515.2 278.7 502.7C291.2 490.2 291.2 469.9 278.7 457.4L173.3 352L544 352C561.7 352 576 337.7 576 320C576 302.3 561.7 288 544 288L173.3 288L278.7 182.6C291.2 170.1 291.2 149.8 278.7 137.3C266.2 124.8 245.9 124.8 233.4 137.3L73.4 297.3z"/>
                        </svg>
                        Back to Login
                    </a>

                    <!-- Header -->
                    <div class="auth-head">
                        <h1>Forgot Password?</h1>
                        <span class="tagline">RESET YOUR PASSWORD</span>
                    </div>
                    
                    <p class="info-text">
                        Enter the email address associated with your account and we'll send you a link to reset your password.
                    </p>
                    
                    <!-- Error/Success Messages -->
                    <?php if (!empty($error)): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <div id="validationErrors" class="error-message" style="display:none;"></div>
                
                    <!-- Email Field -->
                    <div class="input-group" id="emailGroup">
                        <input type="email" name="email" id="email" placeholder=" " required/>
                        <label for="email">Email Address</label>
                    </div>
                    
                    <!-- Submit -->
                    <button type="submit" name="submit" id="submit">Send Reset Link</button>
                    
                    <!-- Signup Link -->
                    <p class="signUp">Don't have an account? <a href="register.php">Sign up</a></p>
                    
                </form>
            </div>
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('forgotForm');
                const validationErrors = document.getElementById('validationErrors');
                
                function displayErrors(errors) {
                    validationErrors.style.display = 'block';
                    validationErrors.innerHTML = errors.join('<br>');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
                
                function clearErrors() {
                    validationErrors.style.display = 'none';
                    validationErrors.innerHTML = '';
                    document.querySelectorAll('.input-group').forEach(group => {
                        group.classList.remove('error');
                    });
                }
                
                form.querySelectorAll('input').forEach(input => {
                    input.addEventListener('input', clearErrors);
                });
                
                form.addEventListener('submit', function(e) {
                    clearErrors();
                    const errors = [];
                    const email = document.getElementById('email').value.trim();
                    
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        errors.push('Please enter a valid email address.');
                        document.getElementById('emailGroup').classList.add('error');
                    }
                    
                    if (errors.length > 0) {
                        e.preventDefault();
                        displayErrors(errors);
                    }
                });
            });
        </script>
    </body>
</html>
