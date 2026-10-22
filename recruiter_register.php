<?php
    require_once "includes/session_init.php";
    
    // Check for error messages from registration handler
    $errors = isset($_SESSION['registration_errors']) ? $_SESSION['registration_errors'] : [];
    $singleError = isset($_SESSION['registration_error']) ? $_SESSION['registration_error'] : '';
    
    // Clear errors after retrieving
    if (!empty($errors) || !empty($singleError)) {
        unset($_SESSION['registration_errors']);
        unset($_SESSION['registration_error']);
    }
    
    // If user is already logged in, redirect them
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard.php");
        die();
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>WIL Connect | Recruiter Register</title>
        <link rel="stylesheet" href="css/auth_pages.css">
        <style>
            /* Additional styles for error messages */
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
            
            .error-message br {
                margin-bottom: 4px;
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
        </style>
    </head>
    <body>
        <main>
            <div class="main-container">
                <form class="form-content" action="includes/registerhandler.inc.php" method="POST" id="registerForm" novalidate>
                    
                    <!-- Go Back -->
                    <a href="register.php" class="goBack">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="16px">
                            <path fill="#0b1f4d" d="M73.4 297.4C60.9 309.9 60.9 330.2 73.4 342.7L233.4 502.7C245.9 515.2 266.2 515.2 278.7 502.7C291.2 490.2 291.2 469.9 278.7 457.4L173.3 352L544 352C561.7 352 576 337.7 576 320C576 302.3 561.7 288 544 288L173.3 288L278.7 182.6C291.2 170.1 291.2 149.8 278.7 137.3C266.2 124.8 245.9 124.8 233.4 137.3L73.4 297.3z"/>
                        </svg>
                        Go Back
                    </a>

                    <!-- Header -->
                    <div class="auth-head">
                        <h1>Join WIL-Connect</h1>
                        <span class="tagline">CREATE AN ACCOUNT AS A RECRUITER</span>
                    </div>
                    
                    <!-- Error Display -->
                    <div id="errorContainer">
                        <?php if (!empty($errors) && is_array($errors)): ?>
                            <div class="error-message" id="phpErrors">
                                <?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?>
                            </div>
                        <?php elseif (!empty($singleError)): ?>
                            <div class="error-message" id="phpErrors">
                                <?php echo htmlspecialchars($singleError); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div id="validationErrors" class="hidden"></div>
                
                    <!-- Form Fields -->
                    <div class="input-group" id="nameGroup">
                        <input type="text" name="name" id="name" placeholder=" " required/>
                        <label for="name">Company Name</label>
                    </div>
                    
                    <div class="input-group" id="emailGroup">
                        <input type="email" name="email" id="email" placeholder=" " required/>
                        <label for="email">Company Email</label>
                    </div>
                    
                    <div class="input-group" id="passwordGroup">
                        <input type="password" name="password" id="password" placeholder=" " required/>
                        <label for="password">Password</label>
                    </div>
                    
                    <div class="input-group" id="confirmGroup">
                        <input type="password" name="confirmPassword" id="confirmPassword" placeholder=" " required/>
                        <label for="confirmPassword">Confirm Password</label>
                    </div>
                    
                    <div class="input-group" id="numberGroup">
                        <input type="text" name="number" id="number" placeholder=" " required/>
                        <label for="number">Contact Number</label>
                    </div>
                    
                    <div class="input-group" id="locationGroup">
                        <input type="text" name="location" id="location" placeholder=" " required/>
                        <label for="location">Company Location</label>
                    </div>
                    
                    <!-- Terms -->
                    <div class="check">
                        <input type="checkbox" name="terms" id="terms" required/>
                        <label for="terms" class="terMs">I Accept Terms & Conditions</label>
                    </div>
                    
                    <!-- Submit -->
                    <button type="submit" name="register" id="register">Create Account</button>
                    
                    <!-- Login Link -->
                    <p class="signUp">Already have an account? <a href="login.php">Log in</a></p>
                    
                </form>
            </div>
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('registerForm');
                const validationErrors = document.getElementById('validationErrors');
                const phpErrors = document.getElementById('phpErrors');
                
                // Function to display multiple errors
                function displayErrors(errors) {
                    // Hide PHP errors if they exist
                    if (phpErrors) {
                        phpErrors.style.display = 'none';
                    }
                    
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
                    
                    // Show PHP errors again if they exist
                    if (phpErrors) {
                        phpErrors.style.display = 'block';
                    }
                    
                    // Remove error styling from inputs
                    document.querySelectorAll('.input-group').forEach(group => {
                        group.classList.remove('error');
                    });
                }
                
                // Clear errors when user types
                form.querySelectorAll('input').forEach(input => {
                    input.addEventListener('input', function() {
                        clearErrors();
                        // Remove error class from parent
                        this.closest('.input-group').classList.remove('error');
                    });
                });
                
                form.addEventListener('submit', function(e) {
                    // Clear previous errors
                    clearErrors();
                    
                    const errors = [];
                    
                    // Get form values
                    const companyName = document.getElementById('name').value.trim();
                    const email = document.getElementById('email').value.trim();
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirmPassword').value;
                    const contactNumber = document.getElementById('number').value.trim();
                    const location = document.getElementById('location').value.trim();
                    const terms = document.getElementById('terms').checked;
                    
                    // Validation: Company Name
                    if (companyName.length < 2) {
                        errors.push('Please enter a valid company name (at least 2 characters).');
                        document.getElementById('nameGroup').classList.add('error');
                    }
                    
                    // Validation: Email
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        errors.push('Please enter a valid email address.');
                        document.getElementById('emailGroup').classList.add('error');
                    }
                    
                    // Validation: Password
                    if (password.length < 8) {
                        errors.push('Password must be at least 8 characters long.');
                        document.getElementById('passwordGroup').classList.add('error');
                    }
                    if (!/[^a-zA-Z0-9]/.test(password)) {
                        errors.push('Password must contain at least one special character.');
                        document.getElementById('passwordGroup').classList.add('error');
                    }
                    
                    // Validation: Confirm Password
                    if (password !== confirmPassword) {
                        errors.push('Passwords do not match.');
                        document.getElementById('confirmGroup').classList.add('error');
                    }
                    
                    // Validation: Contact Number
                    if (!/^[0-9+\-\s()]{10,}$/.test(contactNumber)) {
                        errors.push('Please enter a valid contact number (at least 10 digits).');
                        document.getElementById('numberGroup').classList.add('error');
                    }
                    
                    // Validation: Location
                    if (location.length < 2) {
                        errors.push('Please enter a valid company location.');
                        document.getElementById('locationGroup').classList.add('error');
                    }
                    
                    // Validation: Terms
                    if (!terms) {
                        errors.push('You must accept the Terms & Conditions.');
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
