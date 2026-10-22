<?php

    require_once "../includes/auth.inc.php";
    require_once "../includes/session_timeout.inc.php";
    require_role('recruiter');

    $userID = $_SESSION['userID'];

        function wilTimeAgo($datetime) {
        $diff = time() - strtotime($datetime);
        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins == 1 ? '' : 's') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours == 1 ? '' : 's') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days == 1 ? '' : 's') . ' ago';
        } else {
            $weeks = floor($diff / 604800);
            return $weeks . ' week' . ($weeks == 1 ? '' : 's') . ' ago';
        }
    }

    try {
        require_once "../includes/dbh.inc.php";

        $userQuery = "SELECT company, email FROM users WHERE id = :userID";
        $userStmt = $pdo->prepare($userQuery);
        $userStmt->bindParam(":userID", $userID);
        $userStmt->execute();
        $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
        $companyName = $userData['company'] ?? 'My Company';
        $userEmail = $userData['email'] ?? '';

        $activeJobsQuery = " SELECT
            j.id, j.jobTitle, j.jobType, j.createdAt,
            COUNT(a.id) as applicantCount
            FROM job_postings j
            LEFT JOIN applications a ON j.id = a.jobID
            WHERE j.recruiterID = :userID AND j.status = 'open'
            GROUP BY j.id
            ORDER BY j.createdAt DESC
            LIMIT 3";
        $activeJobsStmt = $pdo->prepare($activeJobsQuery);
        $activeJobsStmt->bindParam(":userID", $userID);
        $activeJobsStmt->execute();
        $activeJobs = $activeJobsStmt->fetchAll(PDO::FETCH_ASSOC);

        $recentApplicantsQuery = " SELECT
            u.fullName as studentName,
            jp.jobTitle,
            ap.appliedAt
            FROM applications ap
            JOIN users u ON ap.studentID = u.id
            JOIN job_postings jp ON ap.jobID = jp.id
            WHERE jp.recruiterID = :userID
            ORDER BY ap.appliedAt DESC
            LIMIT 4";
        $recentApplicantsStmt = $pdo->prepare($recentApplicantsQuery);
        $recentApplicantsStmt->bindParam(":userID", $userID);
        $recentApplicantsStmt->execute();
        $recentApplicants = $recentApplicantsStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $companyName = 'My Company';
        $userEmail = '';
        $activeJobs = [];
        $recentApplicants = [];
    }
	
