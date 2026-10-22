<?php
require_once "auth.inc.php";
require_role('recruiter');

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: ../recruiter/dashboard.php");
    die();
}

$applicationID = $_POST['applicationID'];
$status = $_POST['status'];
$jobID = $_POST['jobID'];
$redirect = $_POST['redirect'] ?? '';

// Only allow redirecting back to a known recruiter page, to avoid an open redirect
$allowedRedirect = preg_match('#^(applicants|applications)\.php(\?[^\s]*)?$#', $redirect);
$fallback = "../recruiter/applications.php?jobID=" . $jobID;
$target = $allowedRedirect ? "../recruiter/" . $redirect : $fallback;
$separator = (strpos($target, '?') !== false) ? '&' : '?';

try {
    require_once "dbh.inc.php";

    //UPDATE status
    $updateQuery = "UPDATE applications SET status = :status WHERE id = :applicationID";

    $stmt = $pdo->prepare($updateQuery);
    $stmt->bindParam(":applicationID", $applicationID);
    $stmt->bindParam(":status", $status);

    if ($stmt ->execute()) {
        header("Location: " . $target . $separator . "success=status_updated");
    } else {
        header("Location: " . $target . $separator . "error=database_error");
    }

    die();
} catch (PDOException $e) {
    error_log("status update failed: " . $e->getMessage());
    header("Location: ../recruiter/applications.php?error=database_error");
    die();
}


?>
