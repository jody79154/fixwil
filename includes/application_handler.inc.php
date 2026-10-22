<?php
require_once "auth.inc.php";
require_role('recruiter');

//Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../recruiter/dashboard.php");
    die();
}

// Get The Form Data
$jobTitle = trim($_POST['jobTitle']);
$jobType = trim($_POST['jobType']);
$workSetup = trim($_POST['workSetup']);
$jobDescription = trim($_POST['jobDescription']);
$requirements = trim($_POST['requirements']);
$deadline = trim($_POST['deadline']);
$location = trim($_POST['location']);
$company = trim($_POST['company']);


$recruiterID = $_SESSION['userID'];

//Validation
if(empty($jobTitle)){
    header("Location:../recruiter/dashboard.php?error=empty_content");
    die();
}

if (empty($jobType)) {
    header("Location: ../recruiter/dashboard.php?error=empty_content");
    die();
}

if (empty($workSetup)) {
    header("Location: ../recruiter/dashboard.php?error=empty_content");
    die();
}

if (empty($jobDescription)) {
    header("Location: ../recruiter/dashboard.php?error=empty_content");
    die();
}
if (empty($requirements)) {
    header("Location: ../recruiter/dashboard.php?error=empty_content");
    die();
}
if (empty($deadline)) {
    header("Location: ../recruiter/dashboard.php?error=empty_content");
    die();
}

if (empty($company)) {
    header("Location: ../recruiter/dashboard.php?error=empty_content");
    die();
}

try {
    //connect to db
    require_once "dbh.inc.php";

    //Insert post into db
    $query = "INSERT INTO job_postings
    (recruiterID,
    company,
    jobTitle,
    jobType,
    workSetup,
    jobDescription,
    requirements,
    deadline)
    VALUES
    (:recruiterID,
    :company,
    :jobTitle,
    :jobType,
    :workSetup,
    :jobDescription,
    :requirements,
    :deadline)
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":recruiterID", $recruiterID);
    $stmt->bindParam(":company", $company);
    $stmt->bindParam(":jobTitle", $jobTitle);
    $stmt->bindParam(":jobType", $jobType);
    $stmt->bindParam(":workSetup", $workSetup);
    $stmt->bindParam(":jobDescription", $jobDescription);
    $stmt->bindParam(":requirements", $requirements);
    $stmt->bindParam(":deadline", $deadline);

    if ($stmt ->execute()) {
        header("Location: ../recruiter/dashboard.php?success=job_created");
    } else {
        header("Location: ../recruiter/dashboard.php?error=database_error");
    }

    die();
    
    
} catch (PDOException $e) {
    error_log("Job creation failed: " . $e->getMessage());
    header("Location: ../recruiter/dashboard.php?error=database_error");
    die();
}




?>
