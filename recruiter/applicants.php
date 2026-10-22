<?php
/*
require_once "../includes/auth.inc.php";
require_once "../includes/session_timeout.inc.php";
require_role('recruiter');
*/
// Get user data
$userID = $_SESSION['userID'];
$username = $_SESSION['fullName'];

// Fetch the user company from database
try {
    require_once "../includes/dbh.inc.php";
    
    // Get recruiter company info
    $userQuery = "SELECT company, email FROM users WHERE id = :userID";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->bindParam(":userID", $userID);
    $userStmt->execute();
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
    $companyName = $userData['company'] ?? 'My Company';
    $userEmail = $userData['email'] ?? '';
    
    // Get jobID from URL (optional, for filtering)
    $jobID = $_GET['jobID'] ?? null;
    
    if ($jobID) {
        // Fetch applications for a specific job
        $applicationsQuery = "SELECT applications.*, users.fullName as studentName, users.email as studentEmail,
                              job_postings.jobTitle, job_postings.company
                              FROM applications 
                              JOIN users ON applications.studentID = users.id 
                              JOIN job_postings ON applications.jobID = job_postings.id
                              WHERE applications.jobID = :jobID
                              ORDER BY applications.appliedAt DESC";
        
        $stmt = $pdo->prepare($applicationsQuery);
        $stmt->bindParam(":jobID", $jobID);
        $stmt->execute();
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get job details for the header
        $jobQuery = "SELECT jobTitle, company FROM job_postings WHERE id = :jobID";
        $jobStmt = $pdo->prepare($jobQuery);
        $jobStmt->bindParam(":jobID", $jobID);
        $jobStmt->execute();
        $jobDetails = $jobStmt->fetch(PDO::FETCH_ASSOC);
        $jobTitle = $jobDetails['jobTitle'] ?? 'Job';
    } else {
        // Fetch ALL applications for jobs posted by this recruiter
        $applicationsQuery = "SELECT applications.*, users.fullName as studentName, users.email as studentEmail,
                              job_postings.jobTitle, job_postings.company
                              FROM applications 
                              JOIN users ON applications.studentID = users.id 
                              JOIN job_postings ON applications.jobID = job_postings.id
                              WHERE job_postings.recruiterID = :userID
                              ORDER BY applications.appliedAt DESC";
        
        $stmt = $pdo->prepare($applicationsQuery);
        $stmt->bindParam(":userID", $userID);
        $stmt->execute();
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $jobTitle = 'All Applications';
    }
    
} catch (PDOException $e) {
    $applications = [];
    $applicationsError = "Error loading applications: " . $e->getMessage();
    $jobTitle = 'Applications';
    $companyName = 'My Company';
    $userEmail = '';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WIL-Connect | Applications</title>
    <link rel="stylesheet" href="../css/sidebar.css"/>
    <style>
        /* Additional styles for job title column */
        .job-title-link {
            color: #0b1f4d;
            text-decoration: none;
            font-weight: 500;
        }
        .job-title-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark"></div>
                WIL-Connect
            </div>

            <p class="nav-label">Main</p>
            <ul class="nav-links">
                <li><a href="dashboard.php" target="_self">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
                    <path fill="#cfdcef" d="M104 112C90.7 112 80 122.7 80 136L80 184C80 197.3 90.7 208 104 208L152 208C165.3 208 176 197.3 176 184L176 136C176 122.7 165.3 112 152 112L104 112zM256 128C238.3 128 224 142.3 224 160C224 177.7 238.3 192 256 192L544 192C561.7 192 576 177.7 576 160C576 142.3 561.7 128 544 128L256 128zM256 288C238.3 288 224 302.3 224 320C224 337.7 238.3 352 256 352L544 352C561.7 352 576 337.7 576 320C576 302.3 561.7 288 544 288L256 288zM256 448C238.3 448 224 462.3 224 480C224 497.7 238.3 512 256 512L544 512C561.7 512 576 497.7 576 480C576 462.3 561.7 448 544 448L256 448zM80 296L80 344C80 357.3 90.7 368 104 368L152 368C165.3 368 176 357.3 176 344L176 296C176 282.7 165.3 272 152 272L104 272C90.7 272 80 282.7 80 296zM104 432C90.7 432 80 442.7 80 456L80 504C80 517.3 90.7 528 104 528L152 528C165.3 528 176 517.3 176 504L176 456C176 442.7 165.3 432 152 432L104 432z"/>
                    </svg> Dashboard</a>
                </li>
                <li><a href="profile.php" target="_self"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
                    <path fill="#cfdcef" d="M320 312C386.3 312 440 258.3 440 192C440 125.7 386.3 72 320 72C253.7 72 200 125.7 200 192C200 258.3 253.7 312 320 312zM290.3 368C191.8 368 112 447.8 112 546.3C112 562.7 125.3 576 141.7 576L498.3 576C514.7 576 528 562.7 528 546.3C528 447.8 448.2 368 349.7 368L290.3 368z"/>
                    </svg> Profile</a>
                </li>
                <li><a href="my_jobs.php" target="_self"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
                    <path fill="#cfdcef" d="M192 64C156.7 64 128 92.7 128 128L128 544C128 555.5 134.2 566.2 144.2 571.8C154.2 577.4 166.5 577.3 176.4 571.4L320 485.3L463.5 571.4C473.4 577.3 485.7 577.5 495.7 571.8C505.7 566.1 512 555.5 512 544L512 128C512 92.7 483.3 64 448 64L192 64z"/>
                    </svg> My Jobs</a>
                </li>
                <li><a href="create_job.php" target="_self"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
                    <path fill="#cfdcef" d="M160 144C151.2 144 144 151.2 144 160L144 480C144 488.8 151.2 496 160 496L480 496C488.8 496 496 488.8 496 480L496 160C496 151.2 488.8 144 480 144L160 144zM96 160C96 124.7 124.7 96 160 96L480 96C515.3 96 544 124.7 544 160L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 160zM296 408L296 344L232 344C218.7 344 208 333.3 208 320C208 306.7 218.7 296 232 296L296 296L296 232C296 218.7 306.7 208 320 208C333.3 208 344 218.7 344 232L344 296L408 296C421.3 296 432 306.7 432 320C432 333.3 421.3 344 408 344L344 344L344 408C344 421.3 333.3 432 320 432C306.7 432 296 421.3 296 408z"/>
                    </svg> Post a Job</a>
                </li>
                <li><a href="applications.php" target="_self" class="active"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
                    <path fill="#cfdcef" d="M535.6 85.7C513.7 63.8 478.3 63.8 456.4 85.7L432 110.1L529.9 208L554.3 183.6C576.2 161.7 576.2 126.3 554.3 104.4L535.6 85.7zM236.4 305.7C230.3 311.8 225.6 319.3 222.9 327.6L193.3 416.4C190.4 425 192.7 434.5 199.1 441C205.5 447.5 215 449.7 223.7 446.8L312.5 417.2C320.7 414.5 328.2 409.8 334.4 403.7L496 241.9L398.1 144L236.4 305.7zM160 128C107 128 64 171 64 224L64 480C64 533 107 576 160 576L416 576C469 576 512 533 512 480L512 384C512 366.3 497.7 352 480 352C462.3 352 448 366.3 448 384L448 480C448 497.7 433.7 512 416 512L160 512C142.3 512 128 497.7 128 480L128 224C128 206.3 142.3 192 160 192L256 192C273.7 192 288 177.7 288 160C288 142.3 273.7 128 256 128L160 128z"/>
                    </svg> Applications</a>
                </li>
            </ul>

            <p class="nav-label">Account</p>
            <ul class="nav-links">
                <li><a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
                    <path fill="#cfdcef" d="M267.7 576.9C267.7 576.9 267.7 576.9 267.7 576.9L229.9 603.6C222.6 608.8 213 609.4 205 605.3C197 601.2 192 593 192 584L192 512L160 512C107 512 64 469 64 416L64 192C64 139 107 96 160 96L480 96C533 96 576 139 576 192L576 416C576 469 533 512 480 512L359.6 512L267.7 576.9zM332 472.8C340.1 467.1 349.8 464 359.7 464L480 464C506.5 464 528 442.5 528 416L528 192C528 165.5 506.5 144 480 144L160 144C133.5 144 112 165.5 112 192L112 416C112 442.5 133.5 464 160 464L216 464C226.4 464 235.3 470.6 238.6 479.9C239.5 482.4 240 485.1 240 488L240 537.7C272.7 514.6 303.3 493 331.9 472.8z"/>
                    </svg> Messages</a>
                </li>
                <li><a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
                    <path fill="#cfdcef" d="M320 64C302.3 64 288 78.3 288 96L288 99.2C215 114 160 178.6 160 256L160 277.7C160 325.8 143.6 372.5 113.6 410.1L103.8 422.3C98.7 428.6 96 436.4 96 444.5C96 464.1 111.9 480 131.5 480L508.4 480C528 480 543.9 464.1 543.9 444.5C543.9 436.4 541.2 428.6 536.1 422.3L526.3 410.1C496.4 372.5 480 325.8 480 277.7L480 256C480 178.6 425 114 352 99.2L352 96C352 78.3 337.7 64 320 64zM258 528C265.1 555.6 290.2 576 320 576C349.8 576 374.9 555.6 382 528L258 528z"/>
                    </svg> Notifications</a>
                </li>
                <li><a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
                    <path fill="#cfdcef" d="M259.1 73.5C262.1 58.7 275.2 48 290.4 48L350.2 48C365.4 48 378.5 58.7 381.5 73.5L396 143.5C410.1 149.5 423.3 157.2 435.3 166.3L503.1 143.8C517.5 139 533.3 145 540.9 158.2L570.8 210C578.4 223.2 575.7 239.8 564.3 249.9L511 297.3C511.9 304.7 512.3 312.3 512.3 320C512.3 327.7 511.8 335.3 511 342.7L564.4 390.2C575.8 400.3 578.4 417 570.9 430.1L541 481.9C533.4 495 517.6 501.1 503.2 496.3L435.4 473.8C423.3 482.9 410.1 490.5 396.1 496.6L381.7 566.5C378.6 581.4 365.5 592 350.4 592L290.6 592C275.4 592 262.3 581.3 259.3 566.5L244.9 496.6C230.8 490.6 217.7 482.9 205.6 473.8L137.5 496.3C123.1 501.1 107.3 495.1 99.7 481.9L69.8 430.1C62.2 416.9 64.9 400.3 76.3 390.2L129.7 342.7C128.8 335.3 128.4 327.7 128.4 320C128.4 312.3 128.9 304.7 129.7 297.3L76.3 249.8C64.9 239.7 62.3 223 69.8 209.9L99.7 158.1C107.3 144.9 123.1 138.9 137.5 143.7L205.3 166.2C217.4 157.1 230.6 149.5 244.6 143.4L259.1 73.5zM320.3 400C364.5 399.8 400.2 363.9 400 319.7C399.8 275.5 363.9 239.8 319.7 240C275.5 240.2 239.8 276.1 240 320.3C240.2 364.5 276.1 400.2 320.3 400z"/>
                    </svg> Settings</a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <a href="../includes/logout.inc.php"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
                <path fill="#cfdcef" d="M569 337C578.4 327.6 578.4 312.4 569 303.1L425 159C418.1 152.1 407.8 150.1 398.8 153.8C389.8 157.5 384 166.3 384 176L384 256L272 256C245.5 256 224 277.5 224 304L224 336C224 362.5 245.5 384 272 384L384 384L384 464C384 473.7 389.8 482.5 398.8 486.2C407.8 489.9 418.1 487.9 425 481L569 337zM224 160C241.7 160 256 145.7 256 128C256 110.3 241.7 96 224 96L160 96C107 96 64 139 64 192L64 448C64 501 107 544 160 544L224 544C241.7 544 256 529.7 256 512C256 494.3 241.7 480 224 480L160 480C142.3 480 128 465.7 128 448L128 192C128 174.3 142.3 160 160 160L224 160z"/>
                </svg> Log Out</a>
            </div>
        </aside>
        <main>
            <div class="header-part">
                <div class="search-box">
                    <input type="text" placeholder="Search Candidates..." id="searchCandidates">
                    <span>🔍</span>
                </div>
                
                <select id="statusFilter" name="status">
                    <option value="all">All Statuses</option>
                    <option value="applied">Applied</option>
                    <option value="review">Under Review</option>
                    <option value="shortlisted">Shortlisted</option>
                    <option value="interview">Interview Scheduled</option>
                    <option value="offered">Offered</option>
                    <option value="hired">Hired</option>
                    <option value="rejected">Rejected</option>
                </select>
                
                <div class="post-btn">
                    <a href="my_jobs.php" target="_self" style="text-decoration:none;">
                    <button>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="26px">
                        <path fill="#fff" d="M160 144C151.2 144 144 151.2 144 160L144 480C144 488.8 151.2 496 160 496L480 496C488.8 496 496 488.8 496 480L496 160C496 151.2 488.8 144 480 144L160 144zM96 160C96 124.7 124.7 96 160 96L480 96C515.3 96 544 124.7 544 160L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 160zM296 408L296 344L232 344C218.7 344 208 333.3 208 320C208 306.7 218.7 296 232 296L296 296L296 232C296 218.7 306.7 208 320 208C333.3 208 344 218.7 344 232L344 296L408 296C421.3 296 432 306.7 432 320C432 333.3 421.3 344 408 344L344 344L344 408C344 421.3 333.3 432 320 432C306.7 432 296 421.3 296 408z"/>
                        </svg>
                        Post a Job
                    </button>
                    </a>
                </div>

                <div class="topbar-right">
                    <span> </span>
                    <a href="profile.php" target="_self" title="View Profile">
                        <div class="profile">
                            <div class="avatar"><img src="../css/icons/user2.svg" alt="" width="35px"></div>
                            <div>
                                <div class="name"><?php echo htmlspecialchars($companyName); ?></div>
                                <div class="email"><?php echo htmlspecialchars($userEmail); ?></div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            
            <section class="btn-section">
                <div class="post-btn">
                    <a href="my_jobs.php" target="_self">
                    <button>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="26px">
                        <path fill="#fff" d="M192 64C156.7 64 128 92.7 128 128L128 544C128 555.5 134.2 566.2 144.2 571.8C154.2 577.4 166.5 577.3 176.4 571.4L320 485.3L463.5 571.4C473.4 577.3 485.7 577.5 495.7 571.8C505.7 566.1 512 555.5 512 544L512 128C512 92.7 483.3 64 448 64L192 64z"/>
                        </svg>
                        View Jobs
                    </button>
                    </a>
                </div>
            </section>
            
            <section class="panel">
                <div class="applications-header">
                    <h2><?php echo htmlspecialchars($jobTitle); ?></h2>
                    <?php if (isset($jobID)): ?>
                        <a href="applications.php" class="back-link">← View All Applications</a>
                    <?php else: ?>
                        <a href="my_jobs.php" class="back-link">← Back to Jobs</a>
                    <?php endif; ?>
                </div>
                
                <div class="table-wrap">
                    <table class="wil-table" id="applicationsTable">
                        <thead>
                            <tr>
                                <?php if (!isset($jobID)): ?>
                                    <th>Job Title</th>
                                <?php endif; ?>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>CV</th>
                                <th>Status</th>
                                <th>Applied Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($applicationsError)): ?>
                                <tr>
                                    <td colspan="<?php echo isset($jobID) ? '6' : '7'; ?>" style="text-align: center; padding: 40px; color: #dc3545;">
                                        <?php echo htmlspecialchars($applicationsError); ?>
                                    </td>
                                </tr>
                            <?php elseif (empty($applications)): ?>
                                <tr>
                                    <td colspan="<?php echo isset($jobID) ? '6' : '7'; ?>" style="text-align: center; padding: 40px; color: #6c757d;">
                                        <p style="font-size: 18px; margin-bottom: 10px;">No applications yet</p>
                                        <p style="font-size: 14px;">Students haven't applied to your job postings yet</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($applications as $application): 
                                    // Status styling
                                    $statusClass = 'pill-' . strtolower($application['status'] ?? 'applied');
                                    $statusLabel = ucfirst($application['status'] ?? 'Applied');
                                    
                                    // Format date
                                    $appliedDate = isset($application['appliedAt']) ? date('M d, Y', strtotime($application['appliedAt'])) : 'N/A';
                                ?>
                                <tr data-status="<?php echo htmlspecialchars($application['status'] ?? 'applied'); ?>">
                                    <?php if (!isset($jobID)): ?>
                                        <td>
                                            <a href="applications.php?jobID=<?php echo $application['jobID']; ?>" class="job-title-link">
                                                <?php echo htmlspecialchars($application['jobTitle']); ?>
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <p class="row-title"><?php echo htmlspecialchars($application['studentName']); ?></p>
                                    </td>
                                    <td><?php echo htmlspecialchars($application['studentEmail'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if (!empty($application['cvFile'])): ?>
                                            <a href="../<?php echo htmlspecialchars($application['cvFile']); ?>" download class="download-link">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20px">
                                                <path fill="#0b1f4d" d="M320 64C302.3 64 288 78.3 288 96L288 293.3L241.4 246.6C229.8 235 211.8 235 200.2 246.6C188.6 258.3 188.6 276.3 200.2 287.9L303 390.7C314.6 402.3 332.6 402.3 344.2 390.7L447 287.9C458.6 276.3 458.6 258.3 447 246.6C435.4 235 417.4 235 405.8 246.6L360 292.3L360 96C360 78.3 345.7 64 328 64L320 64zM160 480L480 480C497.7 480 512 465.7 512 448C512 430.3 497.7 416 480 416L160 416C142.3 416 128 430.3 128 448C128 465.7 142.3 480 160 480z"/>
                                                </svg>
                                                Download CV
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #6c757d;">No CV</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="pill <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span></td>
                                    <td><?php echo $appliedDate; ?></td>
                                    <td>
                                        <div class="row-actions">
                                            <form action="../includes/status_handler.inc.php" method="post" style="display: inline-block;">
                                                <input type="hidden" name="applicationID" value="<?php echo $application['id']; ?>">
                                                <input type="hidden" name="status" value="accepted">
                                                <input type="hidden" name="jobID" value="<?php echo $application['jobID']; ?>">
                                                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars(basename(__FILE__) . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''), ENT_QUOTES); ?>">
                                                <button type="submit" class="btn-icon accept" title="Accept">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="25px">
                                                    <path fill="#28a745" d="M256 440.6L464 233.4C477.3 220.1 477.3 199 464 185.7C450.7 172.4 429.6 172.4 416.3 185.7L243.7 358.3L159.7 274.3C146.4 261 125.3 261 112 274.3C98.7 287.6 98.7 308.7 112 322L228 438C234.6 444.6 244.5 448 256 448C267.5 448 277.4 444.6 284 438L284 440.6C290.6 434 294 424.1 294 412.6L294 412.6C294 401.1 290.6 391.2 284 384.6L256 440.6z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                            <form action="../includes/status_handler.inc.php" method="post" style="display: inline-block;">
                                                <input type="hidden" name="applicationID" value="<?php echo $application['id']; ?>">
                                                <input type="hidden" name="status" value="denied">
                                                <input type="hidden" name="jobID" value="<?php echo $application['jobID']; ?>">
                                                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars(basename(__FILE__) . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''), ENT_QUOTES); ?>">
                                                <button type="submit" class="btn-icon deny" title="Deny">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="25px">
                                                    <path fill="#dc3545" d="M160 144C151.2 144 144 151.2 144 160L144 480C144 488.8 151.2 496 160 496L480 496C488.8 496 496 488.8 496 480L496 160C496 151.2 488.8 144 480 144L160 144zM96 160C96 124.7 124.7 96 160 96L480 96C515.3 96 544 124.7 544 160L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 160zM240 240C253.3 240 264 250.7 264 264L264 376C264 389.3 253.3 400 240 400C226.7 400 216 389.3 216 376L216 264C216 250.7 226.7 240 240 240zM400 240C413.3 240 424 250.7 424 264L424 376C424 389.3 413.3 400 400 400C386.7 400 376 389.3 376 376L376 264C376 250.7 386.7 240 400 240z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Filter and search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('statusFilter');
            const searchInput = document.getElementById('searchCandidates');
            const tableRows = document.querySelectorAll('#applicationsTable tbody tr');
            
            // Filter by status
            statusFilter.addEventListener('change', function() {
                filterTable();
            });
            
            // Search functionality
            searchInput.addEventListener('keyup', function() {
                filterTable();
            });
            
            function filterTable() {
                const filterStatus = statusFilter.value;
                const searchTerm = searchInput.value.toLowerCase();
                
                tableRows.forEach(row => {
                    const rowStatus = row.dataset.status;
                    const rowText = row.textContent.toLowerCase();
                    
                    const matchesFilter = (filterStatus === 'all' || rowStatus === filterStatus);
                    const matchesSearch = rowText.includes(searchTerm);
                    
                    row.style.display = (matchesFilter && matchesSearch) ? '' : 'none';
                });
            }
        });
    </script>
</body>
</html>