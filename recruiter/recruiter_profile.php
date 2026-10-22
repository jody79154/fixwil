<?php
	
	require_once __DIR__ . '/../includes/session_init.php';
	require_once __DIR__ . '/../includes/profile_store.php';

	$profile = getRecruiterProfile();
	if ($profile === null) {
		header("Location: profile_setup.php");
		exit;
	}

	function v($value) { return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); }
	$logoSrc = $profile['logo_path'] !== '' ? v($profile['logo_path']) : 'icons/user2.svg';

	
	$activeJobs = $activeJobs ?? [];
	$recentApplicants = $recentApplicants ?? [];
	if (!function_exists('wilTimeAgo')) {
		function wilTimeAgo($timestamp) { return date('d M Y', strtotime($timestamp)); }
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>WIL-Connect | Profile</title>
		<!--<link rel="stylesheet" href="css/profile.css" />-->
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
				position: fixed;   /* was: sticky */
				top: 0;
				left: 0;
				height: 100vh;
				z-index: 100;
			}

			.sidebar img {
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

			.head {
				display: flex;
				align-items: center;  
				margin-bottom: 30px;
			}

			.system-cont {
				min-width: 0;
				flex-shrink: 0;
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
				margin-left: 230px;
				width: calc(100% - 230px);
				box-sizing: border-box;
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
				main {
					margin-left: 0;
					width: 100%;
				}
			}

			/* ---------- View page: cover + info cards ---------- */
			.section-two {
				background: #b8d3f8; border: 1px solid #b8d3f8;
				box-shadow: 0 2px 4px rgba(220, 232, 248, 0.959); border-radius: 8px; padding: 16px 12px;
			}
			
			.profile-Container { display: flex; padding-top: 12px; padding-left: 10px; }
			
			.profile-img { border: 1px solid #fff; border-radius: 50%; width: 120px; padding: 0; object-fit: cover; height: 120px; }
			
			.details { padding: 15px 16px 0; display: flex; flex-direction: column; gap: 6px; }
			
			.name, .profile-tagline, .location { color: #0b1f4d; }
			
			.profile-tagline { font-size: 13px; }
			
			.location { display: flex; align-items: center; gap: 6px; font-size: 13px; }
			
			.location span { display: inline-flex; align-items: center; }
			
			.edit-cont { margin-left: auto; align-self: center; padding-right: 10px; }
			
			.edit-profile {
				display: flex; align-items: center; gap: 5px; padding: 10px; border: none;
				border-radius: 8px; background: #116ad8; color: #fff; font-size: 13px;
				box-shadow: 0 2px 4px rgba(220, 232, 248, 0.959); cursor: pointer;
			}
			.edit-profile:hover { background: #02397c; }
			.edit-profile span { display: inline-flex; align-items: center; }

			.section-three { margin: 14px 4px; display: flex; }
			.information-container, .contact-container {
				border: 1px solid #f4f8fb; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
				background: #fff; border-radius: 8px; width: 300px; height: auto; padding: 14px 14px;
			}
			.contact-container { margin-top: 14px; }
			.information-container h4, .contact-container h4 { margin-bottom: 6px; }
			.dets { display: flex; padding: 8px 0; gap: 6px; }
			#label { font-size: 14px; color: #b4c1d3; padding-bottom: 2px; }
			.dets p { font-size: 15px; font-weight: 300; padding: 0 8px; }
			.contact { display: flex; align-items: center; gap: 6px; font-size: 14px; padding: 6px 0; }
			.contact span { display: inline-flex; align-items: center; }

			.about-container {
				border: 1px solid #f4f8fb; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
				background: #fff; border-radius: 8px; width: 635px; height: auto;
				padding: 14px 14px; margin-left: 16px;
			}
			.about { display: flex; align-items: center; gap: 6px; padding: 6px 0; }
			.about span { display: inline-flex; align-items: center; }
			.about-container p { font-size: 14px; padding: 6px 4px; }

			.content-grid { display: grid; grid-template-columns: 1.7fr 1fr; gap: 22px; margin-bottom: 22px; }
			.panel-cont { display: flex; }
			.panel2 {
				background: #fff; border: 1px solid #e6ecf2; border-radius: 8px;
				box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); padding: 14px 14px;
				margin-left: 16px; width: 310px; margin-top: 14px;
			}
			.panel2 .card-head, .panel .card-head {
				display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;
			}
			.panel2 h3, .panel h3 { font-size: 16px; color: #0b2545; }
			.panel2 .view-all, .panel .view-all { font-size: 13px; color: #116ad8; font-weight: 600; }
			.panel {
				background: #fff; border: 1px solid #e6ecf2; border-radius: 8px;
				box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); padding: 14px 14px;
				margin-left: 16px; width: 635px; margin-top: 14px;
			}
			.job-item {
				display: flex; justify-content: space-between; align-items: center;
				padding: 14px 0; border-bottom: 1px solid #e6ecf2;
			}
			.job-item:last-child { border-bottom: none; padding-bottom: 0; }
			.job-item .job-title { font-size: 14px; font-weight: 600; color: #0b2545; }
			.job-item .job-meta { font-size: 12.5px; color: #5b6b79; margin-top: 3px; }
			.job-item .job-applicants {
				font-size: 13px; font-weight: 600; color: #116ad8; background: #eaf2ff;
				padding: 6px 12px; border-radius: 20px; white-space: nowrap;
			}
			.post-job-btn {
				margin-top: 16px; width: 100%; padding: 12px; background: #116ad8; color: #fff;
				border: none; border-radius: 10px; font-family: inherit; font-weight: 600;
				font-size: 14px; cursor: pointer; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
			}
			.post-job-btn:hover { background: #02397c; }

			/* ---------- Edit / Setup form pages ---------- */
			.profile-shell { max-width: 980px; margin: 0 auto; padding: 0 24px 64px; color: #0b1f4d; }

			.profile-cover {
				position: relative; height: 180px; border-radius: 8px; overflow: hidden;
				background: #b8d3f8; border: 1px solid #b8d3f8;
				box-shadow: 0 2px 4px rgba(220, 232, 248, 0.959); padding: 16px 12px;
			}
			.profile-cover::after {
				content: ""; position: absolute; inset: 0;
				background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.08) 0, transparent 40%),
								   radial-gradient(circle at 80% 70%, rgba(255,255,255,0.06) 0, transparent 45%);
			}
			.cover-edit-btn {
				position: absolute; top: 16px; right: 16px; background: rgba(8, 18, 38, 0.45);
				color: #fff; border: 1px solid rgba(255,255,255,0.35); padding: 8px 14px;
				border-radius: 100px; font-size: 13px; font-weight: 600; cursor: pointer;
				backdrop-filter: blur(2px); z-index: 10; transition: background 0.15s ease;
			}
			.cover-edit-btn:hover { background: rgba(8, 18, 38, 0.65); }
			#coverColorPicker { display: none; }

			.profile-identity {
				display: flex; align-items: flex-end; gap: 20px; margin-top: -54px;
				padding: 0 8px; position: relative; z-index: 2;
			}
			.company-logo {
				width: 108px; height: 108px; border-radius: 50%; display: flex;
				align-items: center; justify-content: center; flex-shrink: 0; position: relative; cursor: pointer;
			}
			.company-logo .profile-img {
				width: 108px; height: 108px; object-fit: cover; border-radius: 50%;
				margin-top: 0; background: #fff; border: 4px solid #fff; display: block;
			}
			.camera-btn {
				position: absolute; right: 2px; bottom: 2px; width: 32px; height: 32px;
				border-radius: 50%; border: 3px solid #fff; background: #0b1f4d; color: #fff;
				display: flex; align-items: center; justify-content: center; cursor: pointer;
				z-index: 5; transition: 0.2s ease;
			}
			.camera-btn:hover { background: #116ad8; transform: scale(1.05); }
			.camera-btn svg { width: 16px; height: 16px; }
			#profileImageInput { display: none; }

			.identity-text { padding-bottom: 10px; }
			.identity-text h1 { font-size: 23px; }
			.identity-text .company-tag { font-size: 13.5px; color: #55617a; margin-top: 4px; }

			.profile-layout { display: grid; grid-template-columns: 200px 1fr; gap: 32px; margin-top: 36px; }
			.section-nav { position: sticky; top: 24px; align-self: start; display: flex; flex-direction: column; gap: 4px; }
			.section-nav a { padding: 9px 12px; border-radius: 8px; font-size: 13.5px; font-weight: 600; color: #55617a; }
			.section-nav a.active, .section-nav a:hover { background: #dde8fb; color: #081226; }

			.profile-form { display: flex; flex-direction: column; gap: 20px; }
			.form-card {
				border: 1px solid #f4f8fb; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
				background: #fff; border-radius: 8px; width: 100%; padding: 18px 20px; margin-bottom: 14px;
			}
			.form-card h2 { font-size: 17px; margin-bottom: 10px; }
			.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

			.input-group { position: relative; margin: 14px 0; width: 100%; }
			.input-group input, .input-group select, .input-group textarea {
				width: 100%; padding: 10px 9px; border: 1px solid #ccc; border-radius: 6px;
				background: rgba(255, 255, 255, 0.9); color: #1a1a1a; font-size: 13px;
				font-family: 'Inter', sans-serif; outline: none; box-sizing: border-box; appearance: none;
			}
			.input-group textarea { resize: vertical; min-height: 100px; }
			.input-group input:focus, .input-group select:focus, .input-group textarea:focus { border-color: #029def; }
			.input-group label {
				position: absolute; left: 9px; top: 50%; transform: translateY(-50%);
				background: transparent; padding: 0 4px; color: #555; font-size: 13px;
				transition: 0.15s ease all; pointer-events: none;
			}
			.input-group label.textarea-label { top: 18px; }
			.input-group input:focus + label,
			.input-group input:not(:placeholder-shown) + label,
			.input-group select:focus + label,
			.input-group select:not([data-empty="true"]) + label,
			.input-group textarea:focus + label,
			.input-group textarea:not(:placeholder-shown) + label {
				top: 0; transform: translateY(-50%) scale(0.85); color: #011c4b;
				background: rgba(255, 255, 255, 0.9); border-radius: 4px;
			}
			.char-count { text-align: right; font-size: 12px; color: #8b96ab; margin-top: 4px; }

			.save-bar { position: sticky; bottom: 0; display: flex; justify-content: flex-end; gap: 12px; padding: 14px 0 6px; }
			.btn-ghost {
				padding: 10px 20px; background: #fff; border: 1px solid #ccc; border-radius: 6px;
				font-weight: 600; font-size: 13.5px; color: #55617a; cursor: pointer;
			}
			.btn-primary {
				padding: 9px 24px; border: 1px solid #116ad8; border-radius: 6px; cursor: pointer;
				font-weight: bold; color: #fff; background: #116ad8;
				box-shadow: 0 4px 7px rgba(46, 139, 255, 0.35); transition: background 0.15s ease;
			}
			
			.btn-primary:hover { background: #02397c; border-color: #02397c; }
			
			.save-toast {
				position: fixed; bottom: 24px; right: 24px; background: #0b1f4d; color: #fff;
				padding: 12px 20px; border-radius: 8px; font-size: 13.5px; font-weight: 600;
				box-shadow: 0 4px 12px rgba(0,0,0,0.25); opacity: 0; transform: translateY(10px);
				transition: opacity 0.25s ease, transform 0.25s ease; pointer-events: none; z-index: 2000;
			}
			.save-toast.show { opacity: 1; transform: translateY(0); }

			
			
			.field-error { color: #d13a3a; font-size: 12px; margin-top: 4px; display: none; }
			
			.input-group.has-error input,
			.input-group.has-error select,
			.input-group.has-error textarea { border-color: #d13a3a; }
			
			.input-group.has-error .field-error { display: block; }

			
			@media (max-width: 992px) {
				.content-grid { grid-template-columns: 1fr; }
			}
			
			@media (max-width: 768px) {
				.dashboard { flex-direction: column; }
				.sidebar {
					width: 100%;
					height: auto;
					position: relative;
				}
				main { margin-left: 0; }   /* new line */
				.search-box { width: 100%; }
				.topbar-right { box-shadow: none; padding-left: 0; }
			}
			
			@media (max-width: 760px) {
				.profile-layout { grid-template-columns: 1fr; }
				.section-nav { position: static; flex-direction: row; overflow-x: auto; padding-bottom: 6px; }
				.grid-2 { grid-template-columns: 1fr; }
				.form-card { width: 100%; }
			}

		</style>
	</head>
	<body>
		<div class="dashboard">
					<aside class="sidebar">
				<div class="head">
					<img src="../uploads/wil_logo.png" width="60" height="50" alt="WIL Logo"/>
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
					<li><a href="recruiter_profile.php" target="_self" class="active"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
						<path fill="#cfdcef" d="M320 312C386.3 312 440 258.3 440 192C440 125.7 386.3 72 320 72C253.7 72 200 125.7 200 192C200 258.3 253.7 312 320 312zM290.3 368C191.8 368 112 447.8 112 546.3C112 562.7 125.3 576 141.7 576L498.3 576C514.7 576 528 562.7 528 546.3C528 447.8 448.2 368 349.7 368L290.3 368z"/>
						</svg> Profile</a>
					</li>
					<li><a href="my_jobs.php" target="_self"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
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
					<li><a href="messages.php" target="_self"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
						<path fill="#cfdcef" d="M267.7 576.9C267.7 576.9 267.7 576.9 267.7 576.9L229.9 603.6C222.6 608.8 213 609.4 205 605.3C197 601.2 192 593 192 584L192 512L160 512C107 512 64 469 64 416L64 192C64 139 107 96 160 96L480 96C533 96 576 139 576 192L576 416C576 469 533 512 480 512L359.6 512L267.7 576.9zM332 472.8C340.1 467.1 349.8 464 359.7 464L480 464C506.5 464 528 442.5 528 416L528 192C528 165.5 506.5 144 480 144L160 144C133.5 144 112 165.5 112 192L112 416C112 442.5 133.5 464 160 464L216 464C226.4 464 235.3 470.6 238.6 479.9C239.5 482.4 240 485.1 240 488L240 537.7C272.7 514.6 303.3 493 331.9 472.8z"/>
						</svg> Messages</a>
					</li>
					<li><a href="notifications.php" target="_self"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
						<path fill="#cfdcef" d="M320 64C302.3 64 288 78.3 288 96L288 99.2C215 114 160 178.6 160 256L160 277.7C160 325.8 143.6 372.5 113.6 410.1L103.8 422.3C98.7 428.6 96 436.4 96 444.5C96 464.1 111.9 480 131.5 480L508.4 480C528 480 543.9 464.1 543.9 444.5C543.9 436.4 541.2 428.6 536.1 422.3L526.3 410.1C496.4 372.5 480 325.8 480 277.7L480 256C480 178.6 425 114 352 99.2L352 96C352 78.3 337.7 64 320 64zM258 528C265.1 555.6 290.2 576 320 576C349.8 576 374.9 555.6 382 528L258 528z"/>
						</svg> Notifications</a>
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
				    <div>
                        <h3 class="company-name">Profile</h3>
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
								<div class="notification"><p>You have received a new job application</p></div>
							</div>
							<div id="messageContent" style="display: none;">
								<div class="notification"><p>You have received a new job application</p></div>
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
									<img src="<?php echo $logoSrc; ?>" width="80" height="80" style="border-radius:50%; object-fit:cover;" alt="">
									<div class="profile-dets">
										<p class="profile-name"><?php echo v($profile['company_name']); ?></p>
										<p class="profile-email"><?php echo v($profile['email']); ?></p>
									</div>
								</div>
								<a href="recruiter_profile.php" class="see-more">View Profile</a>
							</div>
						</div>
					</div>
				</div>

				<section class="section-two" style="background:<?php echo v($profile['cover_color']); ?>; border-color:<?php echo v($profile['cover_color']); ?>;">
					<div class="profile-Container">
						<div class="img-cont">
							<img src="<?php echo $logoSrc; ?>" class="profile-img" alt="Company logo">
						</div>
						<div class="details">
							<h2 class="name"><?php echo v($profile['company_name']); ?></h2>
							<p class="profile-tagline"><?php echo v($profile['company_tagline']); ?></p>
							<p class="location">
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="18px" height="18px">
									<path fill="#0b1f4d" d="M128 252.6C128 148.4 214 64 320 64C426 64 512 148.4 512 252.6C512 371.9 391.8 514.9 341.6 569.4C329.8 582.2 310.1 582.2 298.3 569.4C248.1 514.9 127.9 371.9 127.9 252.6zM320 320C355.3 320 384 291.3 384 256C384 220.7 355.3 192 320 192C284.7 192 256 220.7 256 256C256 291.3 284.7 320 320 320z"/>
									</svg>
								</span>
								<?php echo v($profile['location'] !== '' ? $profile['location'] : 'Location not set'); ?>
							</p>
						</div>
						<div class="edit-cont">
							<a class="edit-profile" href="edit_profile.php" title="Edit Profile">
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="18px" height="18px">
									<path fill="#fff" d="M416.9 85.2L372 130.1L509.9 268L554.8 223.1C568.4 209.6 576 191.2 576 172C576 152.8 568.4 134.4 554.8 120.9L519.1 85.2C505.6 71.6 487.2 64 468 64C448.8 64 430.4 71.6 416.9 85.2zM338.1 164L122.9 379.1C112.2 389.8 104.4 403.2 100.3 417.8L64.9 545.6C62.6 553.9 64.9 562.9 71.1 569C77.3 575.1 86.2 577.5 94.5 575.2L222.3 539.7C236.9 535.6 250.2 527.9 261 517.1L476 301.9L338.1 164z"/>
									</svg>
								</span>
								Edit Profile
							</a>
						</div>
					</div>
				</section>

				<section class="section-three">
					<div class="details-section">
						<div class="information-container">
							<h4>Company Details</h4>
							<div class="dets">
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="22px" height="22px">
									<path fill="#0b1f4d" d="M192 112C183.2 112 176 119.2 176 128L176 512C176 520.8 183.2 528 192 528L272 528L272 448C272 430.3 286.3 416 304 416L336 416C353.7 416 368 430.3 368 448L368 528L448 528C456.8 528 464 520.8 464 512L464 128C464 119.2 456.8 112 448 112L192 112zM128 128C128 92.7 156.7 64 192 64L448 64C483.3 64 512 92.7 512 128L512 512C512 547.3 483.3 576 448 576L192 576C156.7 576 128 547.3 128 512L128 128zM224 176C224 167.2 231.2 160 240 160L272 160C280.8 160 288 167.2 288 176L288 208C288 216.8 280.8 224 272 224L240 224C231.2 224 224 216.8 224 208L224 176zM368 160L400 160C408.8 160 416 167.2 416 176L416 208C416 216.8 408.8 224 400 224L368 224C359.2 224 352 216.8 352 208L352 176C352 167.2 359.2 160 368 160zM224 304C224 295.2 231.2 288 240 288L272 288C280.8 288 288 295.2 288 304L288 336C288 344.8 280.8 352 272 352L240 352C231.2 352 224 344.8 224 336L224 304zM368 288L400 288C408.8 288 416 295.2 416 304L416 336C416 344.8 408.8 352 400 352L368 352C359.2 352 352 344.8 352 336L352 304C352 295.2 359.2 288 368 288z"/>
									</svg>
								</span>
								<span class="info">
									<p id="label">Company Name</p>
									<p><?php echo v($profile['company_name']); ?></p>
								</span>
							</div>
							<div class="dets">
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="22px" height="22px">
									<path fill="#0b1f4d" d="M96.5 160L96.5 309.5C96.5 326.5 103.2 342.8 115.2 354.8L307.2 546.8C332.2 571.8 372.7 571.8 397.7 546.8L547.2 397.3C572.2 372.3 572.2 331.8 547.2 306.8L355.2 114.8C343.2 102.7 327 96 310 96L160.5 96C125.2 96 96.5 124.7 96.5 160zM208.5 176C226.2 176 240.5 190.3 240.5 208C240.5 225.7 226.2 240 208.5 240C190.8 240 176.5 225.7 176.5 208C176.5 190.3 190.8 176 208.5 176z"/>
									</svg>
								</span>
								<span class="info">
									<p id="label">Industry</p>
									<p><?php echo v($profile['industry'] !== '' ? $profile['industry'] : 'Not set'); ?></p>
								</span>
							</div>
							<div class="dets">
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="22px" height="22px">
									<path fill="#0b1f4d" d="M96 192C96 130.1 146.1 80 208 80C269.9 80 320 130.1 320 192C320 253.9 269.9 304 208 304C146.1 304 96 253.9 96 192zM32 528C32 430.8 110.8 352 208 352C305.2 352 384 430.8 384 528L384 534C384 557.2 365.2 576 342 576L74 576C50.8 576 32 557.2 32 534L32 528zM464 128C517 128 560 171 560 224C560 277 517 320 464 320C411 320 368 277 368 224C368 171 411 128 464 128zM464 368C543.5 368 608 432.5 608 512L608 534.4C608 557.4 589.4 576 566.4 576L421.6 576C428.2 563.5 432 549.2 432 534L432 528C432 476.5 414.6 429.1 385.5 391.3C408.1 376.6 435.1 368 464 368z"/>
									</svg>
								</span>
								<span class="info">
									<p id="label">Company Size</p>
									<p><?php echo v($profile['company_size'] !== '' ? $profile['company_size'] : 'Not set'); ?></p>
								</span>
							</div>
						</div>

						<div class="contact-container">
							<h4>Contact Information</h4>
							<p class="contact">
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20px" height="20px">
									<path fill="#0b1f4d" d="M125.4 128C91.5 128 64 155.5 64 189.4C64 190.3 64 191.1 64.1 192L64 192L64 448C64 483.3 92.7 512 128 512L512 512C547.3 512 576 483.3 576 448L576 192L575.9 192C575.9 191.1 576 190.3 576 189.4C576 155.5 548.5 128 514.6 128L125.4 128zM528 256.3L528 448C528 456.8 520.8 464 512 464L128 464C119.2 464 112 456.8 112 448L112 256.3L266.8 373.7C298.2 397.6 341.7 397.6 373.2 373.7L528 256.3zM112 189.4C112 182 118 176 125.4 176L514.6 176C522 176 528 182 528 189.4C528 193.6 526 197.6 522.7 200.1L344.2 335.5C329.9 346.3 310.1 346.3 295.8 335.5L117.3 200.1C114 197.6 112 193.6 112 189.4z"/>
									</svg>
								</span>
								<?php echo v($profile['email'] !== '' ? $profile['email'] : 'Not set'); ?>
							</p>
							<p class="contact">
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20px" height="20px">
									<path fill="#0b1f4d" d="M224.2 89C216.3 70.1 195.7 60.1 176.1 65.4L170.6 66.9C106 84.5 50.8 147.1 66.9 223.3C104 398.3 241.7 536 416.7 573.1C493 589.3 555.5 534 573.1 469.4L574.6 463.9C580 444.2 569.9 423.6 551.1 415.8L453.8 375.3C437.3 368.4 418.2 373.2 406.8 387.1L368.2 434.3C297.9 399.4 241.3 341 208.8 269.3L253 233.3C266.9 222 271.6 202.9 264.8 186.3L224.2 89z"/>
									</svg>
								</span>
								<?php echo v($profile['phone'] !== '' ? $profile['phone'] : 'Not set'); ?>
							</p>
							<p class="contact">
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20px" height="20px">
									<path fill="#0b1f4d" d="M415.9 344L225 344C227.9 408.5 242.2 467.9 262.5 511.4C273.9 535.9 286.2 553.2 297.6 563.8C308.8 574.3 316.5 576 320.5 576C324.5 576 332.2 574.3 343.4 563.8C354.8 553.2 367.1 535.8 378.5 511.4C398.8 467.9 413.1 408.5 416 344zM224.9 296L415.8 296C413 231.5 398.7 172.1 378.4 128.6C367 104.2 354.7 86.8 343.3 76.2C332.1 65.7 324.4 64 320.4 64C316.4 64 308.7 65.7 297.5 76.2C286.1 86.8 273.8 104.2 262.4 128.6C242.1 172.1 227.8 231.5 224.9 296zM176.9 296C180.4 210.4 202.5 130.9 234.8 78.7C142.7 111.3 74.9 195.2 65.5 296L176.9 296zM65.5 344C74.9 444.8 142.7 528.7 234.8 561.3C202.5 509.1 180.4 429.6 176.9 344L65.5 344zM463.9 344C460.4 429.6 438.3 509.1 406 561.3C498.1 528.6 565.9 444.8 575.3 344L463.9 344zM575.3 296C565.9 195.2 498.1 111.3 406 78.7C438.3 130.9 460.4 210.4 463.9 296L575.3 296z"/>
									</svg>
								</span>
								<?php echo v($profile['website'] !== '' ? $profile['website'] : 'Not set'); ?>
							</p>
							<p class="contact">
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20px" height="20px">
									<path fill="#0b1f4d" d="M512 96L127.9 96C110.3 96 96 110.5 96 128.3L96 511.7C96 529.5 110.3 544 127.9 544L512 544C529.6 544 544 529.5 544 511.7L544 128.3C544 110.5 529.6 96 512 96zM231.4 480L165 480L165 266.2L231.5 266.2L231.5 480L231.4 480zM198.2 160C219.5 160 236.7 177.2 236.7 198.5C236.7 219.8 219.5 237 198.2 237C176.9 237 159.7 219.8 159.7 198.5C159.7 177.2 176.9 160 198.2 160zM480.3 480L413.9 480L413.9 376C413.9 351.2 413.4 319.3 379.4 319.3C344.8 319.3 339.5 346.3 339.5 374.2L339.5 480L273.1 480L273.1 266.2L336.8 266.2L336.8 295.4L337.7 295.4C346.6 278.6 368.3 260.9 400.6 260.9C467.8 260.9 480.3 305.2 480.3 362.8L480.3 480z"/>
									</svg>
								</span>
								<?php echo v($profile['linkedin'] !== '' ? $profile['linkedin'] : 'Not set'); ?>
							</p>
							<p class="contact">
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20px" height="20px">
									<path fill="#0b1f4d" d="M523.4 215.7C523.7 220.2 523.7 224.8 523.7 229.3C523.7 368 418.1 527.9 225.1 527.9C165.6 527.9 110.4 510.7 64 480.8C72.4 481.8 80.6 482.1 89.3 482.1C138.4 482.1 183.5 465.5 219.6 437.3C173.5 436.3 134.8 406.1 121.5 364.5C128 365.5 134.5 366.1 141.3 366.1C150.7 366.1 160.1 364.8 168.9 362.5C120.8 352.8 84.8 310.5 84.8 259.5L84.8 258.2C98.8 266 115 270.9 132.2 271.5C103.9 252.7 85.4 220.5 85.4 184.1C85.4 164.6 90.6 146.7 99.7 131.1C151.4 194.8 229 236.4 316.1 240.9C314.5 233.1 313.5 225 313.5 216.9C313.5 159.1 360.3 112 418.4 112C448.6 112 475.9 124.7 495.1 145.1C518.8 140.6 541.6 131.8 561.7 119.8C553.9 144.2 537.3 164.6 515.6 177.6C536.7 175.3 557.2 169.5 576 161.4C561.7 182.2 543.8 200.7 523.4 215.7z"/>
									</svg>
								</span>
								<?php echo v($profile['x_twitter'] !== '' ? $profile['x_twitter'] : 'Not set'); ?>
							</p>
						</div>
					</div>

					<div class="About-information">
						<div class="about-container">
							<h4 class="about">
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="22px" height="22px">
									<path fill="#0b1f4d" d="M192 112L304 112L304 200C304 239.8 336.2 272 376 272L464 272L464 512C464 520.8 456.8 528 448 528L192 528C183.2 528 176 520.8 176 512L176 128C176 119.2 183.2 112 192 112zM352 131.9L444.1 224L376 224C362.7 224 352 213.3 352 200L352 131.9zM192 64C156.7 64 128 92.7 128 128L128 512C128 547.3 156.7 576 192 576L448 576C483.3 576 512 547.3 512 512L512 250.5C512 233.5 505.3 217.2 493.3 205.2L370.7 82.7C358.7 70.7 342.5 64 325.5 64L192 64zM248 320C234.7 320 224 330.7 224 344C224 357.3 234.7 368 248 368L392 368C405.3 368 416 357.3 416 344C416 330.7 405.3 320 392 320L248 320zM248 416C234.7 416 224 426.7 224 440C224 453.3 234.7 464 248 464L392 464C405.3 464 416 453.3 416 440C416 426.7 405.3 416 392 416L248 416z"/>
									</svg>
								</span>
								About Us
							</h4>
							<p><?php echo v($profile['description'] !== '' ? $profile['description'] : 'No company description added yet.'); ?></p>
						</div>

						<div class="panel-cont">
							<div class="panel2">
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
											<h3 class="job-title"><?php echo v($job['jobTitle']); ?></h3>
											<p class="job-meta"><?php echo v($job['jobType']); ?> <span class="dash-dot">.</span> Posted <?php echo v(wilTimeAgo($job['createdAt'])); ?></p>
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
						</div>

						<section class="content-grid">
							<div class="panel">
								<div class="card-head">
									<h3>Recent Applicants</h3>
									<a href="applications.php" class="view-all">View all</a>
								</div>
								<?php if (empty($recentApplicants)): ?>
									<p style="color:#5b6b79; font-size: 13.5px; padding: 10px 0;">No applicants yet.</p>
								<?php else: ?>
									<?php foreach ($recentApplicants as $applicant): ?>
									<div class="notification">
										<span class="n-icon"><img src="icons/user.svg" width="16" alt=""></span>
										<div>
											<p><strong><?php echo v($applicant['studentName']); ?></strong> applied for <?php echo v($applicant['jobTitle']); ?></p>
											<span class="n-time"><?php echo v(wilTimeAgo($applicant['appliedAt'])); ?></span>
										</div>
									</div>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>
						</section>
					</div>
				</section>
			</main>
		</div>

		<script>
		    document.addEventListener('DOMContentLoaded', function () {
				document.querySelectorAll('.select-field').forEach(function (select) {
					select.addEventListener('change', function () {
						select.setAttribute('data-empty', select.value === '' ? 'true' : 'false');
					});
				});

			 
				var companyNameField = document.getElementById('companyNameField');
				var displayCompanyName = document.getElementById('displayCompanyName');
				if (companyNameField && displayCompanyName) {
					companyNameField.addEventListener('input', function () {
						var value = companyNameField.value.trim();
						displayCompanyName.textContent = value.length ? value : 'Your company name';
					});
				}

				var companyTagField = document.getElementById('companyTagLineField');
				var displayCompanyTag = document.getElementById('displayCompanyTag');
				if (companyTagField && displayCompanyTag) {
					companyTagField.addEventListener('input', function () {
						var value = companyTagField.value.trim();
						displayCompanyTag.textContent = value.length ? value : 'Your company tagline';
					});
				}

			 
				var description = document.getElementById('description');
				var charCount = document.getElementById('charCount');
				if (description && charCount) {
					charCount.textContent = description.value.length;
					description.addEventListener('input', function () {
						charCount.textContent = description.value.length;
					});
				}

				var coverEditBtn = document.getElementById('coverEditBtn');
				var coverColorPicker = document.getElementById('coverColorPicker');
				var profileCover = document.getElementById('profileCover');
				if (coverEditBtn && coverColorPicker && profileCover) {
					coverEditBtn.addEventListener('click', function () {
						coverColorPicker.click();
					});
					coverColorPicker.addEventListener('input', function () {
						profileCover.style.background = coverColorPicker.value;
						profileCover.style.borderColor = coverColorPicker.value;
					});
				}

			   
				var profileImage = document.getElementById('profileImage');
				var profileImageInput = document.getElementById('profileImageInput');
				var cameraBtn = document.getElementById('cameraBtn');

				if (profileImage && profileImageInput) {
					profileImage.addEventListener('click', function () {
						profileImageInput.click();
					});
					if (cameraBtn) {
						cameraBtn.addEventListener('click', function (event) {
							event.stopPropagation();
							profileImageInput.click();
						});
					}
					profileImageInput.addEventListener('change', function (event) {
						var file = event.target.files[0];
						if (!file || !file.type.startsWith('image/')) {
							alert('Please select an image file.');
							return;
						}
						var reader = new FileReader();
						reader.onload = function (e) {
							profileImage.src = e.target.result;
						};
						reader.readAsDataURL(file);
					});
				}

			  
				var trackedFields = document.querySelectorAll('.track');
				var ringFill = document.getElementById('ringFill');
				var ringPercent = document.getElementById('ringPercent');
				var ringSub = document.getElementById('ringSub');
				var CIRCUMFERENCE = 150.8;

				function updateStrength() {
					if (!ringFill || !ringPercent || trackedFields.length === 0) return;
					var filled = 0;
					trackedFields.forEach(function (f) {
						if (f.value.trim().length > 0) filled++;
					});
					var percent = Math.round((filled / trackedFields.length) * 100);
					ringFill.style.strokeDashoffset = CIRCUMFERENCE - (CIRCUMFERENCE * percent / 100);
					ringPercent.textContent = percent + '%';
					if (ringSub) {
						if (percent === 0) ringSub.textContent = 'Just getting started';
						else if (percent < 50) ringSub.textContent = 'Keep going';
						else if (percent < 100) ringSub.textContent = 'Almost there';
						else ringSub.textContent = 'Profile complete';
					}
				}
				if (trackedFields.length > 0) {
					trackedFields.forEach(function (field) {
						field.addEventListener('input', updateStrength);
						field.addEventListener('change', updateStrength);
					});
					updateStrength();
				}

				
				var profileForm = document.getElementById('profileForm');
				if (profileForm) {
					profileForm.addEventListener('submit', function (e) {
						var requiredFields = profileForm.querySelectorAll('[required]');
						var firstBadField = null;

						requiredFields.forEach(function (field) {
							var group = field.closest('.input-group');
							if (!group) return;
							if (field.value.trim() === '') {
								group.classList.add('has-error');
								if (!firstBadField) firstBadField = field;
							} else {
								group.classList.remove('has-error');
							}
						});

						if (firstBadField) {
							e.preventDefault();
							firstBadField.focus();
						}
					   
					});
				}

				
				var notificationBell = document.getElementById('notificationBell');
				var messagesIcon = document.getElementById('messagesIcon');
				var notificationBox = document.getElementById('notificationBox');
				var popupTitle = document.getElementById('popupTitle');
				var closeButton = document.getElementById('closeButton');
				var popupSeeMore = document.getElementById('popupSeeMore');
				var notificationContent = document.getElementById('notificationContent');
				var messageContent = document.getElementById('messageContent');
				var profileIcon = document.getElementById('profileIcon');
				var profileBox = document.getElementById('profileBox');
				var closeProfileButton = document.getElementById('closeProfileButton');

				function hideAllPopups() {
					if (notificationBox) notificationBox.style.display = 'none';
					if (profileBox) profileBox.style.display = 'none';
				}

				if (notificationBell && notificationBox) {
					notificationBell.addEventListener('click', function (e) {
						e.preventDefault();
						hideAllPopups();
						if (popupTitle) popupTitle.textContent = 'Notifications';
						if (notificationContent) notificationContent.style.display = 'block';
						if (messageContent) messageContent.style.display = 'none';
						if (popupSeeMore) popupSeeMore.href = 'notifications.php';
						notificationBox.style.display = 'block';
					});
				}

				if (messagesIcon && notificationBox) {
					messagesIcon.addEventListener('click', function (e) {
						e.preventDefault();
						hideAllPopups();
						if (popupTitle) popupTitle.textContent = 'Messages';
						if (notificationContent) notificationContent.style.display = 'none';
						if (messageContent) messageContent.style.display = 'block';
						if (popupSeeMore) popupSeeMore.href = 'messages.php';
						notificationBox.style.display = 'block';
					});
				}

				if (closeButton && notificationBox) {
					closeButton.addEventListener('click', function () {
						notificationBox.style.display = 'none';
					});
				}

				if (profileIcon && profileBox) {
					profileIcon.addEventListener('click', function (event) {
						event.preventDefault();
						hideAllPopups();
						profileBox.style.display = 'block';
					});
				}

				if (closeProfileButton && profileBox) {
					closeProfileButton.addEventListener('click', function () {
						profileBox.style.display = 'none';
					});
				}

				/* ---------------------------------------------------------
				   9. "Saved!" toast - shows automatically if the page URL
					  contains ?saved=1 (save_profile.php redirects back with
					  this after a successful save - see that file).
				   --------------------------------------------------------- */
				if (window.location.search.indexOf('saved=1') !== -1) {
					var toast = document.createElement('div');
					toast.className = 'save-toast';
					toast.textContent = 'Profile saved successfully!';
					document.body.appendChild(toast);
					setTimeout(function () { toast.classList.add('show'); }, 50);
					setTimeout(function () { toast.classList.remove('show'); }, 3000);
				}

			});

		</script>
	</body>
</html>
