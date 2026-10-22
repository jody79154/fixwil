<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/session_init.php';
require_once "dbh.inc.php";
require "../PHPMailer/Exception.php";
require "../PHPMailer/PHPMailer.php";
require "../PHPMailer/SMTP.php";
require_once "config.php";

$email = $_SESSION['email'];

if (empty($email)) {
    die("No email found in session. Please register again.");
}

//Generate a new code and expiry
$verificationCode = random_int(100000, 999999);
$codeExpiresAt = date("Y-m-d H:i:s", strtotime("+10 minutes"));

try {
    //Update the user's row with the new code
    $update = "UPDATE users SET verificationCode = ?, codeExpiresAt = ? WHERE email = ?";
    $stmt = $pdo->prepare($update);
    $stmt->execute([$verificationCode, $codeExpiresAt, $email]);

    //Send the new code
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_APP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom(MAIL_USERNAME, 'Campus Recruit');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Your new verification code';
    $mail->Body = "Your new verification code is: $verificationCode";

    $mail->send();

    //Redirect back to verify.php
    header("Location: ../verify.php?resent=1");
    exit();
} catch (Exception $e) {
    die("Could not resend code. Error: {$mail->ErrorInfo}");
} catch (PDOException $e) {
    die("Something went wrong. Please try again.");
}
?>


