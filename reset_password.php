<?php
    require_once "includes/session_init.php";
    
    require_once "includes/dbh.inc.php";
    
    // Check for messages
    $error = isset($_SESSION['reset_error']) ? $_SESSION['reset_error'] : '';
    $success = isset($_SESSION['reset_success']) ? $_SESSION['reset_success'] : '';
    
    if (!empty($error)) unset($_SESSION['reset_error']);
    if (!empty($success)) unset($_SESSION['reset_success']);
    
    // Get token from URL
    $token = $_GET['token'] ?? '';
    
    // Validate token
    $tokenValid = false;
    $userEmail = '';
    
    if (!empty($token)) {
        $stmt = $pdo->prepare("
            SELECT pr.id, pr.userID, pr.email, pr.expiresAt, pr.used, u.fullName 
            FROM password_resets pr
            JOIN users u ON u.id = pr.userID
            WHERE pr.token = ? AND pr.used = 0
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reset) {
            // Check if not expired
            if (strtotime($reset['expiresAt']) > time()) {
                $tokenValid = true;
                $userEmail = $reset['email'];
                $userName = $reset['fullName'];
            } else {
                $error = "This reset link has expired. Please request a new one.";
            }
        } else {
            $error = "Invalid or already used reset link.";
        }
    } else {
        $error = "No reset token provided.";
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>WIL Connect | Reset Password</title>
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
                text-align: center;
            }
            
            .info-text {
                font-size: 13px;
                color: #48557a;
                text-align: center;
                margin: 10px 0 18px;
                line-height: 1.5;
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
                
                <?php if ($tokenValid): ?>
                
                <form class="form-content" action="includes/reset_password_handler.inc.php" method="POST" id="resetForm" novalidate>
                    
                    <!-- Header -->
                    <div class="auth-head">
                        <h1>Set New Password</h1>
                        <span class="tagline">RESET YOUR PASSWORD</span>
                    </div>
                    
                    <p class="info-text">
                        Hi <?php echo htmlspecialchars($userName ?? 'there'); ?>,<br>
                        Please enter your new password below.
                    </p>
                    
                    <!-- Error Display -->
                    <div id="errorContainer">
                        <?php if (!empty($error)): ?>
                            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div id="validationErrors" class="error-message" style="display:none;"></div>
                    
                    <!-- Hidden token -->
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>"/>
                
                    <!-- Password Field -->
                    <div class="input-group" id="passwordGroup">
                        <input type="password" name="password" id="password" placeholder=" " required/>
                        <label for="password">New Password</label>
                    </div>
                    
                    <!-- Confirm Password Field -->
                    <div class="input-group" id="confirmGroup">
                        <input type="password" name="confirmPassword" id="confirmPassword" placeholder=" " required/>
                        <label for="confirmPassword">Confirm New Password</label>
                    </div>
                    
                    <!-- Submit -->
                    <button type="submit" name="reset" id="reset">Reset Password</button>
                    
                </form>
                
                <?php else: ?>
                
                <div class="form-content">
                    <div class="auth-head">
                        <h1>Reset Link Invalid</h1>
                        <span class="tagline">SOMETHING WENT WRONG</span>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <p class="signUp" style="margin-top: 20px;">
                        <a href="forgot_password.php">Request a new reset link</a>
                    </p>
                    
                    <p class="signUp">
                        <a href="login.php">Back to Login</a>
                    </p>
                </div>
                
                <?php endif; ?>
                
            </div>
        </main>

        <?php if ($tokenValid): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('resetForm');
                const validationErrors = document.getElementById('validationErrors');
                const phpErrors = document.querySelector('.error-message');
                
                function displayErrors(errors) {
                    if (phpErrors) phpErrors.style.display = 'none';
                    validationErrors.style.display = 'block';
                    validationErrors.innerHTML = errors.join('<br>');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
                
                function clearErrors() {
                    validationErrors.style.display = 'none';
                    validationErrors.innerHTML = '';
                    if (phpErrors) phpErrors.style.display = 'block';
                    document.querySelectorAll('.input-group').forEach(g => g.classList.remove('error'));
                }
                
                form.querySelectorAll('input').forEach(input => {
                    input.addEventListener('input', clearErrors);
                });
                
                form.addEventListener('submit', function(e) {
                    clearErrors();
                    const errors = [];
                    
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirmPassword').value;
                    
                    if (password.length < 8) {
                        errors.push('Password must be at least 8 characters long.');
                        document.getElementById('passwordGroup').classList.add('error');
                    }
                    if (!/[^a-zA-Z0-9]/.test(password)) {
                        errors.push('Password must contain at least one special character.');
                        document.getElementById('passwordGroup').classList.add('error');
                    }
                    if (password !== confirmPassword) {
                        errors.push('Passwords do not match.');
                        document.getElementById('confirmGroup').classList.add('error');
                    }
                    
                    if (errors.length > 0) {
                        e.preventDefault();
                        displayErrors(errors);
                    }
                });
            });
        </script>
        <?php endif; ?>
    </body>
</html>
