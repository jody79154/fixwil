<?php

require_once "../includes/auth.inc.php";
require_once "../includes/session_timeout.inc.php";
require_role('recruiter');

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
    
    // Get all jobs with applicant counts
    $jobsQuery = "
        SELECT 
            j.*,
            COUNT(a.id) as applicant_count,
            (SELECT COUNT(*) FROM applications WHERE jobID = j.id) as total_applicants
        FROM job_postings j
        LEFT JOIN applications a ON j.id = a.jobID
        WHERE j.recruiterID = :recruiterID
        GROUP BY j.id
        ORDER BY j.createdAt DESC
    ";
    $jobsStmt = $pdo->prepare($jobsQuery);
    $jobsStmt->bindParam(":recruiterID", $userID);
    $jobsStmt->execute();
    $jobs = $jobsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate statistics
    $totalJobs = count($jobs);
    $activeJobs = 0;
    $totalApplicants = 0;
    $totalViews = 0; 
    
    foreach ($jobs as $job) {
        if ($job['status'] === 'open') {
            $activeJobs++;
        }
        $totalApplicants += $job['total_applicants'];
        
    }
    
} catch (PDOException $e) {
    $jobs = [];
    $totalJobs = 0;
    $activeJobs = 0;
    $totalApplicants = 0;
    $totalViews = 0;
    $companyName = 'My Company';
    $userEmail = '';
    $jobsError = "Error loading jobs: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>WIL-Connect | My Jobs</title>
		<!--<link rel="stylesheet" href="../css/sidebar.css"/>-->
		<style>
		    * { margin: 0; padding: 0; box-sizing: border-box; }
			
			body {
				font-family: 'Inter', sans-serif;
				background: #f4f8fb;
				color: #10233f;
			}

			h1, h2, h3, .brand {
				font-family: 'Poppins', sans-serif;
			}

			a {
				text-decoration: none;
				color: inherit;
			}

			.dashboard {
				display: flex;
				min-height: 100vh;
				
			}

			.sidebar {
				width: 230px;
				background: #0b1f4d;
				color: #fff;
				padding: 17px 16px;
				display: flex;
				flex-direction: column;
				position: sticky;
				top: 0;
				height: 100vh;
				/*border-radius: 0 30px 30px 0;*/
			}

			/*.brand {
				display: flex;
				align-items: center;
				gap: 10px;
				font-size: 20px;
				font-weight: 700;
				margin-bottom: 40px;
			}*/

			.head {
				display: flex;
				align-items: center;  
				margin-bottom: 30px;
			}
			
			img {
				margin-top: -10px;
			}

			.slogan {
				font-size: 8px;
				color: #cfdcef;
				margin: 0;
				line-height: 1.1;
			}

			.system_name {
				margin: 0;
				color: #fff;
				line-height: 1.1;
				padding-top: 10px;
				font-size: 18px;
				font-weight: 700;
				letter-spacing: 0.5px;
				white-space: nowrap; 
			}

			.nav-label {
				font-size: 11px;
				text-transform: uppercase;
				letter-spacing: 1px;
				color: #8ea3c2;
				margin: 16px 0 10px 8px;
			}

			.nav-links {
				list-style: none;
				display: flex;
				flex-direction: column;
				gap: 4px;
			}

			.nav-links a {
				display: flex;
				align-items: center;
				gap: 12px;
				padding: 11px 14px;
				border-radius: 10px;
				color: #cfdcef;
				font-size: 14.5px;
				font-weight: 500;
				transition: background 0.15s ease, color 0.15s ease;
			}

			.nav-links a .icon {
				font-size: 13px;
				width: 15px;
				text-align: center;
			}

			.icon {
				width: 20px;
			}

			.nav-links a:hover {
				background: #13335f;
				color: #fff;
			}


			.nav-links a.active {
				background: rgba(245, 166, 35, .14);
				color: #fff;
				border-left: 4px solid #f5a623; 
			}

			.sidebar-footer {
				margin-top: 10px;
				padding-top: 20px;
				border-top: 2px solid #f5a623;
			}

			.sidebar-footer a {
				display: flex;
				align-items: center;
				gap: 12px;
				padding: 10px 14px;
				border-radius: 10px;
				color: #cfdcef;
				font-size: 14px;
			}

			.sidebar-footer a:hover {
				background: #13335f;
				color: #fff;
			}

			main {
				padding: 10px 16px;
				flex: 1;
			}

            .header-part {
				display: flex;
				align-items: center;
				justify-content: space-between;
				margin-bottom: 28px;
				gap: 10px;
				flex-wrap: wrap;
				box-shadow: 0 4px 6px -4px rgba(11, 31, 77, 0.15);
				padding-bottom: 10px;
			}

			.header-part h1 {
				font-size: 23px;
				color: #0b1f4d;
			}

			.header-part p {
				color: #5b6b79;;
				font-size: 14px;
				margin-top: 4px;
			}

			.search-box {
				display: flex;
				align-items: center;
				/*gap: 3px;*/
				justify-content: space-between;
				/*margin-left: 22px;*/
				margin-left: 0;
				background: #fff;
				border: 1px solid #ccc;
				border-radius: 16px;
				padding: 6px 14px;
				width: 360px;
			}

			.search-box input {
				border: none;
				outline: none;
				font-family: inherit;
				font-size: 14px;
				width: 100%;
			}
			
			.search-box span {
				display: flex;
				align-items: center;
				justify-content: center;
            }

			.topbar-right {
				display: flex;
				align-items: center;
				gap: 16px;
				position: relative;
				box-shadow: -3px 0 5px -2px rgba(11, 31, 77, 0.15);
				padding-left: 18px;
			}

			.icon-btn {
				width: 30px;
				display: flex;
				align-items: center;
				justify-content: center;
				font-size: 12px;
				cursor: pointer;
				position: relative;
			}

			.icon-btn .dot {
				position: absolute;
				top: 2px;
				right: 6px;
				width: 8px;
				height: 8px;
				background: #f5a623;
				border-radius: 50%;
				border: 2px solid #fff;
			}

			/* Notification & Profile Popups Container */
			.notification-box, .profile-box {
				width: 320px;
				background-color: white;
				border-radius: 10px;
				box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
				position: absolute;
				top: 60px;
				right: 20px;
				display: none;
				z-index: 1000;
			}
			
			.notification-header, .profile-header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				padding: 15px;
			}
			
			.notification-header h3, .profile-header h3 {
				margin: 0;
				font-size: 16px;
			}
			
			.close-button {
				border: none;
				background: none;
				cursor: pointer;
				width: 30px;
                height: 30px;
			}
			
			.close-button:hover {
				background: #dfe8fb;
                border-radius: 50%;
			}
			
			.notification {
				padding: 12px 15px;
				display: flex;
				border-left: 3px solid #f5a623;
				background: #fffbf5;
				border-radius: 12px;
				margin: 0 5px;
				gap: 10px;
				align-items: center;
			}
			
			.notification h4 {
				margin: 0 0 5px 0;
			}
			
			.notification p {
				margin: 0;
				font-size: 14px;
				color: #666;
			}
			
			.see-more {
				display: block;
				text-align: center;
				padding: 15px;
				text-decoration: none;
				color: #1e3a8a;
				font-size: 12px;
			}

			.profile-header h3 {
				color: #0b1f4d;
			}
			
			.profile-body {
				padding: 10px;
				text-align: center;
			}
			
			.profile-avatar {
				display: flex;
				border-radius: 50%;
			}
			
			.profile-dets {
				margin-top: 18px;
			}
			
			.profile-name {
				font-size: 18px;
				font-weight: 700;
				color: #0b1f4d;
			}
			
			.profile-email {
				font-size: 13px;
				color: #5b6b79;
				margin-bottom: 15px;
			}
			
			/* Responsive Breakpoints */
			@media (max-width: 992px) {
				.content-grid {
					grid-template-columns: 1fr;
				}
			}
			
			@media (max-width: 768px) {
				.dashboard {
					flex-direction: column;
				}
				.sidebar {
					width: 100%;
					height: auto;
					position: relative;
				}
				.search-box {
					width: 100%;
				}
				.topbar-right {
					box-shadow: none;
					padding-left: 0;
				}
			}
			
			.middle-part {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 20px;
			}

			.post-section {
				display: flex;
                justify-content: flex-end;
			}
			
			.post-btn {
				width: 150px;
				height: 40px;
				background: #116ad8;
				border: none;
				border-radius: 20px;
				font-family: inherit;
				font-weight: 600;
				font-size: 14px;
				cursor: pointer;
				box-shadow: 0 4px 7px rgba(151, 196, 250, 0.822);
				color: #fff;
				display: flex;
				align-items: center;
				justify-content: center;
				gap: 8px;
				transition: background 0.2s ease, position 0.2s ease;
			}
			
			.post-btn:hover {
				background: #02397c;
			}

			/*Section two*/
			.content-grid {
              display: grid;
              grid-template-columns: 1.7fr 1fr;
              gap: 22px;
			}

			.panel {
              background: #fff;
              border: 1px solid #e6ecf2;
              border-radius: 14px;
              box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
              padding: 22px 24px;
			}

			.panel .card-head {
              display: flex;
              justify-content: space-between;
              align-items: center;
              margin-bottom: 18px;
			}

			.panel h3 {
              font-size: 16px;
              color: #0b2545;
			}

			.panel .view-all {
              font-size: 13px;
              color: #116ad8;
              font-weight: 600;
			}

			
			#status {
				padding: 10px 11px;
				border: 1px solid #116ad8;
				background: #fff;
				border-radius: 10px;
				color: #000;
				font-weight: 400;
			}

			.stat-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
				gap: 18px;
				margin-bottom: 30px;
			}

			.stat-card {
				border-left: 3px solid #0b1f4d;
				box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
				background: #fff;
				border-radius: 8px;
				padding: 14px;
				height: auto;
			}
			
			.stat-value {
                font-size: 30px;
				font-weight: 600;
				margin-left: 120px;
				color: #0b1f4d;
			}
			
			.stat-icon {
                display: flex;
				align-items: center;
				gap: 6px;
				padding: 10px 0;
			}
			
			.stat-icon span {
                display: inline-flex;
				align-items: center;
			}
			

			.table-wrap { overflow-x: auto; }

			table.wil-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }

			.wil-table th {
				text-align: left;
				font-size: 14px;
				text-transform: uppercase;
				letter-spacing: .6px;
				color: #5b6b79;
				padding: 10px 14px;
				border-bottom: 1.5px solid #d9dadb;
			}

			.wil-table td {
				padding: 16px 14px;
				border-bottom: 1px solid #eef3fb; 
				vertical-align: middle;
			}

			.wil-table tr:hover td { background: #dcecf8; }

			.row-title { font-weight: 700; color: #0b1f4d; font-size: 14px; }

			.row-sub { font-size: 12px; color: #8792ad; margin-top: 2px; }

			.row-actions { display: flex; gap: 8px; }

			.btn-icon {
				width: 32px; 
				height: 32px;
				border-radius: 8px;
				border: 1px solid #e2e8f5;
				background: #fff;
				display: inline-flex; 
				align-items: center; 
				justify-content: center;
				color: #48557a;
				transition: all .15s ease;
				cursor: pointer;
			}
			
			.btn-icon:hover { 
				border-color: #5b6b79;
				background: #ebf3fa;
			}
			
			.btn-icon.edit:hover {
				border-color: #5b6b79;
				background: #ebf3fa;

			}

			.btn-icon.danger:hover { 
                border-color: #5b6b79;
                background: #ebf3fa;
			}

			.filter-tabs { 
				display: flex; 
				gap: 10px; 
				flex-wrap: wrap; 
				margin-bottom: 22px; 
			}

			.filter-tabs button {
				background: #f0f4f9;
				border: none;
				color: #48557a;
				padding: 8px 18px;
				border-radius: 20px;
				font-size: 13.5px;
				font-weight: 600;
				cursor: pointer;
				transition: all 0.2s ease;
			}

			.filter-tabs button.active { 
				background: #2563eb; 
				color: #fff; 
			}

			
			.tag-input-box {
				display: flex;
				flex-wrap: wrap;
				gap: 8px;
				border: 1.5px solid #e2e8f5;
				border-radius: 10px;
				padding: 10px;
				min-height: 48px;
			}
			.tag-input-box input {
				border: none; outline: none; flex: 1; min-width: 120px; font-size: 13.5px; padding: 4px;
			}
			.tag {
				display: inline-flex; align-items: center; gap: 6px;
				background: #eef3fb; color: #116ad8;
				padding: 5px 10px; border-radius: 20px; font-size: 12.5px; font-weight: 600;
			}
			.tag button { background: none; border: none; color: #116ad8; font-size: 13px; }

			.checkbox-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px,1fr)); gap: 10px; }
			.checkbox-item {
				display: flex; align-items: center; gap: 10px;
				border: 1.5px solid #e2e8f5; border-radius: 10px; padding: 10px 14px; font-size: 13px; font-weight: 600;
			}
			.checkbox-item input { width: 16px; height: 16px; accent-color: #116ad8; }

			.pill {
				padding: 6px 12px;
				border-radius: 20px;
				font-size: 12px;
				font-weight: 600;
			}
			.pill-active { background: #e3fcef; color: #00875a; }
			.pill-draft { background: #fff7e6; color: #d9822b; }
			.pill-closed { background: #ffebe6; color: #de350b; }
		</style>
	</head>
	<body>
		<div class="dashboard">
			<aside class="sidebar">
                
				<div class="head">
					<img src="../uploads/wil_logo.png" width="60" height="50"/>
					<div class="system-cont">
						<h3 class="system_name">WIL-Connect</h3>
						<p class="slogan">UNLOCKING OPPORTUNITIES</p>
					</div>
				</div>

                <p class="nav-label">Main</p>
                <ul class="nav-links">
                    <li><a href="dashboard.php" target="_self">
					    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
					    <path fill="#cfdcef" d="M104 112C90.7 112 80 122.7 80 136L80 184C80 197.3 90.7 208 104 208L152 208C165.3 208 176 197.3 176 184L176 136C176 122.7 165.3 112 152 112L104 112zM256 128C238.3 128 224 142.3 224 160C224 177.7 238.3 192 256 192L544 192C561.7 192 576 177.7 576 160C576 142.3 561.7 128 544 128L256 128zM256 288C238.3 288 224 302.3 224 320C224 337.7 238.3 352 256 352L544 352C561.7 352 576 337.7 576 320C576 302.3 561.7 288 544 288L256 288zM256 448C238.3 448 224 462.3 224 480C224 497.7 238.3 512 256 512L544 512C561.7 512 576 497.7 576 480C576 462.3 561.7 448 544 448L256 448zM80 296L80 344C80 357.3 90.7 368 104 368L152 368C165.3 368 176 357.3 176 344L176 296C176 282.7 165.3 272 152 272L104 272C90.7 272 80 282.7 80 296zM104 432C90.7 432 80 442.7 80 456L80 504C80 517.3 90.7 528 104 528L152 528C165.3 528 176 517.3 176 504L176 456C176 442.7 165.3 432 152 432L104 432z"/>
						</svg> Dashboard</a>
					</li>
					<li><a href="recruiter_profile.php" target="_self"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
					    <path fill="#cfdcef" d="M320 312C386.3 312 440 258.3 440 192C440 125.7 386.3 72 320 72C253.7 72 200 125.7 200 192C200 258.3 253.7 312 320 312zM290.3 368C191.8 368 112 447.8 112 546.3C112 562.7 125.3 576 141.7 576L498.3 576C514.7 576 528 562.7 528 546.3C528 447.8 448.2 368 349.7 368L290.3 368z"/>
						</svg> Profile</a>
					</li>
                    <li><a href="my_jobs.php" target="_self"  class="active"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
					    <path fill="#cfdcef" d="M264 112L376 112C380.4 112 384 115.6 384 120L384 160L256 160L256 120C256 115.6 259.6 112 264 112zM208 120L208 160L128 160C92.7 160 64 188.7 64 224L64 320L576 320L576 224C576 188.7 547.3 160 512 160L432 160L432 120C432 89.1 406.9 64 376 64L264 64C233.1 64 208 89.1 208 120zM576 368L384 368L384 384C384 401.7 369.7 416 352 416L288 416C270.3 416 256 401.7 256 384L256 368L64 368L64 480C64 515.3 92.7 544 128 544L512 544C547.3 544 576 515.3 576 480L576 368z"/>
						</svg> My Jobs</a>
					</li>
                    <li><a href="create_job.php" target="_self"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
					    <path fill="#cfdcef" d="M352 128C352 110.3 337.7 96 320 96C302.3 96 288 110.3 288 128L288 288L128 288C110.3 288 96 302.3 96 320C96 337.7 110.3 352 128 352L288 352L288 512C288 529.7 302.3 544 320 544C337.7 544 352 529.7 352 512L352 352L512 352C529.7 352 544 337.7 544 320C544 302.3 529.7 288 512 288L352 288L352 128z"/>
						</svg> Post a Job</a>
					</li>
                    <li><a href="applications.php" target="_self"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
					    <path fill="#cfdcef" d="M535.6 85.7C513.7 63.8 478.3 63.8 456.4 85.7L432 110.1L529.9 208L554.3 183.6C576.2 161.7 576.2 126.3 554.3 104.4L535.6 85.7zM236.4 305.7C230.3 311.8 225.6 319.3 222.9 327.6L193.3 416.4C190.4 425 192.7 434.5 199.1 441C205.5 447.5 215 449.7 223.7 446.8L312.5 417.2C320.7 414.5 328.2 409.8 334.4 403.7L496 241.9L398.1 144L236.4 305.7zM160 128C107 128 64 171 64 224L64 480C64 533 107 576 160 576L416 576C469 576 512 533 512 480L512 384C512 366.3 497.7 352 480 352C462.3 352 448 366.3 448 384L448 480C448 497.7 433.7 512 416 512L160 512C142.3 512 128 497.7 128 480L128 224C128 206.3 142.3 192 160 192L256 192C273.7 192 288 177.7 288 160C288 142.3 273.7 128 256 128L160 128z"/>
						</svg> Applications</a>
					</li>
                </ul>

                <p class="nav-label">Account</p>
                <ul class="nav-links">
                    <li><a href="messages.php"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"  class="icon">
					    <path fill="#cfdcef" d="M267.7 576.9C267.7 576.9 267.7 576.9 267.7 576.9L229.9 603.6C222.6 608.8 213 609.4 205 605.3C197 601.2 192 593 192 584L192 512L160 512C107 512 64 469 64 416L64 192C64 139 107 96 160 96L480 96C533 96 576 139 576 192L576 416C576 469 533 512 480 512L359.6 512L267.7 576.9zM332 472.8C340.1 467.1 349.8 464 359.7 464L480 464C506.5 464 528 442.5 528 416L528 192C528 165.5 506.5 144 480 144L160 144C133.5 144 112 165.5 112 192L112 416C112 442.5 133.5 464 160 464L216 464C226.4 464 235.3 470.6 238.6 479.9C239.5 482.4 240 485.1 240 488L240 537.7C272.7 514.6 303.3 493 331.9 472.8z"/>
						</svg> Messages</a>
					</li>
                    <li><a href="notifications.php"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
					    <path fill="#cfdcef" d="M320 64C302.3 64 288 78.3 288 96L288 99.2C215 114 160 178.6 160 256L160 277.7C160 325.8 143.6 372.5 113.6 410.1L103.8 422.3C98.7 428.6 96 436.4 96 444.5C96 464.1 111.9 480 131.5 480L508.4 480C528 480 543.9 464.1 543.9 444.5C543.9 436.4 541.2 428.6 536.1 422.3L526.3 410.1C496.4 372.5 480 325.8 480 277.7L480 256C480 178.6 425 114 352 99.2L352 96C352 78.3 337.7 64 320 64zM258 528C265.1 555.6 290.2 576 320 576C349.8 576 374.9 555.6 382 528L258 528z"/>
						</svg>Notifications</a>
					</li>
                    <li><a href="settings.php"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
					    <path fill="#cfdcef" d="M259.1 73.5C262.1 58.7 275.2 48 290.4 48L350.2 48C365.4 48 378.5 58.7 381.5 73.5L396 143.5C410.1 149.5 423.3 157.2 435.3 166.3L503.1 143.8C517.5 139 533.3 145 540.9 158.2L570.8 210C578.4 223.2 575.7 239.8 564.3 249.9L511 297.3C511.9 304.7 512.3 312.3 512.3 320C512.3 327.7 511.8 335.3 511 342.7L564.4 390.2C575.8 400.3 578.4 417 570.9 430.1L541 481.9C533.4 495 517.6 501.1 503.2 496.3L435.4 473.8C423.3 482.9 410.1 490.5 396.1 496.6L381.7 566.5C378.6 581.4 365.5 592 350.4 592L290.6 592C275.4 592 262.3 581.3 259.3 566.5L244.9 496.6C230.8 490.6 217.7 482.9 205.6 473.8L137.5 496.3C123.1 501.1 107.3 495.1 99.7 481.9L69.8 430.1C62.2 416.9 64.9 400.3 76.3 390.2L129.7 342.7C128.8 335.3 128.4 327.7 128.4 320C128.4 312.3 128.9 304.7 129.7 297.3L76.3 249.8C64.9 239.7 62.3 223 69.8 209.9L99.7 158.1C107.3 144.9 123.1 138.9 137.5 143.7L205.3 166.2C217.4 157.1 230.6 149.5 244.6 143.4L259.1 73.5zM320.3 400C364.5 399.8 400.2 363.9 400 319.7C399.8 275.5 363.9 239.8 319.7 240C275.5 240.2 239.8 276.1 240 320.3C240.2 364.5 276.1 400.2 320.3 400z"/>
						</svg> Settings</a>
					</li>
                </ul>

                <div class="sidebar-footer">
                    <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
					<path fill="#cfdcef" d="M569 337C578.4 327.6 578.4 312.4 569 303.1L425 159C418.1 152.1 407.8 150.1 398.8 153.8C389.8 157.5 384 166.3 384 176L384 256L272 256C245.5 256 224 277.5 224 304L224 336C224 362.5 245.5 384 272 384L384 384L384 464C384 473.7 389.8 482.5 398.8 486.2C407.8 489.9 418.1 487.9 425 481L569 337zM224 160C241.7 160 256 145.7 256 128C256 110.3 241.7 96 224 96L160 96C107 96 64 139 64 192L64 448C64 501 107 544 160 544L224 544C241.7 544 256 529.7 256 512C256 494.3 241.7 480 224 480L160 480C142.3 480 128 465.7 128 448L128 192C128 174.3 142.3 160 160 160L224 160z"/>
					</svg> Log Out</a>
                </div>
			</aside>
			<main>
				<div class="header-part">
					
					<div class="search-box">
						<input type="text" id="searchInput" placeholder="Search jobs...">
						<span>
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="18px">
							<path fill="#0b1f4d" d="M480 272C480 317.9 465.1 360.3 440 394.7L566.6 521.4C579.1 533.9 579.1 554.2 566.6 566.7C554.1 579.2 533.8 579.2 521.3 566.7L394.7 440C360.3 465.1 317.9 480 272 480C157.1 480 64 386.9 64 272C64 157.1 157.1 64 272 64C386.9 64 480 157.1 480 272zM272 416C351.5 416 416 351.5 416 272C416 192.5 351.5 128 272 128C192.5 128 128 192.5 128 272C128 351.5 192.5 416 272 416z"/>
							</svg>
						</span>
					</div>

					<div class="topbar-right">
						<a href="#" target="_self" id="notificationBell" title="View Notifications">
							<div class="icon-btn">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="topbar-icon">
									<path fill="#0b1f4d" d="M320 64C302.3 64 288 78.3 288 96L288 99.2C215 114 160 178.6 160 256L160 277.7C160 325.8 143.6 372.5 113.6 410.1L103.8 422.3C98.7 428.6 96 436.4 96 444.5C96 464.1 111.9 480 131.5 480L508.4 480C528 480 543.9 464.1 543.9 444.5C543.9 436.4 541.2 428.6 536.1 422.3L526.3 410.1C496.4 372.5 480 325.8 480 277.7L480 256C480 178.6 425 114 352 99.2L352 96C352 78.3 337.7 64 320 64zM258 528C265.1 555.6 290.2 576 320 576C349.8 576 374.9 555.6 382 528L258 528z"/>
								</svg>
								<span class="dot"></span>
							</div>
						</a>

						<div id="notificationBox" class="notification-box">
							<div class="notification-header">
								<h3 id="popupTitle">Notifications</h3>
								<button id="closeButton" class="close-button">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="19">
									<path fill="#0b1f4d" d="M183.1 137.4C170.6 124.9 150.3 124.9 137.8 137.4C125.3 149.9 125.3 170.2 137.8 182.7L275.2 320L137.9 457.4C125.4 469.9 125.4 490.2 137.9 502.7C150.4 515.2 170.7 515.2 183.2 502.7L320.5 365.3L457.9 502.6C470.4 515.1 490.7 515.1 503.2 502.6C515.7 490.1 515.7 469.8 503.2 457.3L365.8 320L503.1 182.6C515.6 170.1 515.6 149.8 503.1 137.3C490.6 124.8 470.3 124.8 457.8 137.3L320.5 274.7L183.1 137.4z"/>
									</svg>
								</button>
							</div>
							
							<div id="notificationContent">
								<div class="notification">
									<p>You have received a new job application</p>
								</div>
							</div>
							
							<div id="messageContent" style="display: none;">
								<div class="notification">
									<p>You have received a new job application</p>
								</div>
							</div>
							
							<a href="notifications.php" class="see-more" id="popupSeeMore">See More</a>
						</div>

						<a href="#" target="_self" id="messagesIcon" title="View Messages">
							<div class="icon-btn">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="topbar-icon">
									<path fill="#0b1f4d" d="M267.7 576.9C267.7 576.9 267.7 576.9 267.7 576.9L229.9 603.6C222.6 608.8 213 609.4 205 605.3C197 601.2 192 593 192 584L192 512L160 512C107 512 64 469 64 416L64 192C64 139 107 96 160 96L480 96C533 96 576 139 576 192L576 416C576 469 533 512 480 512L359.6 512L267.7 576.9zM332 472.8C340.1 467.1 349.8 464 359.7 464L480 464C506.5 464 528 442.5 528 416L528 192C528 165.5 506.5 144 480 144L160 144C133.5 144 112 165.5 112 192L112 416C112 442.5 133.5 464 160 464L216 464C226.4 464 235.3 470.6 238.6 479.9C239.5 482.4 240 485.1 240 488L240 537.7C272.7 514.6 303.3 493 331.9 472.8z"/>
								</svg>
								<span class="dot"></span>
							</div>
						</a>

						<!-- User Profile Icon Link -->
						<a href="#" id="profileIcon" title="View Profile" target="_self">
							<div class="icon-btn">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="topbar-icon">
									<path fill="#0b1f4d" d="M463 448.2C440.9 409.8 399.4 384 352 384L288 384C240.6 384 199.1 409.8 177 448.2C212.2 487.4 263.2 512 320 512C376.8 512 427.8 487.3 463 448.2zM64 320C64 178.6 178.6 64 320 64C461.4 64 576 178.6 576 320C576 461.4 461.4 576 320 576C178.6 576 64 461.4 64 320zM320 336C359.8 336 392 303.8 392 264C392 224.2 359.8 192 320 192C280.2 192 248 224.2 248 264C248 303.8 280.2 336 320 336z"/>
								</svg>
							</div>
						</a>

						<div id="profileBox" class="profile-box">
							<div class="profile-header">
								<h3>Profile</h3>
								<button id="closeProfileButton" class="close-button">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="19">
									<path fill="#0b1f4d" d="M183.1 137.4C170.6 124.9 150.3 124.9 137.8 137.4C125.3 149.9 125.3 170.2 137.8 182.7L275.2 320L137.9 457.4C125.4 469.9 125.4 490.2 137.9 502.7C150.4 515.2 170.7 515.2 183.2 502.7L320.5 365.3L457.9 502.6C470.4 515.1 490.7 515.1 503.2 502.6C515.7 490.1 515.7 469.8 503.2 457.3L365.8 320L503.1 182.6C515.6 170.1 515.6 149.8 503.1 137.3C490.6 124.8 470.3 124.8 457.8 137.3L320.5 274.7L183.1 137.4z"/>
									</svg>
								</button>
							</div>
							<div class="profile-body">
								<div class="profile-avatar">
									<!--<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="80" height="80">
										<path fill="#0b1f4d" d="M463 448.2C440.9 409.8 399.4 384 352 384L288 384C240.6 384 199.1 409.8 177 448.2C212.2 487.4 263.2 512 320 512C376.8 512 427.8 487.3 463 448.2zM64 320C64 178.6 178.6 64 320 64C461.4 64 576 178.6 576 320C576 461.4 461.4 576 320 576C178.6 576 64 461.4 64 320zM320 336C359.8 336 392 303.8 392 264C392 224.2 359.8 192 320 192C280.2 192 248 224.2 248 264C248 303.8 280.2 336 320 336z"/>
									</svg>-->
									<img src="<?php echo $logoSrc; ?>" width="80" height="80" style="border-radius:50%; object-fit:cover;" alt="">
									<div class="profile-dets">
										<p class="name"><?php echo htmlspecialchars($companyName); ?></p>
                                        <p class="email"><?php echo htmlspecialchars($userEmail); ?></p>
									</div>
								</div>
								
								<a href="recruiter_profile.php" class="see-more">View Profile</a>
							</div>
						</div>
					</div>
				</div>
				
				<section class="middle-part">
					<div class="dashboard-title">
						<h2>My Jobs</h2>
					</div>
					<div class="post-section">
						<a href="create_job.php" class="post-btn" id="postBtn">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="add-icon"  width="15px" height="15px">
								<path fill="#fff" d="M352 128C352 110.3 337.7 96 320 96C302.3 96 288 110.3 288 128L288 288L128 288C110.3 288 96 302.3 96 320C96 337.7 110.3 352 128 352L288 352L288 512C288 529.7 302.3 544 320 544C337.7 544 352 529.7 352 512L352 352L512 352C529.7 352 544 337.7 544 320C544 302.3 529.7 288 512 288L352 288L352 128z"/>
							</svg>
							<span>Post a New Job</span>
						</a>
					</div>
				</section>
				
				<section class="stat-grid">
					<?php
						$cards = [
							["label" => "Total Jobs Posted", "total" => "4", "icon" => '<path fill="#0b1f4d" d="M264 112L376 112C380.4 112 384 115.6 384 120L384 160L256 160L256 120C256 115.6 259.6 112 264 112zM208 120L208 160L128 160C92.7 160 64 188.7 64 224L64 320L576 320L576 224C576 188.7 547.3 160 512 160L432 160L432 120C432 89.1 406.9 64 376 64L264 64C233.1 64 208 89.1 208 120zM576 368L384 368L384 384C384 401.7 369.7 416 352 416L288 416C270.3 416 256 401.7 256 384L256 368L64 368L64 480C64 515.3 92.7 544 128 544L512 544C547.3 544 576 515.3 576 480L576 368z"/>'],
							["label" => "Active Listings", "total" => "2", "icon" => '<path fill="#0b1f4d" d="M416 224C398.3 224 384 209.7 384 192C384 174.3 398.3 160 416 160L576 160C593.7 160 608 174.3 608 192L608 352C608 369.7 593.7 384 576 384C558.3 384 544 369.7 544 352L544 269.3L374.6 438.7C362.1 451.2 341.8 451.2 329.3 438.7L224 333.3L86.6 470.6C74.1 483.1 53.8 483.1 41.3 470.6C28.8 458.1 28.8 437.8 41.3 425.3L201.3 265.3C213.8 252.8 234.1 252.8 246.6 265.3L352 370.7L498.7 224L416 224z"/>'],
							["label" => "Total Applicants", "total" => "44", "icon" => ' <path fill="#0b1f4d" d="M96 192C96 130.1 146.1 80 208 80C269.9 80 320 130.1 320 192C320 253.9 269.9 304 208 304C146.1 304 96 253.9 96 192zM32 528C32 430.8 110.8 352 208 352C305.2 352 384 430.8 384 528L384 534C384 557.2 365.2 576 342 576L74 576C50.8 576 32 557.2 32 534L32 528zM464 128C517 128 560 171 560 224C560 277 517 320 464 320C411 320 368 277 368 224C368 171 411 128 464 128zM464 368C543.5 368 608 432.5 608 512L608 534.4C608 557.4 589.4 576 566.4 576L421.6 576C428.2 563.5 432 549.2 432 534L432 528C432 476.5 414.6 429.1 385.5 391.3C408.1 376.6 435.1 368 464 368z"/>'],
							["label" => "Total Views", "total" => "22", "icon" => '<path fill="#0b1f4d" d="M320 96C239.2 96 174.5 132.8 127.4 176.6C80.6 220.1 49.3 272 34.4 307.7C31.1 315.6 31.1 324.4 34.4 332.3C49.3 368 80.6 420 127.4 463.4C174.5 507.1 239.2 544 320 544C400.8 544 465.5 507.2 512.6 463.4C559.4 419.9 590.7 368 605.6 332.3C608.9 324.4 608.9 315.6 605.6 307.7C590.7 272 559.4 220 512.6 176.6C465.5 132.9 400.8 96 320 96zM176 320C176 240.5 240.5 176 320 176C399.5 176 464 240.5 464 320C464 399.5 399.5 464 320 464C240.5 464 176 399.5 176 320zM320 256C320 291.3 291.3 320 256 320C244.5 320 233.7 317 224.3 311.6C223.3 322.5 224.2 333.7 227.2 344.8C240.9 396 293.6 426.4 344.8 412.7C396 399 426.4 346.3 412.7 295.1C400.5 249.4 357.2 220.3 311.6 224.3C316.9 233.6 320 244.4 320 256z"/>'],
						];
					?>
					
					<?php foreach ($cards as $card): ?>
					<div class="stat-card">
					    <p class="stat-label">
						    <?php echo $card['label']; ?>
						</p>
						<div class="stat-icon">
						    <span>
							    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="40" height="40">
							    <?php echo $card['icon']; ?>
								</svg>
							</span>
						    <h3 class="stat-value"><?php echo $card['total']; ?></h3>
						</div>
					</div>
					<?php endforeach; ?>
				</section>
				
				<section class="panel">
					<div class="filter-tabs" id="jobFilterTabs">
						<button class="active" data-filter="all">All (<?php echo $totalJobs; ?>)</button>
						<?php
							$activeCount = 0;
							$draftCount = 0;
							$closedCount = 0;
							foreach ($jobs as $job) {
								if ($job['status'] === 'open') $activeCount++;
								elseif ($job['status'] === 'draft') $draftCount++;
								elseif ($job['status'] === 'closed') $closedCount++;
							}
						?>
						<button data-filter="open">Active (<?php echo $activeCount; ?>)</button>
						<button data-filter="draft">Draft (<?php echo $draftCount; ?>)</button>
						<button data-filter="closed">Closed (<?php echo $closedCount; ?>)</button>
					</div>
					
					<div class="table-wrap">
						<table class="wil-table" id="jobsTable">
							<thead>
								<tr>
									<th>Job</th>
									<th>Type</th>
									<th>Applicants</th>
									<th>Posted</th>
									<th>Status</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php if (isset($jobsError)): ?>
									<tr>
										<td colspan="6" style="text-align: center; padding: 40px; color: #dc3545;">
											<?php echo htmlspecialchars($jobsError); ?>
										</td>
									</tr>
								<?php elseif (empty($jobs)): ?>
									<tr>
										<td colspan="6" style="text-align: center; padding: 40px; color: #6c757d;">
											<p style="font-size: 18px; margin-bottom: 10px;">No jobs posted yet</p>
											<p style="font-size: 14px;">Click "Post a Job" to create your first job listing</p>
										</td>
									</tr>
								<?php else: ?>
									<?php foreach ($jobs as $job): 
										// Calculate days ago
										$createdAt = new DateTime($job['createdAt']);
										$now = new DateTime();
										$interval = $createdAt->diff($now);
										if ($interval->days == 0) {
											$daysAgo = 'Today';
										} elseif ($interval->days == 1) {
											$daysAgo = 'Yesterday';
										} else {
											$daysAgo = $interval->days . ' days ago';
										}
										
										// Status styling
										$statusClass = '';
										$statusLabel = ucfirst($job['status']);
										if ($job['status'] === 'open') {
											$statusClass = 'pill-active';
										} elseif ($job['status'] === 'closed') {
											$statusClass = 'pill-closed';
										} else {
											$statusClass = 'pill-draft';
										}
									?>
									<tr data-status="<?php echo htmlspecialchars($job['status']); ?>">
										<td>
											<p class="row-title"><?php echo htmlspecialchars($job['jobTitle']); ?></p>
											<p class="row-sub"><?php echo htmlspecialchars($job['location'] ?? 'No location'); ?> · <?php echo htmlspecialchars($job['workSetup'] ?? 'N/A'); ?></p>
										</td>
										<td><?php echo htmlspecialchars($job['jobType'] ?? 'N/A'); ?></td>
										<td><?php echo $job['applicant_count']; ?> applicants</td>
										<td><?php echo $daysAgo; ?></td>
										<td><span class="pill <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span></td>
										<td>
											<div class="row-actions">
												<a class="btn-icon" href="applications.php?jobID=<?php echo $job['id']; ?>" title="View applicants">
													<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="25px">
													<path fill="#0b1f4d" d="M535.6 85.7C513.7 63.8 478.3 63.8 456.4 85.7L432 110.1L529.9 208L554.3 183.6C576.2 161.7 576.2 126.3 554.3 104.4L535.6 85.7zM236.4 305.7C230.3 311.8 225.6 319.3 222.9 327.6L193.3 416.4C190.4 425 192.7 434.5 199.1 441C205.5 447.5 215 449.7 223.7 446.8L312.5 417.2C320.7 414.5 328.2 409.8 334.4 403.7L496 241.9L398.1 144L236.4 305.7zM160 128C107 128 64 171 64 224L64 480C64 533 107 576 160 576L416 576C469 576 512 533 512 480L512 384C512 366.3 497.7 352 480 352C462.3 352 448 366.3 448 384L448 480C448 497.7 433.7 512 416 512L160 512C142.3 512 128 497.7 128 480L128 224C128 206.3 142.3 192 160 192L256 192C273.7 192 288 177.7 288 160C288 142.3 273.7 128 256 128L160 128z"/>
													</svg>
												</a>
												<a class="btn-icon" href="edit_job.php?id=<?php echo $job['id']; ?>" title="Edit Job">
													<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="25px">
													<path fill="#0b1f4d" d="M362.7 19.3L314.3 67.7L444.3 197.7148.4-48.4c25-25 25-65.5 0-90.5L453.3 19.3c-25-25-65.5-25-90.5 0zm-71 71L58.6 323.5c-10.3 10.3-17.8 23.1-21.5 37.2L1.3 494.3c-.8 3.2 1.1 6.4 4.3 7.2c1.1 .3 2.2 .5 3.3 .1l134.1-35.8c14.2-3.8 27-11.2 37.2-21.5L414.3 210.7 291.7 90.3z"/>
													</svg>
												</a>
												<?php if ($job['status'] === 'open'): ?>
													<a class="btn-icon danger" href="close_job.php?id=<?php echo $job['id']; ?>" 
													   onclick="return confirm('Are you sure you want to close this job posting?')" title="Close Job">
														<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="25px">
														<path fill="#dc3545" d="M160 144C151.2 144 144 151.2 144 160L144 480C144 488.8 151.2 496 160 496L480 496C488.8 496 496 488.8 496 480L496 160C496 151.2 488.8 144 480 144L160 144zM96 160C96 124.7 124.7 96 160 96L480 96C515.3 96 544 124.7 544 160L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 160zM240 240C253.3 240 264 250.7 264 264L264 376C264 389.3 253.3 400 240 400C226.7 400 216 389.3 216 376L216 264C216 250.7 226.7 240 240 240zM400 240C413.3 240 424 250.7 424 264L424 376C424 389.3 413.3 400 400 400C386.7 400 376 389.3 376 376L376 264C376 250.7 386.7 240 400 240z"/>
														</svg>
													</a>
												<?php endif; ?>
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
		    const notificationBell = document.getElementById("notificationBell");
			const messagesIcon = document.getElementById("messagesIcon");
			const notificationBox = document.getElementById("notificationBox");
			const popupTitle = document.getElementById("popupTitle");
			const closeButton = document.getElementById("closeButton");
			const popupSeeMore = document.getElementById("popupSeeMore");
			const notificationContent = document.getElementById("notificationContent");
			const messageContent = document.getElementById("messageContent");

			/* Hide notification/message popup */
			function hideNotificationPopup() {
				notificationBox.style.display = "none";
			}

			/* Show Notifications */
			function showNotifications(e) {
				e.preventDefault();
				popupTitle.textContent = "Notifications";

				/* Show notifications */
				notificationContent.style.display = "block";
				/* Hide messages */
				messageContent.style.display = "none";
				/* Notifications page */
				popupSeeMore.href = "notifications.php";
				notificationBox.style.display = "block";
			}

			/* Show Messages */
			function showMessages(e) {
				e.preventDefault();
				popupTitle.textContent = "Messages";
				/* Hide notifications */
				notificationContent.style.display = "none";
				/* Show messages */
				messageContent.style.display = "block";
				
				popupSeeMore.href = "messages.php";
				notificationBox.style.display = "block";
			}

			/* Notification icon */
			if (notificationBell) {
				notificationBell.addEventListener(
					"click",
					showNotifications
				);
			}

			/* Messages icon */
			if (messagesIcon) {
				messagesIcon.addEventListener(
					"click",
					showMessages
				);
			}

			/* Close popup */
			if (closeButton) {
				closeButton.addEventListener(
					"click",
					hideNotificationPopup
				);
			}
			
			
			function hideAllPopups() {

				notificationBox.style.display = "none";

				profileBox.style.display = "none";

			}

			// Profile Details Popup Handler
			if (profileIcon) {
				profileIcon.addEventListener("click", function(event) {
					event.preventDefault();
					hideAllPopups();
					profileBox.style.display = "block";
				});
			}

			if (closeProfileButton) {
				closeProfileButton.addEventListener("click", function() {
					profileBox.style.display = "none";
				});
			}
			
			// --- SAVED JOBS FROM POST JOB PAGE ---
			function loadSavedJobs() {
				const jobs = JSON.parse(localStorage.getItem("postedJobs")) || [];
				const tableBody = document.getElementById("jobsTableBody");
				if (!tableBody) return;

				tableBody.innerHTML = "";

				if (jobs.length === 0) {
					const emptyRow = document.createElement("tr");
					emptyRow.innerHTML = '<td colspan="6" style="text-align:center; padding:30px;">No jobs have been posted yet.</td>';
					tableBody.appendChild(emptyRow);
					return;
				}

				for (let i = 0; i < jobs.length; i++) {
					const job = jobs[i];
					let statusText = "Active";
					let statusClass = "pill-active";

					if (job.status === "draft") {
						statusText = "Draft";
						statusClass = "pill-draft";
					}
					if (job.status === "closed") {
						statusText = "Closed";
						statusClass = "pill-closed";
					}

					const row = document.createElement("tr");
					row.setAttribute("data-status", job.status || "active");
					row.setAttribute("data-job-id", job.id);

					row.innerHTML = `
						<td>
							<p class="row-title">${job.title || "Untitled Job"}</p>
							<p class="row-sub">${job.location || "Location not provided"} · ${job.arrangement || "Not specified"}</p>
						</td>
						<td>${job.type || "Not specified"}</td>
						<td>${job.applicants || 0} applicants</td>
						<td>${job.postedDate || "Not available"}</td>
						<td><span class="pill ${statusClass}">${statusText}</span></td>
						<td>
							<div class="row-actions">
								<a class="btn-icon view-btn" href="applications.php" title="View applicants">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="25px"><path fill="#a5acb1" d="M320 96C239.2 96 174.5 132.8 127.4 176.6C80.6 220.1 49.3 272 34.4 307.7C31.1 315.6 31.1 324.4 34.4 332.3C49.3 368 80.6 420 127.4 463.4C174.5 507.1 239.2 544 320 544C400.8 544 465.5 507.2 512.6 463.4C559.4 419.9 590.7 368 605.6 332.3C608.9 324.4 608.9 315.6 605.6 307.7C590.7 272 559.4 220 512.6 176.6C465.5 132.9 400.8 96 320 96zM176 320C176 240.5 240.5 176 320 176C399.5 176 464 240.5 464 320C464 399.5 399.5 464 320 464C240.5 464 176 399.5 176 320zM320 256C320 291.3 291.3 320 256 320C244.5 320 233.7 317 224.3 311.6C223.3 322.5 224.2 333.7 227.2 344.8C240.9 396 293.6 426.4 344.8 412.7C396 399 426.4 346.3 412.7 295.1C400.5 249.4 357.2 220.3 311.6 224.3C316.9 233.6 320 244.4 320 256z"/></svg>
								</a>
								<a class="btn-icon edit" href="post.php" title="Edit Job">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="25px"><path fill="#a5acb1" d="M416.9 85.2L372 130.1L509.9 268L554.8 223.1C568.4 209.6 576 191.2 576 172C576 152.8 568.4 134.4 554.8 120.9L519.1 85.2C505.6 71.6 487.2 64 468 64C448.8 64 430.4 71.6 416.9 85.2zM338.1 164L122.9 379.1C112.2 389.8 104.4 403.2 100.3 417.8L64.9 545.6C62.6 553.9 64.9 562.9 71.1 569C77.3 575.1 86.2 577.5 94.5 575.2L222.3 539.7C236.9 535.6 250.2 527.9 261 517.1L476 301.9L338.1 164z"/></svg>
								</a>
								<a class="btn-icon danger close-job-btn" title="Close job" href="#">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="25px"><path fill="#a5acb1" d="M320 576C461.4 576 576 461.4 576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320C64 461.4 178.6 576 320 576zM232 296L408 296C421.3 296 432 306.7 432 320C432 333.3 421.3 344 408 344L232 344C218.7 344 208 333.3 208 320C208 306.7 218.7 296 232 296z"/></svg>
								</a>
							</div>
						</td>`;

					tableBody.appendChild(row);
				}
			}

			function updateJobCounts() {
				const jobs = JSON.parse(localStorage.getItem("postedJobs")) || [];
				let activeCount = 0;
				let draftCount = 0;
				let closedCount = 0;
				let applicantCount = 0;

				for (let i = 0; i < jobs.length; i++) {
					if (jobs[i].status === "active") activeCount++;
					if (jobs[i].status === "draft") draftCount++;
					if (jobs[i].status === "closed") closedCount++;
					applicantCount += Number(jobs[i].applicants || 0);
				}

				const buttons = {
					all: document.querySelector('#jobFilterTabs button[data-filter="all"]'),
					active: document.querySelector('#jobFilterTabs button[data-filter="active"]'),
					draft: document.querySelector('#jobFilterTabs button[data-filter="draft"]'),
					closed: document.querySelector('#jobFilterTabs button[data-filter="closed"]')
				};

				if (buttons.all) buttons.all.textContent = "All (" + jobs.length + ")";
				if (buttons.active) buttons.active.textContent = "Active (" + activeCount + ")";
				if (buttons.draft) buttons.draft.textContent = "Draft (" + draftCount + ")";
				if (buttons.closed) buttons.closed.textContent = "Closed (" + closedCount + ")";

				const values = document.querySelectorAll(".stat-value");
				if (values.length >= 3) {
					values[0].textContent = jobs.length;
					values[1].textContent = activeCount;
					values[2].textContent = applicantCount;
				}
			}

			loadSavedJobs();
			updateJobCounts();

		
			/* Filter functionality
			document.addEventListener('DOMContentLoaded', function() {
				const filterButtons = document.querySelectorAll('#jobFilterTabs button');
				const tableRows = document.querySelectorAll('#jobsTable tbody tr');
				const searchInput = document.getElementById('searchJobs');
				
				// Filter by status
				filterButtons.forEach(button => {
					button.addEventListener('click', function() {
						// Update active button
						filterButtons.forEach(btn => btn.classList.remove('active'));
						this.classList.add('active');
						
						const filter = this.dataset.filter;
						filterTable(filter);
					});
				});
				
				// Search functionality
				searchInput.addEventListener('keyup', function() {
					const searchTerm = this.value.toLowerCase();
					const activeFilter = document.querySelector('#jobFilterTabs .active').dataset.filter;
					
					tableRows.forEach(row => {
						const rowStatus = row.dataset.status;
						const rowText = row.textContent.toLowerCase();
						
						const matchesFilter = (activeFilter === 'all' || rowStatus === activeFilter);
						const matchesSearch = rowText.includes(searchTerm);
						
						row.style.display = (matchesFilter && matchesSearch) ? '' : 'none';
					});
				});
				
				function filterTable(filter) {
					tableRows.forEach(row => {
						const rowStatus = row.dataset.status;
						const searchTerm = searchInput.value.toLowerCase();
						const rowText = row.textContent.toLowerCase();
						
						const matchesFilter = (filter === 'all' || rowStatus === filter);
						const matchesSearch = rowText.includes(searchTerm);
						
						row.style.display = (matchesFilter && matchesSearch) ? '' : 'none';
					});
				}
			});*/
			// --- INTERACTIVE FEATURES ADDED BELOW ---

			// 1. Filter Tabs Interactivity (All, Active, Draft, Closed)
			const filterBtns = document.querySelectorAll("#jobFilterTabs button");
			let tableRows = document.querySelectorAll("#jobsTable tbody tr");

			filterBtns.forEach(btn => {
				btn.addEventListener("click", function() {
					// Remove active state from all filter buttons
					filterBtns.forEach(b => b.classList.remove("active"));
					this.classList.add("active");

					tableRows = document.querySelectorAll("#jobsTable tbody tr");

					const selectedFilter = this.getAttribute("data-filter");

					tableRows.forEach(row => {
						const rowStatus = row.getAttribute("data-status");
						if (selectedFilter === "all" || rowStatus === selectedFilter) {
							row.style.display = "";
						} else {
							row.style.display = "none";
						}
					});
				});
			});

			// 2. Real-Time Live Search Filter
			const searchInput = document.getElementById("searchInput");
			if (searchInput) {
				searchInput.addEventListener("keyup", function() {
					const filterText = this.value.toLowerCase();
					tableRows = document.querySelectorAll("#jobsTable tbody tr");

					tableRows.forEach(row => {
						const text = row.textContent.toLowerCase();
						if (text.includes(filterText)) {
							row.style.display = "";
						} else {
							row.style.display = "none";
						}
					});
				});
			}

			// 3. Close Job / Delete Row Functionality
			function attachCloseButtons() {
				const closeJobBtns = document.querySelectorAll(".close-job-btn");
				closeJobBtns.forEach(btn => {
					btn.addEventListener("click", function(e) {
						e.preventDefault();
						const confirmClose = confirm("Are you sure you want to close this job listing?");
						if (!confirmClose) return;

						const row = this.closest("tr");
						const jobId = row.getAttribute("data-job-id");
						let jobs = JSON.parse(localStorage.getItem("postedJobs")) || [];

						for (let i = 0; i < jobs.length; i++) {
							if (String(jobs[i].id) === String(jobId)) {
								jobs[i].status = "closed";
							}
						}

						localStorage.setItem("postedJobs", JSON.stringify(jobs));
						loadSavedJobs();
						updateJobCounts();
						attachCloseButtons();
					});
				});
			}

			attachCloseButtons();

			// 4. View & Edit Row Alert Demonstrations
			const viewBtns = document.querySelectorAll(".view-btn");
			viewBtns.forEach(btn => {
				btn.addEventListener("click", function(e) {
					e.preventDefault();
					alert("Navigating to job applicants...");
				});
			});

			const editBtns = document.querySelectorAll(".btn-icon.edit");
			editBtns.forEach(btn => {
				btn.addEventListener("click", function(e) {
					e.preventDefault();
					alert("Opening Job Editor...");
				});
			});
		</script>
	</body>
</html>
