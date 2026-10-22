<?php
require_once "auth.inc.php";
require_role('student');

//Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../recruiter/dashboard.php");
    die();
}

$jobID = trim($_POST['jobID']);
$userID = $_SESSION['userID'];

//Check if the student has already applied to this job
require_once "dbh.inc.php";
$checkExisting = $pdo->prepare("SELECT id FROM applications WHERE studentID = ? AND jobID = ?");
$checkExisting->execute([$userID, $jobID]);
if ($checkExisting->rowCount() > 0) {
    header("Location: ../student/dashboard.php?error=already_applied");
    die();
}


//Handle file upload
$fileLocation = null;
if (isset($_FILES['cvFile']) && $_FILES['cvFile']['error'] === UPLOAD_ERR_OK){
    $file = $_FILES['cvFile'];
    //validate file is a correct format
    $allowedTypes = ['application/pdf'];
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        header("Location: ../student/dashboard.php?error=invalid_file_type");
        die();
    }
    // Validate file size (max 5mb)
    if ($file['size'] > 5 * 1024 * 1024) {
        header("Location:../student/dashboard.php?error=file_too_large");
        die();
    }

        // create uploads directory if it doesnt exist
    $uploadDir = '../uploads/cvs/';
    if(!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    //unique file name
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('cv_', true). '.' . $fileExtension;
    $uploadPath = $uploadDir . $fileName;
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $fileLocation = 'uploads/cvs/' . $fileName;
    } else {
        header("Location: ../student/dashboard.php?error=upload_failed");
        die();
    }
}


try {
    //Insert post into db
    $query = "INSERT INTO applications (studentID, jobID, cvFile)
              VALUES (:studentID, :jobID, :cvFile)";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":studentID", $userID);
    $stmt->bindParam(":jobID", $jobID);
    $stmt->bindParam(":cvFile", $fileLocation);
    

    if ($stmt ->execute()) {
        header("Location: ../student/dashboard.php?success=application_submitted");
    } else {
        header("Location: ../student/dashboard.php?error=database_error");
    }

    die();
    
    
} catch (PDOException $e) {
    error_log("Job creation failed: " . $e->getMessage());
    header("Location: ../student/dashboard.php?error=database_error");
    die();
}
