<?php
require_once "../includes/auth.inc.php";
require_once "../includes/session_timeout.inc.php";
require_role('recruiter');

//check if job exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: my_jobs.php?error=No job Specified");
    exit();
}

$jobID = (int)$_GET['id'];
$userID = $_SESSION['userID'];

try {
    require_once "../includes/dbh.inc.php";

    //start transaction
    $pdo->beginTransaction();

    //make sure that the job actually belongs to the recruiter
    $verifyQuery = "SELECT id, jobTitle, status FROM job_postings WHERE id = :jobID AND recruiterID = :recruiterID";
    $verifyStmt = $pdo->prepare($verifyQuery);
    $verifyStmt->bindParam(":jobID", $jobID);
    $verifyStmt->bindParam(":recruiterID", $userID);
    $verifyStmt->execute();
    $job = $verifyStmt->fetch(PDO::FETCH_ASSOC);

    //if job isnt found
    if (!$job) {
        $pdo->rollBack();
        header("Location: my_jobs.php?error=Job not found or you don't have permission");
        exit();
    }

    //status is already closed
    if ($job['status'] === 'closed') {
        $pdo->rollBack();
        header("Location: my_jobs.php?error=This job is already closed");
        exit();
    }
    
    // Close the job
    $updateQuery = "UPDATE job_postings SET status = 'closed' WHERE id = :jobID AND recruiterID = :recruiterID";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->bindParam(":jobID", $jobID);
    $updateStmt->bindParam(":recruiterID", $userID);
    $updateStmt->execute();
    
    // Commit transaction
    $pdo->commit();
    
    // Redirect with success message
    header("Location: my_jobs.php?success=Job '" . urlencode($job['jobTitle']) . "' has been closed successfully");
    exit();
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error closing job: " . $e->getMessage());
    header("Location: my_jobs.php?error=Error closing job. Please try again.");
    exit();
    
}



?>