?>
<!DOCTYPE html>
<html>
    <head>
	    <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>WIL-Connect | Dashboard</title>
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

			.middle-part {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 20px;
			}

			.robot-section {
				display: flex;
                justify-content: flex-end;
			}
			
			.robot-btn {
				width: 150px;
				height: 40px;
				background: #0b1f4d;
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
			.robot-btn:hover {
				background: #02397c;
			}
			.robot-btn .robot-icon {
				width: 20px;
				height: 20px;
			}

			/* Fixed class dynamically toggled via JS */
			.robot-btn.is-fixed {
				position: fixed;
				top: 20px;
				right: 30px;
				z-index: 1000;
			}

			.cards-row {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
				gap: 20px;
				margin: 20px 0;
			}

			.card {
				padding: 10px 15px;
				border-left: 3px solid #f5a623;
				box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
				background: #fff;
				border-radius: 8px;
				transition: box-shadow .2s ease;
				cursor: pointer;
				font-family: 'Poppins', sans-serif;
			}

			.card:hover {
				box-shadow: 0 12px 28px rgba(11,31,77,.08); 
			}

			.card:hover .title {
				
				font-weight: bold;
			}

			.card .title { 
				font-weight: ; 
				color: #0b1f4d;
				font-size: 16px; 
			}

			.card h3 {
				font-size: 26px; 
				margin: 8px 0 6px; 
				font-weight: 800; 
				color: #f5a623;
			}

			.card p { 
				font-size: 13.5px;
			}

			.content-grid {
			  display: grid;
			  grid-template-columns: 1.7fr 1fr;
			  gap: 22px;
              margin-bottom: 22px;
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

			.job-item {
			  display: flex;
			  justify-content: space-between;
			  align-items: center;
			  padding: 14px 0;
			  border-bottom: 1px solid #e6ecf2;
			}

			.job-item:last-child {
			  border-bottom: none;
			  padding-bottom: 0;
			}

			.job-item .job-title {
			  font-size: 14px;
			  font-weight: 600;
			  color: #0b2545;
			}

			.job-item .job-meta {
			  font-size: 12.5px;
			  color: #5b6b79;
			  margin-top: 3px;
			}

			.job-item .job-applicants {
			  font-size: 13px;
			  font-weight: 600;
			  color: #116ad8;
			  background: #eaf2ff;
			  padding: 6px 12px;
			  border-radius: 20px;
			  white-space: nowrap;
			}

			.post-job-btn {
			  margin-top: 16px;
			  width: 100%;
			  padding: 12px;
			  background: #116ad8;
			  color: #fff;
			  border: none;
			  border-radius: 10px;
			  font-family: inherit;
			  font-weight: 600;
			  font-size: 14px;
			  cursor: pointer;
			  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
			}

			.post-job-btn:hover {
			  background: #02397c;
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
			
			.ai-chat-box {
				display: none;

				position: fixed;
				right: 25px;
				bottom: 25px;

				width: 350px;
				max-width: calc(100% - 40px);

				background: white;

				border: 1px solid #dfe3ec;
				border-radius: 12px;

				box-shadow: 0 8px 25px rgba(11, 31, 77, 0.18);

				overflow: hidden;

				z-index: 2000;
			}


			/* Show the AI window */

			.ai-chat-box.show {
				display: block;
			}


			/* AI Header */

			.ai-chat-header {
				background: #0b1f4d;

				color: white;

				padding: 14px 16px;

				display: flex;
				align-items: center;
				justify-content: space-between;
			}


			/* AI title */

			.ai-chat-title {
				display: flex;
				align-items: center;
				gap: 10px;
			}


			.ai-small-icon {
				background: #f5a623;
				border-radius: 50%;
				padding: 6px;
				display: flex;
				align-items: center;
				justify-content: center;
			}

			.ai-chat-title strong {
				display: block;

				font-size: 15px;
			}


			.ai-chat-title small {
				display: block;

				margin-top: 2px;

				font-size: 11px;

				color: #dce4f5;
			}


			/* Close button */

			.ai-close-button {
				background: none;

				border: none;

				color: white;

				font-size: 25px;

				cursor: pointer;

				line-height: 1;
			}


			.ai-close-button:hover {
				color: #f5a623;
			}


			/* Chat body */

			.ai-chat-body {

				height: 300px;

				padding: 15px;

				background: #f7f8fc;

				overflow-y: auto;
			}


			/* General AI message */

			.ai-message {

				max-width: 85%;

				padding: 10px 12px;

				margin-bottom: 12px;

				border-radius: 10px;

				font-size: 13px;

				line-height: 1.5;
			}


			/* AI message */

			.ai-bot-message {

				background: white;

				border: 1px solid #e3e6ee;

				color: #48557a;

				margin-right: auto;
			}


			.ai-bot-message strong {
				color: #0b1f4d;

				display: block;

				margin-bottom: 4px;
			}


			/* User message */

			.ai-user-message {

				background: #0b1f4d;

				color: white;

				margin-left: auto;
			}


			.ai-user-message strong {

				display: block;

				margin-bottom: 4px;
			}


			/* Chat footer */

			.ai-chat-footer {

				display: flex;

				gap: 8px;

				padding: 12px;

				background: white;

				border-top: 1px solid #e3e6ee;
			}


			.ai-chat-footer input {
				flex: 1;
				height: 38px;
				border: 1px solid #d7dce7;
				border-radius: 20px;
				padding: 0 12px;
				outline: none;
				font-size: 13px;
			}


			.ai-chat-footer input:focus {
				border-color: #0b1f4d;
			}


			/* Send button */

			.ai-chat-footer button {
				height: 38px;
				padding: 0 14px;
				background: #f5a623;
				color: #0b1f4d;
				border: none;
				border-radius: 20px;
				cursor: pointer;
				font-weight: bold;
			}
			
			
			.ai-chat-footer button:hover {
				background: #e89a16;
			}


			.robot-btn {
				display: flex;
				align-items: center;
				justify-content: center;
				gap: 8px;
				cursor: pointer;
				border: none;
			}


			.robot-icon {
				display: flex;
				align-items: center;
				justify-content: center;
			}


			@media (max-width: 600px) {

				.ai-chat-box {
					right: 15px;
					bottom: 15px;
					width: calc(100% - 30px);
				}
			}
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
                    <li><a href="dashboard.php" target="_self" class="active">
					    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
					    <path fill="#cfdcef" d="M104 112C90.7 112 80 122.7 80 136L80 184C80 197.3 90.7 208 104 208L152 208C165.3 208 176 197.3 176 184L176 136C176 122.7 165.3 112 152 112L104 112zM256 128C238.3 128 224 142.3 224 160C224 177.7 238.3 192 256 192L544 192C561.7 192 576 177.7 576 160C576 142.3 561.7 128 544 128L256 128zM256 288C238.3 288 224 302.3 224 320C224 337.7 238.3 352 256 352L544 352C561.7 352 576 337.7 576 320C576 302.3 561.7 288 544 288L256 288zM256 448C238.3 448 224 462.3 224 480C224 497.7 238.3 512 256 512L544 512C561.7 512 576 497.7 576 480C576 462.3 561.7 448 544 448L256 448zM80 296L80 344C80 357.3 90.7 368 104 368L152 368C165.3 368 176 357.3 176 344L176 296C176 282.7 165.3 272 152 272L104 272C90.7 272 80 282.7 80 296zM104 432C90.7 432 80 442.7 80 456L80 504C80 517.3 90.7 528 104 528L152 528C165.3 528 176 517.3 176 504L176 456C176 442.7 165.3 432 152 432L104 432z"/>
						</svg> Dashboard</a>
					</li>
					<li><a href="recruiter_profile.php" target="_self"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
					    <path fill="#cfdcef" d="M320 312C386.3 312 440 258.3 440 192C440 125.7 386.3 72 320 72C253.7 72 200 125.7 200 192C200 258.3 253.7 312 320 312zM290.3 368C191.8 368 112 447.8 112 546.3C112 562.7 125.3 576 141.7 576L498.3 576C514.7 576 528 562.7 528 546.3C528 447.8 448.2 368 349.7 368L290.3 368z"/>
						</svg> Profile</a>
					</li>
                    <li><a href="my_jobs.php"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
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
                    <li><a href="messages.php" target="_self"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"  class="icon">
					    <path fill="#cfdcef" d="M267.7 576.9C267.7 576.9 267.7 576.9 267.7 576.9L229.9 603.6C222.6 608.8 213 609.4 205 605.3C197 601.2 192 593 192 584L192 512L160 512C107 512 64 469 64 416L64 192C64 139 107 96 160 96L480 96C533 96 576 139 576 192L576 416C576 469 533 512 480 512L359.6 512L267.7 576.9zM332 472.8C340.1 467.1 349.8 464 359.7 464L480 464C506.5 464 528 442.5 528 416L528 192C528 165.5 506.5 144 480 144L160 144C133.5 144 112 165.5 112 192L112 416C112 442.5 133.5 464 160 464L216 464C226.4 464 235.3 470.6 238.6 479.9C239.5 482.4 240 485.1 240 488L240 537.7C272.7 514.6 303.3 493 331.9 472.8z"/>
						</svg> Messages</a>
					</li>
                    <li><a href="notifications.php" target="_self"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
					    <path fill="#cfdcef" d="M320 64C302.3 64 288 78.3 288 96L288 99.2C215 114 160 178.6 160 256L160 277.7C160 325.8 143.6 372.5 113.6 410.1L103.8 422.3C98.7 428.6 96 436.4 96 444.5C96 464.1 111.9 480 131.5 480L508.4 480C528 480 543.9 464.1 543.9 444.5C543.9 436.4 541.2 428.6 536.1 422.3L526.3 410.1C496.4 372.5 480 325.8 480 277.7L480 256C480 178.6 425 114 352 99.2L352 96C352 78.3 337.7 64 320 64zM258 528C265.1 555.6 290.2 576 320 576C349.8 576 374.9 555.6 382 528L258 528z"/>
						</svg>Notifications</a>
					</li>
                    <li><a href="settings.php" target="_self"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
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
                    <div
                        <h3 class="company-name">Welcome back, <?php echo htmlspecialchars($companyName); ?></h3>
                    </div>

                    <div class="search-box">
                        <input type="text" placeholder="Search...">
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

						<!-- Profile Details Popup Container -->
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
						<h2>Dashboard</h2>
					</div>
					<div class="robot-section">
						<button type="button" class="robot-btn" id="robotBtn">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="robot-icon">
								<path fill="#cfdcef" d="M352 64C352 46.3 337.7 32 320 32C302.3 32 288 46.3 288 64L288 128L192 128C139 128 96 171 96 224L96 448C96 501 139 544 192 544L448 544C501 544 544 501 544 448L544 224C544 171 501 128 448 128L352 128L352 64zM160 432C160 418.7 170.7 408 184 408L216 408C229.3 408 240 418.7 240 432C240 445.3 229.3 456 216 456L184 456C170.7 456 160 445.3 160 432zM280 432C280 418.7 290.7 408 304 408L336 408C349.3 408 360 418.7 360 432C360 445.3 349.3 456 336 456L304 456C290.7 456 280 445.3 280 432zM400 432C400 418.7 410.7 408 424 408L456 408C469.3 408 480 418.7 480 432C480 445.3 469.3 456 456 456L424 456C410.7 456 400 445.3 400 432zM224 240C250.5 240 272 261.5 272 288C272 314.5 250.5 336 224 336C197.5 336 176 314.5 176 288C176 261.5 197.5 240 224 240zM368 288C368 261.5 389.5 240 416 240C442.5 240 464 261.5 464 288C464 314.5 442.5 336 416 336C389.5 336 368 314.5 368 288zM64 288C64 270.3 49.7 256 32 256C14.3 256 0 270.3 0 288L0 384C0 401.7 14.3 416 32 416C49.7 416 64 401.7 64 384L64 288zM608 256C590.3 256 576 270.3 576 288L576 384C576 401.7 590.3 416 608 416C625.7 416 640 401.7 640 384L640 288C640 270.3 625.7 256 608 256z"/>
							</svg>
							<span>Ask Wil AI</span>
						</button>
					</div>
				</section>

				<section class="cards-container" id="cards-container">
					<?php
						$cards = [
							["title" => "Active Jobs", "total" => "8",  "date" => "this month."],
							["title" => "Total Applications", "total" => "12",  "date" => "this week."],
							["title" => "Total Hired", "total" => "50", "date" => "last month."],
							["title" => "Total Shortlisted", "total" => "22", "date" => "this month."],
						];
					?>
					<div class="cards-row">
						<?php foreach ($cards as $card): ?>
						<div class="card">
							<div class="title"><?php echo $card['title']; ?></div>
							<h3><?php echo $card['total']; ?></h3>
							<p><?php echo $card['date']; ?></p>
						</div>
						<?php endforeach; ?>
					</div>
				</section>

				<section class="content-grid" id="content-grid">
					<div class="panel">
						<div class="card-head">
							<h3>Hiring Pipeline Stats</h3>
							<span>146 candidates across all jobs</span>
						</div>
					</div>

					<div class="panel">
						<div class="card-head">
							<h3>Your Active Jobs</h3>
							<a href="jobs.php" class="view-all">Manage</a>
						</div>
						<?php if (empty($activeJobs)): ?>
							<p style="color:#5b6b79; font-size: 13.5px; padding: 10px 0;">No active job postings yet.</p>
						<?php else: ?>
							<?php foreach ($activeJobs as $job): ?>
							<div class="job-item">
								<div>
									<h3 class="job-title"><?php echo htmlspecialchars($job['jobTitle']); ?></h3>
									<p class="job-meta"><?php echo htmlspecialchars($job['jobType']); ?> <span class="dash-dot">.</span> Posted <?php echo wilTimeAgo($job['createdAt']); ?></p>
								</div>
								<p class="job-applicants"><?php echo (int) $job['applicantCount']; ?></p>
							</div>
							<?php endforeach; ?>
						<?php endif; ?>
						<button class="post-job-btn" onclick="window.location.href='post.php'">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="12">
								<path fill="#fff" d="M352 128C352 110.3 337.7 96 320 96C302.3 96 288 110.3 288 128L288 288L128 288C110.3 288 96 302.3 96 320C96 337.7 110.3 352 128 352L288 352L288 512C288 529.7 302.3 544 320 544C337.7 544 352 529.7 352 512L352 352L512 352C529.7 352 544 337.7 544 320C544 302.3 529.7 288 512 288L352 288L352 128z"/>
							</svg> 
							Post a New Job
						</button>
					</div>
				</section>

				<section class="content-grid">
					<div class="panel">
						<div class="card-head">
							<h3>Recent Applicants</h3>
							<a href="Applications.php" class="view-all">View all</a>
						</div>
						<?php if (empty($recentApplicants)): ?>
							<p style="color:#5b6b79; font-size: 13.5px; padding: 10px 0;">No applicants yet.</p>
						<?php else: ?>
							<?php foreach ($recentApplicants as $applicant): ?>
							<div class="notification">
								<span class="n-icon">
								    <img src="<?php // user profile picture will appear here?>" width=""/>
								</span>
								<div>
									<p><strong><?php echo htmlspecialchars($applicant['studentName']); ?></strong> applied for <?php echo htmlspecialchars($applicant['jobTitle']); ?></p>
									<span class="n-time"><?php echo wilTimeAgo($applicant['appliedAt']); ?></span>
								</div>
							</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div> 
				</section>
			</main>
		</div>
		
		<!-- WIL AI CHAT BOX -->
		<div class="ai-chat-box" id="aiChatBox">

			<!-- Chat Header -->
			<div class="ai-chat-header">

				<div class="ai-chat-title">
					<span class="ai-small-icon">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="25" height="25">
						<path fill="#fff" d="M352 64C352 46.3 337.7 32 320 32C302.3 32 288 46.3 288 64L288 128L192 128C139 128 96 171 96 224L96 448C96 501 139 544 192 544L448 544C501 544 544 501 544 448L544 224C544 171 501 128 448 128L352 128L352 64zM160 432C160 418.7 170.7 408 184 408L216 408C229.3 408 240 418.7 240 432C240 445.3 229.3 456 216 456L184 456C170.7 456 160 445.3 160 432zM280 432C280 418.7 290.7 408 304 408L336 408C349.3 408 360 418.7 360 432C360 445.3 349.3 456 336 456L304 456C290.7 456 280 445.3 280 432zM400 432C400 418.7 410.7 408 424 408L456 408C469.3 408 480 418.7 480 432C480 445.3 469.3 456 456 456L424 456C410.7 456 400 445.3 400 432zM224 240C250.5 240 272 261.5 272 288C272 314.5 250.5 336 224 336C197.5 336 176 314.5 176 288C176 261.5 197.5 240 224 240zM368 288C368 261.5 389.5 240 416 240C442.5 240 464 261.5 464 288C464 314.5 442.5 336 416 336C389.5 336 368 314.5 368 288zM64 288C64 270.3 49.7 256 32 256C14.3 256 0 270.3 0 288L0 384C0 401.7 14.3 416 32 416C49.7 416 64 401.7 64 384L64 288zM608 256C590.3 256 576 270.3 576 288L576 384C576 401.7 590.3 416 608 416C625.7 416 640 401.7 640 384L640 288C640 270.3 625.7 256 608 256z"/>
						</svg>
					</span>

					<div>
						<strong>WIL AI</strong>
						<small>Career Portal Assistant</small>
					</div>
				</div>

				<button type="button" class="ai-close-button" id="closeAiButton">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="19">
					<path fill="#cfdcef" d="M183.1 137.4C170.6 124.9 150.3 124.9 137.8 137.4C125.3 149.9 125.3 170.2 137.8 182.7L275.2 320L137.9 457.4C125.4 469.9 125.4 490.2 137.9 502.7C150.4 515.2 170.7 515.2 183.2 502.7L320.5 365.3L457.9 502.6C470.4 515.1 490.7 515.1 503.2 502.6C515.7 490.1 515.7 469.8 503.2 457.3L365.8 320L503.1 182.6C515.6 170.1 515.6 149.8 503.1 137.3C490.6 124.8 470.3 124.8 457.8 137.3L320.5 274.7L183.1 137.4z"/>
					</svg>
				</button>
			</div>

			
			<div class="ai-chat-body" id="aiChatBody">
				<div class="ai-message ai-bot-message">
					<strong>WIL AI</strong>
					<p>
						Hello!  How can I help you?
						You can ask me about jobs, applications,
						candidates or using the WIL-Connect portal.
					</p>
				</div>
			</div>

			<div class="ai-chat-footer">
				<input type="text" id="aiInput" placeholder="Type your message...">
				<button type="button" id="sendAiButton">Send</button>
			</div>
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
			
			
            // ChatBot Ai
			const robotBtn = document.getElementById("robotBtn");
			const aiChatBox = document.getElementById("aiChatBox");
			const closeAiButton = document.getElementById("closeAiButton");
			const sendAiButton = document.getElementById("sendAiButton");
			const aiInput =	document.getElementById("aiInput");
			const aiChatBody = document.getElementById("aiChatBody");

			/* Open WIL AI */
			robotBtn.addEventListener("click", function () {
				/* Open chat */
				aiChatBox.classList.add("show");
				/* Put cursor inside input */
				aiInput.focus();
			});


			/* Close WIL AI */
			closeAiButton.addEventListener("click", function () {
				aiChatBox.classList.remove("show");
			});


			/* Add message to chat */
			function addMessage(message, type) {
				const messageDiv = document.createElement("div");
				messageDiv.classList.add(
					"ai-message"
				);


				if (type === "user") {
					messageDiv.classList.add(
						"ai-user-message"
					);
					messageDiv.innerHTML =
						"<strong>You</strong>" +
						"<p>" + message + "</p>";
				} else {
					messageDiv.classList.add(
						"ai-bot-message"
					);
					messageDiv.innerHTML =
						"<strong>WIL AI</strong>" +
						"<p>" + message + "</p>";
				}

				aiChatBody.appendChild(messageDiv);

				/* Automatically scroll down */
				aiChatBody.scrollTop =
					aiChatBody.scrollHeight;
			}

			/* Simple WIL AI response */
			function getAiResponse(message) {
				const text = message.toLowerCase();
				
				if (
					text.includes("job") ||
					text.includes("vacancy")
				) {
					return "You can use the Jobs section to view, create and manage job opportunities.";
				}

				if (
					text.includes("application") ||
					text.includes("applications")
				) {
					return "You can use the Applications section to view applications submitted by candidates.";
				}


				if (
					text.includes("candidate") ||
					text.includes("candidates")
				) {
					return "You can view candidate information from the Applications or Candidate Profile sections.";
				}

				if (
					text.includes("message") ||
					text.includes("messages")
				) {
					return "You can view your recruiter messages by clicking the Messages option in the sidebar.";
				}

				if (
					text.includes("profile")
				) {
					return "You can update your company information from the Profile section.";
				}

				if (
					text.includes("help") ||
					text.includes("hello") ||
					text.includes("hi")
				) {
					return "Hello! I can help you with jobs, applications, candidates, messages and your WIL-Connect account.";
				}
				return "I am here to help with the WIL-Connect career portal. Try asking me about jobs, applications, candidates, messages or your profile.";

			}


			/* Send message */

			function sendAiMessage() {

				const message =
					aiInput.value.trim();


				/* Do nothing if input is empty */

				if (message === "") {

					return;

				}


				/* Display user's message */

				addMessage(
					message,
					"user"
				);


				/* Clear input */

				aiInput.value = "";


				/* Get simple response */

				const response =
					getAiResponse(message);


				/* Display AI response */

				setTimeout(function () {

					addMessage(
						response,
						"bot"
					);

				}, 400);

			}


			/* Send button */

			sendAiButton.addEventListener(
				"click",
				sendAiMessage
			);


			/* Press Enter to send */

			aiInput.addEventListener(
				"keydown",
				function (event) {

					if (event.key === "Enter") {

						sendAiMessage();

					}

				}
			);
			
			function hideAllPopups() {

				notificationBox.style.display = "none";

				profileBox.style.display = "none";

			}
			// Profile Details Popup Handler
			profileIcon.addEventListener("click", function(event) {
				event.preventDefault();
				hideAllPopups();
				profileBox.style.display = "block";
			});

			closeProfileButton.addEventListener("click", function() {
				profileBox.style.display = "none";
			});

			// Fixed Button Logic (Fixed until scrolling past 50% of page height)
			window.addEventListener("scroll", function() {
				const pageHeight = document.documentElement.scrollHeight - window.innerHeight;
				const scrollPosition = window.scrollY;

				// If scrolled less than 50% of the scrollable page height
				if (scrollPosition > 100 && scrollPosition <= pageHeight / 2) {
					robotBtn.classList.add("is-fixed");
				} else {
					robotBtn.classList.remove("is-fixed");
				}
			});
		</script>
	</body>
</html>
