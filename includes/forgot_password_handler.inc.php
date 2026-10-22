<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/session_init.php';

require_once "dbh.inc.php";
require "../PHPMailer/Exception.php";
require "../PHPMailer/PHPMailer.php";
require "../PHPMailer/SMTP.php";
require_once "config.php";

// Function to redirect with error
function redirectWithError($errorMsg) {
    $_SESSION['forgot_error'] = $errorMsg;
    header("Location: ../forgot_password.php");
    die();
}

// Function to redirect with success
function redirectWithSuccess($successMsg) {
    $_SESSION['forgot_success'] = $successMsg;
    header("Location: ../forgot_password.php");
    die();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $email = trim($_POST['email'] ?? '');
    
    // Validate email
    if (empty($email)) {
        redirectWithError("Please enter your email address.");
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithError("Please enter a valid email address.");
    }
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, fullName FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // SECURITY: Always show success message (don't reveal if email exists)
    if (!$user) {
        redirectWithSuccess("If an account with that email exists, a reset link has been sent. Please check your inbox.");
    }
    
    // Delete any existing unused tokens for this user
    $delete = $pdo->prepare("DELETE FROM password_resets WHERE userID = ? AND used = 0");
    $delete->execute([$user['id']]);
    
    // Generate secure token
    $token = bin2hex(random_bytes(32)); // 64 char token
    $expiresAt = date("Y-m-d H:i:s", strtotime("+30 minutes"));
    
    // Store token in database
    $insert = $pdo->prepare("INSERT INTO password_resets (userID, email, token, expiresAt) VALUES (?, ?, ?, ?)");
    $insert->execute([$user['id'], $email, $token, $expiresAt]);
    
    // Build reset link
    // Update this to match your actual domain/path
    $resetLink = "http://localhost/wil_connect/reset_password.php?token=" . $token;
    
    // Send email
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_APP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom(MAIL_USERNAME, 'WIL-Connect');
        $mail->addAddress($email);
        
        $mail->isHTML(true);
        $mail->Subject = 'Reset your WIL-Connect password';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #0b1f4d;'>Password Reset Request</h2>
                <p style='color: #48557a;'>Hi " . htmlspecialchars($user['fullName']) . ",</p>
                <p style='color: #48557a;'>
                    We received a request to reset your WIL-Connect password. 
                    Click the button below to create a new password.
                </p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$resetLink' 
                       style='background: #116ad8; color: #ffffff; padding: 14px 30px; 
                              text-decoration: none; border-radius: 6px; font-weight: bold;
                              display: inline-block;'>
                        Reset Password
                    </a>
                </div>
                <p style='color: #48557a; font-size: 13px;'>
                    Or copy and paste this link into your browser:
                </p>
                <p style='color: #116ad8; font-size: 12px; word-break: break-all;'>
                    $resetLink
                </p>
                <p style='color: #dc3545; font-size: 13px;'>
                    <strong>This link will expire in 30 minutes.</strong>
                </p>
                <p style='color: #48557a; font-size: 13px;'>
                    If you didn't request a password reset, you can safely ignore this email.
                </p>
                <br>
                <p style='color: #48557a;'>Best regards,<br>WIL-Connect Team</p>
            </div>
        ";
        
        // Plain text alternative
        $mail->AltBody = "Reset your WIL-Connect password by visiting: $resetLink\n\nThis link expires in 30 minutes.";
        
        $mail->send();
        
        redirectWithSuccess("If an account with that email exists, a reset link has been sent. Please check your inbox.");
        
    } catch (Exception $e) {
        // Delete the token since email failed
        $cleanup = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
        $cleanup->execute([$token]);
        
        redirectWithError("Could not send reset email. Please try again later.");
    }
}

// If not POST, redirect
header("Location: ../forgot_password.php");
die();
?>
