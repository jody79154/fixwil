<?php

require_once "includes/session_init.php";
require_once "includes/dbh.inc.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $email = $_SESSION['email'];
    try {
        $checkCode = "SELECT * FROM users WHERE email = :email
                    AND verificationCode = :code
                    AND codeExpiresAt > NOW()";
        $stmt = $pdo->prepare($checkCode);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":code", $code);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        //verify the user and clear the verification code
        if ($user) {
            $query = "UPDATE users SET isVerified = 1, verificationCode = NULL WHERE email = :email";
            $updateStmt = $pdo->prepare($query);
            $updateStmt->bindParam(":email", $email);
            $updateStmt->execute();
            //redirect to the login
            header("Location: login.php");
            exit();
        } else {
            //code doesnt match
            $error = "Invalid verification code.";
        }
    } catch (PDOException $e) {
        $error = "Something went wrong. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify</title>
    <link rel="stylesheet" href="#">
</head>
<body>
<?php if (isset($error)): ?>
    <p><?php echo $error; ?></p>
<?php endif; ?>
<form method="POST" action="verify.php">
<label for="code">Verify</label>
<input type="number" name="code" id="code">
<button>submit</button>
</form>

<form method="POST" action="includes/resend_code.inc.php">
    <button type="submit">Resend code</button>
</form>

<?php if (isset($_GET['resent'])): ?>
    <p>A new code has been sent to your email.</p>
<?php endif; ?>
</body>
</html>
