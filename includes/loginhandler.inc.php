<?php
// Don't auto-login from an old remember-me cookie while someone is logging in.
define('SKIP_REMEMBER_ME', true);
require_once __DIR__ . '/session_init.php';   // hardened session cookie
require_once __DIR__ . '/dbh.inc.php';

// Function to redirect with error
function redirectWithError($errorMsg) {
    $_SESSION['login_error'] = $errorMsg;
    header("Location: ../login.php");
    die();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $rememberMe = isset($_POST["rememberMe"]);
    
    // Validation
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if (empty($password)) {
        $errors[] = "Please enter your password.";
    }
    
    // If validation errors, redirect back
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        header("Location: ../login.php");
        die();
    }
    
    // Find user by email
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verify user exists and password matches
    if (!$user) {
        redirectWithError("Invalid email or password.");
    }
    
    if (!password_verify($password, $user["password"])) {
        redirectWithError("Invalid email or password.");
    }
    
    // Check if email is verified
    if ($user["isVerified"] != 1) {
        redirectWithError("Please verify your email before logging in. Check your inbox for the verification code.");
    }
    
    // Regenerates the session ID and stores the user in the session
    start_user_session($user);

    // Drop any old remember-me token, then issue a new one if requested
    remember_me_clear($pdo);
    if ($rememberMe) {
        remember_me_issue($pdo, (int) $user["id"]);
    }

    // Redirect based on role
    switch ($user["role"]) {
        case "student":
            header("Location: ../student/dashboard.php");
            break;
        case "recruiter":
            header("Location: ../recruiter/dashboard.php");
            break;
        case "lecturer":
            header("Location: ../lecturer/dashboard.php");
            break;
        default:
            header("Location: ../home.php");
            break;
    }
    exit();
    
} else {
    // If not POST, redirect to login page
    header("Location: ../login.php");
    die();
}
?>
