<?php
// edit_job.php
require_once "../includes/auth.inc.php";
require_once "../includes/session_timeout.inc.php";
require_role('recruiter');

// Check if job ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: my_jobs.php?error=No job specified");
    exit();
}

$jobID = (int)$_GET['id'];
$userID = $_SESSION['userID'];

try {
    require_once "../includes/dbh.inc.php";
    
    // Fetch the job to edit
    $query = "SELECT * FROM job_postings WHERE id = :jobID AND recruiterID = :recruiterID";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":jobID", $jobID);
    $stmt->bindParam(":recruiterID", $userID);
    $stmt->execute();
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If job doesn't exist or doesn't belong to this recruiter
    if (!$job) {
        header("Location: my_jobs.php?error=Job not found or you don't have permission");
        exit();
    }
    
    // Fetch recruiter company info
    $userQuery = "SELECT company FROM users WHERE id = :userID";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->bindParam(":userID", $userID);
    $userStmt->execute();
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
    $companyName = $userData['company'] ?? '';
    
} catch (PDOException $e) {
    header("Location: my_jobs.php?error=Error loading job details");
    exit();
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $jobTitle = trim($_POST['jobTitle'] ?? '');
    $jobType = $_POST['jobType'] ?? '';
    $workSetup = $_POST['workSetup'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $jobDescription = trim($_POST['jobDescription'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $deadline = $_POST['deadline'] ?? '';
    $status = $_POST['status'] ?? 'open';
    
    // Validation
    if (empty($jobTitle)) {
        $errors[] = "Job title is required";
    }
    if (empty($jobType)) {
        $errors[] = "Job type is required";
    }
    if (empty($workSetup)) {
        $errors[] = "Work setup is required";
    }
    if (empty($jobDescription)) {
        $errors[] = "Job description is required";
    }
    if (empty($requirements)) {
        $errors[] = "Requirements are required";
    }
    if (empty($deadline)) {
        $errors[] = "Deadline is required";
    }
    
    // Validate deadline is in the future
    if (!empty($deadline) && strtotime($deadline) < strtotime(date('Y-m-d'))) {
        $errors[] = "Deadline must be in the future";
    }
    
    // If no errors, update the job
    if (empty($errors)) {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            $updateQuery = "UPDATE job_postings SET 
                jobTitle = :jobTitle,
                jobType = :jobType,
                workSetup = :workSetup,
                location = :location,
                jobDescription = :jobDescription,
                requirements = :requirements,
                deadline = :deadline,
                status = :status
                WHERE id = :jobID AND recruiterID = :recruiterID";
            
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->bindParam(":jobTitle", $jobTitle);
            $updateStmt->bindParam(":jobType", $jobType);
            $updateStmt->bindParam(":workSetup", $workSetup);
            $updateStmt->bindParam(":location", $location);
            $updateStmt->bindParam(":jobDescription", $jobDescription);
            $updateStmt->bindParam(":requirements", $requirements);
            $updateStmt->bindParam(":deadline", $deadline);
            $updateStmt->bindParam(":status", $status);
            $updateStmt->bindParam(":jobID", $jobID);
            $updateStmt->bindParam(":recruiterID", $userID);
            $updateStmt->execute();
            
            $pdo->commit();
            $success = true;
            
            // Refresh job data
            $refreshQuery = "SELECT * FROM job_postings WHERE id = :jobID AND recruiterID = :recruiterID";
            $refreshStmt = $pdo->prepare($refreshQuery);
            $refreshStmt->bindParam(":jobID", $jobID);
            $refreshStmt->bindParam(":recruiterID", $userID);
            $refreshStmt->execute();
            $job = $refreshStmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Error updating job: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job - WIL-Connect</title>
</head>
<body>

    <h1>Edit Job</h1>
    
    <!-- Status Badge -->
    <p>Current Status: <?php echo ucfirst($job['status']); ?></p>

    <!-- Success Message -->
    <?php if ($success): ?>
        <p>Job updated successfully. <a href="my_jobs.php">Return to My Jobs</a></p>
    <?php endif; ?>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
        <div>
            <strong>Please fix the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Edit Form -->
    <form method="POST" action="">
        
        <!-- Company (Read-only) -->
        <div>
            <label>Company</label>
            <input type="text" value="<?php echo htmlspecialchars($companyName); ?>" disabled>
            <small>Company name is set in your profile</small>
        </div>

        <!-- Job Title -->
        <div>
            <label for="jobTitle">Job Title *</label>
            <input type="text" id="jobTitle" name="jobTitle" 
                   value="<?php echo htmlspecialchars($job['jobTitle']); ?>" required>
        </div>

        <!-- Job Type -->
        <div>
            <label for="jobType">Job Type *</label>
            <select id="jobType" name="jobType" required>
                <option value="">Select Job Type</option>
                <option value="Full-Time" <?php echo $job['jobType'] === 'Full-Time' ? 'selected' : ''; ?>>Full-Time</option>
                <option value="Part-Time" <?php echo $job['jobType'] === 'Part-Time' ? 'selected' : ''; ?>>Part-Time</option>
                <option value="Internship" <?php echo $job['jobType'] === 'Internship' ? 'selected' : ''; ?>>Internship</option>
            </select>
        </div>

        <!-- Work Setup -->
        <div>
            <label for="workSetup">Work Setup *</label>
            <select id="workSetup" name="workSetup" required>
                <option value="">Select Work Setup</option>
                <option value="Remote" <?php echo $job['workSetup'] === 'Remote' ? 'selected' : ''; ?>>Remote</option>
                <option value="On-Site" <?php echo $job['workSetup'] === 'On-Site' ? 'selected' : ''; ?>>On-Site</option>
                <option value="Hybrid" <?php echo $job['workSetup'] === 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
            </select>
        </div>

        <!-- Location -->
        <div>
            <label for="location">Location</label>
            <input type="text" id="location" name="location" 
                   value="<?php echo htmlspecialchars($job['location'] ?? ''); ?>" 
                   placeholder="e.g., Durban, KZN">
        </div>

        <!-- Job Description -->
        <div>
            <label for="jobDescription">Job Description *</label>
            <textarea id="jobDescription" name="jobDescription" required><?php echo htmlspecialchars($job['jobDescription']); ?></textarea>
        </div>

        <!-- Requirements -->
        <div>
            <label for="requirements">Requirements *</label>
            <textarea id="requirements" name="requirements" required><?php echo htmlspecialchars($job['requirements']); ?></textarea>
        </div>

        <!-- Deadline -->
        <div>
            <label for="deadline">Application Deadline *</label>
            <input type="date" id="deadline" name="deadline" 
                   value="<?php echo htmlspecialchars($job['deadline']); ?>" required>
        </div>

        <!-- Status -->
        <div>
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="open" <?php echo $job['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                <option value="closed" <?php echo $job['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                <option value="draft" <?php echo $job['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
            </select>
        </div>

        <!-- Form Actions -->
        <div>
            <button type="submit">Update Job</button>
            <a href="my_jobs.php">Cancel</a>
            
            <?php if ($job['status'] !== 'closed'): ?>
                <a href="close_job.php?id=<?php echo $jobID; ?>" 
                   onclick="return confirm('Are you sure you want to close this job? This will stop new applications.')">
                    Close Job
                </a>
            <?php endif; ?>
        </div>
        
    </form>

</body>
</html>
