<?php
require_once __DIR__ . '/session_init.php';

require_once "dbh.inc.php";

function redirectWithError($errorMsg, $token = '') {
    $_SESSION['reset_error'] = $errorMsg;
    $url = "../reset_password.php";
    if (!empty($token)) {
        $url .= "?token=" . urlencode($token);
    }
    header("Location: " . $url);
    die();
}

function redirectWithSuccess($successMsg) {
    $_SESSION['login_success'] = $successMsg;
    header("Location: ../login.php");
    die();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    $errors = [];
    
    // Validate token
    if (empty($token)) {
        redirectWithError("No reset token provided.");
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Please enter a new password.";
    } else {
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character.";
        }
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }
    
    if (!empty($errors)) {
        $_SESSION['reset_error'] = implode('<br>', $errors);
        header("Location: ../reset_password.php?token=" . urlencode($token));
        die();
    }
    
    // Verify token is still valid
    $stmt = $pdo->prepare("
        SELECT id, userID, expiresAt, used 
        FROM password_resets 
        WHERE token = ? AND used = 0
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reset) {
        redirectWithError("Invalid or already used reset link.");
    }
    
    if (strtotime($reset['expiresAt']) < time()) {
        redirectWithError("This reset link has expired. Please request a new one.");
    }
    
    // Hash new password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Update user's password
    $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update->execute([$hashedPassword, $reset['userID']]);
    
    // Sign the account out everywhere that used "remember me"
    require_once __DIR__ . '/remember_me.inc.php';
    remember_me_revoke_all($pdo, (int) $reset['userID']);

    // Mark token as used
    $markUsed = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
    $markUsed->execute([$reset['id']]);
    
    // Clean up any other expired/used tokens for this user
    $cleanup = $pdo->prepare("DELETE FROM password_resets WHERE userID = ? AND (used = 1 OR expiresAt < NOW())");
    $cleanup->execute([$reset['userID']]);
    
    // Redirect to login with success message
    redirectWithSuccess("Your password has been reset successfully. Please log in with your new password.");
}

header("Location: ../forgot_password.php");
die();
?>
