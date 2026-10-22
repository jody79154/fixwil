<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/session_init.php';

require_once "dbh.inc.php";
require "../PHPMailer/Exception.php";
require "../PHPMailer/PHPMailer.php";
require "../PHPMailer/SMTP.php";
require_once "config.php";

// Function to redirect with multiple errors
function redirectWithErrors($errors, $redirectPage) {
    $_SESSION['registration_errors'] = $errors; // Store array of errors
    header("Location: ../" . $redirectPage);
    die();
}

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Determine which form was submitted based on the presence of specific fields
    $isRecruiter = isset($_POST['number']); // Recruiter form has 'number' field
    $isStudent = isset($_POST['surname']); // Student form has 'surname' field
    
    // Common fields
    $name = sanitizeInput($_POST["name"] ?? '');
    $email = sanitizeInput($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $confirmPassword = $_POST["confirmPassword"] ?? '';
    $terms = isset($_POST['terms']);
    
    // Array to collect all errors
    $errors = [];
    
    // Validation - Common
    if (empty($name)) {
        $errors[] = $isRecruiter ? "Please enter your company name." : "Please enter your name.";
    }
    
    if (empty($email)) {
        $errors[] = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if (empty($password)) {
        $errors[] = "Please enter a password.";
    } else {
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character.";
        }
    }
    
    if (empty($confirmPassword)) {
        $errors[] = "Please confirm your password.";
    } elseif ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }
    
    // Check terms
    if (!$terms) {
        $errors[] = "You must accept the Terms & Conditions.";
    }
    
    // Role-specific validation
    if ($isStudent) {
        // Student registration
        $surname = sanitizeInput($_POST["surname"] ?? '');
        
        if (empty($surname)) {
            $errors[] = "Please enter your surname.";
        }
        
        // Student must use school email
        if (!empty($email) && !str_ends_with($email, "@my.richfield.ac.za")) {
            $errors[] = "Students must register using their school email (@my.richfield.ac.za).";
        }
        
        // If no errors, proceed with student registration
        if (empty($errors)) {
            $fullName = $name . ' ' . $surname;
            $role = 'student';
            $company = null;
            $location = null;
            $contactNumber = null;
        }
        
    } elseif ($isRecruiter) {
        // Recruiter registration
        $contactNumber = sanitizeInput($_POST["number"] ?? '');
        $location = sanitizeInput($_POST["location"] ?? '');
        
        if (empty($contactNumber)) {
            $errors[] = "Please enter a contact number.";
        } elseif (!preg_match('/^[0-9+\-\s()]{10,}$/', $contactNumber)) {
            $errors[] = "Please enter a valid contact number (at least 10 digits).";
        }
        
        if (empty($location)) {
            $errors[] = "Please enter your company location.";
        }
        
        // If no errors, proceed with recruiter registration
        if (empty($errors)) {
            $fullName = $name; // Company name for recruiters
            $role = 'recruiter';
            $company = $name; // Company name
            $surname = null; // Not applicable for recruiters
        }
        
    } else {
        // Fallback - should never happen
        $errors[] = "Invalid registration type.";
    }
    
    // If there are validation errors, redirect back with errors
    if (!empty($errors)) {
        redirectWithErrors($errors, $isRecruiter ? "recruiter_register.php" : "student_register.php");
    }
    
    // Check if email already exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->rowCount() > 0) {
        $errors[] = "An account with this email already exists.";
        redirectWithErrors($errors, $isRecruiter ? "recruiter_register.php" : "student_register.php");
    }
    
    // Generate verification code
    $verificationCode = random_int(100000, 999999);
    $codeExpiresAt = date("Y-m-d H:i:s", strtotime("+10 minutes"));
    
    // Hash Password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $sql = "INSERT INTO users (fullName, email, password, role, company, location, verificationCode, isVerified, codeExpiresAt, profilePic, bio)
            VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, NULL, NULL)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $fullName,
        $email,
        $hashedPassword,
        $role,
        $company,
        $location,
        $verificationCode,
        $codeExpiresAt
    ]);
    
    $userId = $pdo->lastInsertId();
    
    // Store email in session for verification
    $_SESSION['email'] = $email;
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = $role;
    
    // Send verification email
    $mail = new PHPMailer(true);
    
    try {
		
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_APP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom(MAIL_USERNAME, 'WIL-Connect');
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify your WIL-Connect account';
        $mail->Body = "
            <h2>Welcome to WIL-Connect!</h2>
            <p>Thank you for registering. Your verification code is:</p>
            <h1 style='color: #116ad8; font-size: 36px; text-align: center; padding: 20px; background: #f0f4ff; border-radius: 8px;'>
                $verificationCode
            </h1>
            <p>This code will expire in 10 minutes.</p>
            <p>Please enter this code to verify your account.</p>
            <br>
            <p>Best regards,<br>WIL-Connect Team</p>
        ";
        
        $mail->send();
        
        // Redirect to verification page
        header("Location: ../verify.php");
        die();
        
		
    } catch (Exception $e) {
        // If email fails, delete the user and redirect with error
        $deleteUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $deleteUser->execute([$userId]);
        
        $errors[] = "Could not send verification email. Please try again.";
        redirectWithErrors($errors, $isRecruiter ? "recruiter_register.php" : "student_register.php");
    }
}

// If not POST, redirect to register page
header("Location: ../register.php");
die();
?>
