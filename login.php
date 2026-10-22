<?php
    require_once "includes/session_init.php";

    // Show success/error messages
    $error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
    $success = isset($_SESSION['login_success']) ? $_SESSION['login_success'] : '';
    
    // Clear messages after retrieving
    if (!empty($error)) unset($_SESSION['login_error']);
    if (!empty($success)) unset($_SESSION['login_success']);
    
    // If user is already logged in, redirect them based on role
    if (isset($_SESSION['userID'])) {
        switch ($_SESSION['role']) {
            case "student":
                header("Location: student/dashboard.php");
                break;
            case "recruiter":
                header("Location: recruiter/dashboard.php");
                break;
            case "lecturer":
                header("Location: lecturer/dashboard.php");
                break;
            default:
                header("Location: home.php");
                break;
        }
        die();
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>WIL Connect | Login</title>
        <link rel="stylesheet" href="css/auth_pages.css">
        <style>
            /* Login specific styles */
            .main-container {
                width: 450px;
                max-width: 95%;
            }
            
            /*.head {
                display: flex;
                align-items: center;
                gap: 15px;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 1px solid rgba(17, 106, 216, 0.1);
            }
            
            .head .logo {
                width: 60px;
                height: 60px;
                background: linear-gradient(135deg, #116ad8, #0b1f4d);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-weight: bold;
                font-size: 20px;
                flex-shrink: 0;
            }
            
            .system-cont {
                flex: 1;
            }
            
            .system_name {
                margin: 0;
                line-height: 1.2;
                font-size: 22px;
                color: #0b1f4d;
            }
            
            .slogan {
                font-size: 12px;
                font-weight: bold;
                color: #2E6BE6;
                margin: 0;
                line-height: 1.2;
                letter-spacing: 1px;
            }*/
			
			.head {
				text-align: left; 
				/*margin-bottom: 28px;*/
				margin-bottom: 48px;
				display: flex;
			}

			.slogan {
				font-size: 13px;
				font-weight: bold;
				color: #2E6BE6;
				margin: 0;
				line-height: 1.1;
			}

			.system_name {
		        margin: 0;
                line-height: 1.1;
			}

			.system-cont {
				margin-top: 27px;
			}
            
            .remember {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin: 15px 0 10px;
                width: 98%;
            }
            
            .remember .checkbox-group {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .remember .checkbox-group input[type="checkbox"] {
                width: 16px;
                height: 16px;
                cursor: pointer;
            }
            
            .remember .checkbox-group label {
                color: #48557a;
                font-size: 13px;
                cursor: pointer;
            }
            
            .forgot-pass {
                color: #116ad8;
                text-decoration: none;
                font-size: 13px;
                font-weight: 500;
                transition: color 0.2s ease;
            }
            
            .forgot-pass:hover {
                color: #f5a623;
                text-decoration: underline;
            }
            
            .error-message {
                border: 1px solid #dc3545;
                border-radius: 6px;
                padding: 10px 14px;
                color: #dc3545;
                background-color: #ffe2ea;
                font-size: 13px;
                margin: 10px 0;
                display: block !important;
            }
            
            .error-message.hidden {
                display: none !important;
            }
            
            .success-message {
                border: 1px solid #28a745;
                border-radius: 6px;
                padding: 10px 14px;
                color: #155724;
                background-color: #d4edda;
                font-size: 13px;
                margin: 10px 0;
                display: block !important;
            }
            
            .success-message.hidden {
                display: none !important;
            }
            
            #validationErrors {
                border: 1px solid #dc3545;
                border-radius: 6px;
                padding: 10px 14px;
                color: #dc3545;
                background-color: #ffe2ea;
                font-size: 13px;
                margin: 10px 0;
            }
            
            #validationErrors.hidden {
                display: none !important;
            }
            
            .input-group.error input {
                border-color: #dc3545;
                background-color: #fff8f8;
            }
            
            .input-group.error label {
                color: #dc3545;
            }
            
            @media (max-width: 500px) {
                .head {
                    flex-direction: column;
                    text-align: center;
                }
                
                .remember {
                    flex-direction: column;
                    gap: 10px;
                    align-items: flex-start;
                }
            }
        </style>
    </head>
    <body>
        <main>
            <div class="main-container">
                <form class="form-content" action="includes/loginhandler.inc.php" method="POST" id="loginForm" novalidate>
                    
                    <!-- Header with Logo -->
                    <div class="head">
                        <!--<div class="logo">W</div>
                        <div class="system-cont">
                            <h2 class="system_name">WIL-Connect</h2>
                            <p class="slogan">UNLOCKING OPPORTUNITIES | BUILDING FUTURES</p>
                        </div>-->
						<img src="uploads/wil-connect.png" width="100"/>
						<div class="system-cont">
						    <h2 class="system_name">WIL-Connect</h2>
						    <p class="slogan">UNLOCKING OPPORTUNITIES</p>
						</div>
                    </div>
                    
                    <!-- Error / Success Display -->
                    <div id="errorContainer">
                        <?php if (!empty($error)): ?>
                            <div class="error-message" id="phpErrors">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="success-message" id="phpSuccess">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div id="validationErrors" class="hidden"></div>
                
                    <!-- Email Field -->
                    <div class="input-group" id="emailGroup">
                        <input type="email" name="email" id="email" placeholder=" " required/>
                        <label for="email">Email Address</label>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="input-group" id="passwordGroup">
                        <input type="password" name="password" id="password" placeholder=" " required/>
                        <label for="password">Password</label>
                    </div>
                    
                    <!-- Remember Me & Forgot Password -->
                    <div class="remember">
                        <div class="checkbox-group">
                            <input type="checkbox" name="rememberMe" id="rememberMe"/>
                            <label for="rememberMe">Remember Me</label>
                        </div>
                        <a href="forgot_password.php" class="forgot-pass">Forgot Password?</a>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" name="login" id="login">Log In</button>
                    
                    <!-- Sign Up Link -->
                    <p class="signUp">Don't have an account? <a href="register.php">Sign Up</a></p>
                    
                </form>
            </div>
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('loginForm');
                const validationErrors = document.getElementById('validationErrors');
                const phpErrors = document.getElementById('phpErrors');
                const phpSuccess = document.getElementById('phpSuccess');
                
                // Function to display errors
                function displayErrors(errors) {
                    // Hide PHP errors/success if they exist
                    if (phpErrors) phpErrors.style.display = 'none';
                    if (phpSuccess) phpSuccess.style.display = 'none';
                    
                    // Show validation errors
                    validationErrors.classList.remove('hidden');
                    validationErrors.style.display = 'block';
                    validationErrors.innerHTML = errors.join('<br>');
                    
                    // Scroll to top
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
                
                // Function to clear errors
                function clearErrors() {
                    validationErrors.classList.add('hidden');
                    validationErrors.style.display = 'none';
                    validationErrors.innerHTML = '';
                    
                    // Show PHP errors/success again if they exist
                    if (phpErrors) phpErrors.style.display = 'block';
                    if (phpSuccess) phpSuccess.style.display = 'block';
                    
                    // Remove error styling from inputs
                    document.querySelectorAll('.input-group').forEach(group => {
                        group.classList.remove('error');
                    });
                }
                
                // Clear errors when user types
                form.querySelectorAll('input').forEach(input => {
                    input.addEventListener('input', function() {
                        clearErrors();
                        this.closest('.input-group').classList.remove('error');
                    });
                });
                
                form.addEventListener('submit', function(e) {
                    // Clear previous errors
                    clearErrors();
                    
                    const errors = [];
                    
                    // Get form values
                    const email = document.getElementById('email').value.trim();
                    const password = document.getElementById('password').value;
                    
                    // Validation: Email
                    if (email === '') {
                        errors.push('Please enter your email address.');
                        document.getElementById('emailGroup').classList.add('error');
                    } else {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(email)) {
                            errors.push('Please enter a valid email address.');
                            document.getElementById('emailGroup').classList.add('error');
                        }
                    }
                    
                    // Validation: Password
                    if (password === '') {
                        errors.push('Please enter your password.');
                        document.getElementById('passwordGroup').classList.add('error');
                    }
                    
                    // If there are errors, prevent form submission
                    if (errors.length > 0) {
                        e.preventDefault();
                        displayErrors(errors);
                    }
                });
            });
        </script>
    </body>
</html>
