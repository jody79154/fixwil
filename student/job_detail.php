<?php
require_once "../includes/auth.inc.php";
require_once "../includes/session_timeout.inc.php";
require_role('student');

$jobID = $_GET['id'];

try {
    require_once "../includes/dbh.inc.php";
    $detailsQuery = "SELECT job_postings.*, users.fullName as recruiterName FROM job_postings 
                    JOIN users ON job_postings.recruiterID = users.id 
                    WHERE job_postings.id = :id";
                
    $stmt = $pdo->prepare($detailsQuery);
    $stmt->bindParam(":id", $jobID);
    $stmt->execute();
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    if($stmt->rowCount() == 0){
        header("Location:../student/dashboard.php");
        die();
    }
}catch (PDOException $e)
{
    die("Query Failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Job Detail</title>
<link rel="stylesheet" href="#">
</head>

<body>
    <div id="job-detail">
    <!-- logo and basic info -->
    <div id="job-header">
        <img src="../assets/<?php echo strtolower($job['company']); ?>.png" alt="Company Logo">
        <h2><?php echo $job['jobTitle']; ?></h2>
        <p><?php echo $job['company']; ?></p>
        <p><?php echo $job['jobType']; ?></p>
        <p><?php echo $job['workSetup']; ?></p>
        <p><?php echo $job['deadline']; ?></p>
        <p><?php echo $job['recruiterName']; ?></p>
        <?php if ($job['workSetup'] == 'On-Site' || $job['workSetup'] == 'Hybrid'): ?>
            <p><?php echo $job['location']; ?></p>
        <?php endif; ?> 
    </div>

    <!-- full details below -->
    <div id="job-body">
        <p><?php echo $job['jobDescription']; ?></p>
        <p><?php echo $job['requirements']; ?></p>
    </div>

    <!-- apply at the bottom -->
    
    <div id="job-apply">
        <form id="apply" action="../includes/apply_handler.inc.php" method="post" enctype="multipart/form-data">
        <input type="file" name="cvFile" id="cvFile" accept=".pdf"> <br> <br>
        <input type="hidden" name="jobID" value="<?php echo $job['id']; ?>"> <br> <br>
        <input type="submit" value="Apply"> 
        </form>
    </div>
</div>
<?php require __DIR__ . '/chat_widget.php'; ?>
</body>


