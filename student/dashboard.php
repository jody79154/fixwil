<?php
require_once "../includes/auth.inc.php";
require_once "../includes/session_timeout.inc.php";
require_role('student');
// get the data
$userID = $_SESSION['userID'];
$username = $_SESSION['fullName'];
// Still to do connect and get pfp
//Fetch available postings
try {
    require_once "../includes/dbh.inc.php";
    $jobsQuery = "SELECT * FROM job_postings WHERE status = 'open'"; 
    $jobsStmt = $pdo->prepare($jobsQuery);
    $jobsStmt->execute();
    $jobs = $jobsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $jobs = [];
    $jobsError = "Error loading jobs.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="#">
</head>
<body>
<a href="profile.php">Go To Student Profile</a>

<?php if (isset($_GET['success']) && $_GET['success'] === 'application_submitted'): ?>
    <p>Application submitted successfully.</p>
<?php elseif (isset($_GET['error'])): ?>
    <?php
    $errorMessages = [
        'already_applied'    => 'You have already applied to this job.',
        'invalid_file_type'  => 'Please upload a PDF file.',
        'file_too_large'     => 'Your file is too large. Maximum size is 5MB.',
        'upload_failed'      => 'File upload failed. Please try again.',
        'database_error'     => 'Something went wrong. Please try again.'
    ];
    $errorCode = $_GET['error'];
    echo '<p>' . htmlspecialchars($errorMessages[$errorCode] ?? 'Something went wrong.') . '</p>';
    ?>
<?php endif; ?>

    <h2>Job Postings</h2>
    <div id="job-cards">
        <?php if (isset($jobsError)): ?>
            <p><?php echo htmlspecialchars($jobsError); ?></p>
        <?php elseif (empty($jobs)): ?>
            <p>No Jobs Yet</p>
        <?php else: ?>
            <?php foreach ($jobs as $job): ?>
                <div>
                    <h3><?php echo htmlspecialchars($job['jobTitle']); ?></h3>
                    <p><?php echo htmlspecialchars($job['company']); ?></p>
                    <p><?php echo htmlspecialchars($job['workSetup']); ?></p>
                    <p><?php echo htmlspecialchars($job['deadline']); ?></p>
                    <img src="../assets/<?php echo strtolower(htmlspecialchars($job['company'])); ?>.png" alt="Company Logo">
                    <a href="job_detail.php?id=<?php echo (int) $job['id']; ?>">View Details</a>
                    <?php if ($job['workSetup'] == 'On-Site' || $job['workSetup'] == 'Hybrid'): ?>
                      <p><?php echo htmlspecialchars($job['location']); ?></p>
                    <?php endif; ?> 
                </div>
                <hr>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
</body>
