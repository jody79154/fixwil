<?php

    require_once "../includes/auth.inc.php";
    require_once "../includes/session_timeout.inc.php";
    require_role('recruiter');
	
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>WIL-Connect | Post Job</title>
		<!--<link rel="stylesheet" href="style.css"/>-->
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

			.panel-head { padding: 0 0 10px 0; }

			.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
			.form-grid .full { grid-column: 1 / -1; }

			.field label {
				display: block;
				font-size: 13px;
				font-weight: 700;
				color: #0b1f4d;
				margin-bottom: 7px;
			}

			.field .hint { font-size: 11.5px; color: #8792ad; font-weight: 400; margin-left: 4px; }

			.field input[type="text"],
			.field input[type="number"],
			.field input[type="date"],
			.field input[type="email"],
			.field select,
			.field textarea {
				width: 100%;
				padding: 12px 14px;
				border-radius: 10px;
				border: 1.5px solid #e2e8f5;
				font-size: 13.5px;
				font-family: inherit;
				color: #0b1f4d;
				background: #fff;
				transition: border-color .15s ease;
			}

			.field input:focus, .field select:focus, .field textarea:focus {
				outline: none;
				border-color: #116ad8;
			}

			.field textarea { resize: vertical; min-height: 110px; }
			.field { margin-bottom: 10px; }

			.tag-input-box {
				display: flex;
				flex-wrap: wrap;
				align-items: center;
				gap: 8px;
				border: 1.5px solid #e2e8f5;
				border-radius: 10px;
				padding: 8px 10px;
				min-height: 48px;
			}

			.tag-chip {
				background: #eef3fb;
				color: #116ad8;
				border: 1px solid #cbdcf7;
				border-radius: 16px;
				padding: 4px 10px;
				font-size: 12px;
				font-weight: 600;
				display: flex;
				align-items: center;
				gap: 6px;
			}

			.tag-chip span {
				cursor: pointer;
				color: #e03131;
				font-weight: bold;
			}

			.tag-input-box input {
				border: none !important;
				outline: none !important;
				flex: 1;
				min-width: 150px;
				padding: 4px !important;
			}

			.pill-select { display: flex; gap: 10px; flex-wrap: wrap; }
			
			.pill-select label {
				border: 1.5px solid #e2e8f5;
				border-radius: 10px;
				padding: 10px 16px;
				font-size: 13px;
				font-weight: 600;
				color: #48557a;
				cursor: pointer;
			}

			.pill-select input { display: none; }
			.pill-select label:has(input:checked) { border-color: #116ad8; background: #eef3fb; color: #116ad8; }

			.form-footer {
				display: flex;
				justify-content: flex-end;
				gap: 12px;
				padding-top: 20px;
				border-top: 1px solid #eef3fb;
				margin-top: 6px;
			}

			.form-footer button {
				padding: 10px 16px;
				border-radius: 6px;
				cursor: pointer;
				color: #fff;
				font-weight: 600;
				border: none;
			}

			.btn-btn-outline { background: #f5a623; }
			.btn-btn-outline:hover { background: #c27f14; }

			.btn-btn-cancel { background: #e03131; }
			.btn-btn-cancel:hover { background: #c91e1e; }

			.btn-btn-primary { background: #116ad8; }
			.btn-btn-primary:hover { background: #02397c; }

			.preview-card {
				background: linear-gradient(180deg, #0b1f4d 0%, #14295e 100%);
				color: #fff;
				border-radius: 16px;
				padding: 26px;
				position: sticky;
				top: 26px;
			}

			.preview-card .eyebrow { color: #9fc4ff; font-size: 12px; font-weight: bold; text-transform: uppercase; }
			.preview-card h3 { color: #fff; font-size: 19px; margin: 6px 0 4px; }
			.preview-card .company-line { font-size: 13px; color: #c7d3ee; margin-bottom: 16px; }

			.preview-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
			.preview-tags span {
				background: rgba(255,255,255,.1); 
				padding: 5px 12px; 
				border-radius: 20px;
				font-size: 11.5px; 
				font-weight: 600;
			}

			.preview-card .desc { 
				font-size: 13px; 
				color: #c7d3ee; 
				line-height: 1.6; 
				margin-bottom: 18px; 
			}

			.preview-card .divider { 
				border-top: 1px solid #f5a623;
				margin: 16px 0; 
			}

			.preview-stub { 
				font-size: 12px; 
				color: #8ea3d1; 
				text-align: center;
				display: block;
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
                    <li><a href="dashboard.php" target="_self">
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
                    <li><a href="create_job.php" target="_self"  class="active"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
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
                    <li><a href="messages.php"  target="_self"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"  class="icon">
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
                    <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="icon">
					<path fill="#cfdcef" d="M569 337C578.4 327.6 578.4 312.4 569 303.1L425 159C418.1 152.1 407.8 150.1 398.8 153.8C389.8 157.5 384 166.3 384 176L384 256L272 256C245.5 256 224 277.5 224 304L224 336C224 362.5 245.5 384 272 384L384 384L384 464C384 473.7 389.8 482.5 398.8 486.2C407.8 489.9 418.1 487.9 425 481L569 337zM224 160C241.7 160 256 145.7 256 128C256 110.3 241.7 96 224 96L160 96C107 96 64 139 64 192L64 448C64 501 107 544 160 544L224 544C241.7 544 256 529.7 256 512C256 494.3 241.7 480 224 480L160 480C142.3 480 128 465.7 128 448L128 192C128 174.3 142.3 160 160 160L224 160z"/>
					</svg> Log Out</a>
                </div>
			</aside>
			<main>
			    <div class="header-part">
				    <div>
                        <h3 class="company-name">Post a Job</h3>
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
				
				<section class="job-layout" style="display:grid; grid-template-columns: 1.6fr 1fr; gap:24px; align-items:start;">
					<form class="panel" id="postJobForm">
						<div class="panel-head"><h2>Job Details</h2></div>

						<div class="form-grid">
							<div class="field full">
								<label for="f-title">Job Title</label>
								<input type="text" id="f-title" placeholder="e.g. Front-End Development Intern" required />
							</div>

							<div class="field">
								<label for="f-dept">Department</label>
								<select id="f-dept">
									<option>Software Development</option>
									<option>Data & Analytics</option>
									<option>Cybersecurity</option>
									<option>Cloud & Infrastructure</option>
									<option>Marketing</option>
									<option>Finance</option>
								</select>
							</div>

							<div class="field">
								<label for="f-location">Location</label>
								<input type="text" id="f-location" placeholder="e.g. Durban, KZN" required />
							</div>

							<div class="field full">
								<label>Work Arrangement</label>
								<div class="pill-select">
									<label><input type="radio" name="arrangement" value="On-site" checked /><span>On-site</span></label>
									<label><input type="radio" name="arrangement" value="Remote" /><span>Remote</span></label>
									<label><input type="radio" name="arrangement" value="Hybrid" /><span>Hybrid</span></label>
								</div>
							</div>

							<div class="field">
								<label for="f-type">Job Type</label>
								<select id="f-type">
									<option>Internship</option>
									<option>Learnership</option>
									<option>Graduate Programme</option>
									<option>Part-Time</option>
								</select>
							</div>

							<div class="field">
								<label for="f-duration">Duration</label>
								<input type="text" id="f-duration" placeholder="e.g. 6 months" />
							</div>

							<div class="field">
								<label for="f-salary">Stipend / Salary <span class="hint">(optional)</span></label>
								<input type="text" id="f-salary" placeholder="e.g. R6,500 / month" />
							</div>

							<div class="field">
								<label for="f-positions">Number of Positions</label>
								<input type="number" id="f-positions" value="1" min="1" />
							</div>

							<div class="field">
								<label for="f-deadline">Application Deadline</label>
								<input type="date" id="f-deadline" />
							</div>

							<div class="field full">
								<label>Required Skills <span class="hint">(Type & Press Enter)</span></label>
								<div class="tag-input-box" id="skillsTagContainer">
									<input type="text" id="skillInput" placeholder="Type a skill and press Enter..." />
								</div>
							</div>

							<div class="field full">
								<label for="f-desc">Job Description</label>
								<textarea id="f-desc" placeholder="Describe day-to-day responsibilities..."></textarea>
							</div>

							<div class="field full">
								<label for="f-reqs">Requirements</label>
								<textarea id="f-reqs" placeholder="Qualifications, year of study, minimum requirements, etc."></textarea>
							</div>
						</div>

						<div class="form-footer">
							<button type="submit" class="btn-btn-primary">Publish Job</button>
							<button type="button" id="saveDraftBtn" class="btn-btn-outline">Save as Draft</button>
							<button type="button" id="cancelBtn" class="btn-btn-cancel">Cancel</button>
						</div>
					</form>
					
					<!-- Live Job Preview Card -->
					<div class="preview-card">
						<span class="eyebrow">Live Job Preview</span>
						<h3 id="pv-title">Job Title</h3>
						<div class="company-line" id="pv-meta">Department &middot; Location &middot; Arrangement</div>

						<div class="preview-tags">
							<span id="pv-type">Type</span>
							<span id="pv-duration">Duration</span>
							<span id="pv-salary">Stipend/Salary</span>
						</div>

						<p class="desc" id="pv-desc">Job Description...</p>

						<div id="pv-skills" class="preview-tags"></div>

						<div class="divider"></div>
						<span class="preview-stub">&copy; 2026 WIL-Connect. All rights reserved.</span>
					</div>
				</section>
			</main>
		</div>
		
		<script>
			// --- Popups Handlers ---
			const notificationBell = document.getElementById("notificationBell");
			const messagesIcon = document.getElementById("messagesIcon");
			const profileIcon = document.getElementById("profileIcon");
			const notificationBox = document.getElementById("notificationBox");
			const profileBox = document.getElementById("profileBox");
			const closeButton = document.getElementById("closeButton");
			const closeProfileButton = document.getElementById("closeProfileButton");
			const popupTitle = document.getElementById("popupTitle");
			const notificationContent = document.getElementById("notificationContent");
			const messageContent = document.getElementById("messageContent");

			function hideAllPopups() {
				if(notificationBox) notificationBox.style.display = "none";
				if(profileBox) profileBox.style.display = "none";
			}

			if(notificationBell) {
				notificationBell.addEventListener("click", function(e) {
					e.preventDefault();
					hideAllPopups();
					popupTitle.textContent = "Notifications";
					notificationContent.style.display = "block";
					messageContent.style.display = "none";
					notificationBox.style.display = "block";
				});
			}

			if(messagesIcon) {
				messagesIcon.addEventListener("click", function(e) {
					e.preventDefault();
					hideAllPopups();
					popupTitle.textContent = "Messages";
					notificationContent.style.display = "none";
					messageContent.style.display = "block";
					notificationBox.style.display = "block";
				});
			}

			if(profileIcon) {
				profileIcon.addEventListener("click", function(e) {
					e.preventDefault();
					hideAllPopups();
					profileBox.style.display = "block";
				});
			}

			if(closeButton) closeButton.addEventListener("click", hideAllPopups);
			if(closeProfileButton) closeProfileButton.addEventListener("click", hideAllPopups);

			// --- Interactive Form & Live Preview JS ---
			const form = document.getElementById('postJobForm');
			const titleInput = document.getElementById('f-title');
			const locationInput = document.getElementById('f-location');
			const typeSelect = document.getElementById('f-type');
			const durationInput = document.getElementById('f-duration');
			const salaryInput = document.getElementById('f-salary');
			const descInput = document.getElementById('f-desc');

			const pvTitle = document.getElementById('pv-title');
			const pvMeta = document.getElementById('pv-meta');
			const pvType = document.getElementById('pv-type');
			const pvDuration = document.getElementById('pv-duration');
			const pvSalary = document.getElementById('pv-salary');
			const pvDesc = document.getElementById('pv-desc');
			const pvSkills = document.getElementById('pv-skills');

			let skillTags = [];

			function getArrangementValue() {
				const selected = document.querySelector('input[name="arrangement"]:checked');
				return selected ? selected.value : 'On-site';
			}

			function updatePreview() {
				pvTitle.textContent = titleInput.value.trim() || "Front-End Development Intern";
				
				const loc = locationInput.value.trim() || "Durban, KZN";
				const arrange = getArrangementValue();
				pvMeta.innerHTML = "WIL-Connect Company &middot; " + loc + " &middot; " + arrange;

				pvType.textContent = typeSelect.value || "Internship";
				pvDuration.textContent = durationInput.value.trim() || "6 months";
				pvSalary.textContent = salaryInput.value.trim() || "Unpaid / Undisclosed";
				
				pvDesc.textContent = descInput.value.trim() || "Job description preview will display here in real time...";

				// Update live skill preview badges
				pvSkills.innerHTML = "";
				for (let i = 0; i < skillTags.length; i++) {
					const badge = document.createElement("span");
					badge.textContent = skillTags[i];
					pvSkills.appendChild(badge);
				}
			}

			// Attach Real-Time Inputs
			titleInput.addEventListener('input', updatePreview);
			locationInput.addEventListener('input', updatePreview);
			typeSelect.addEventListener('change', updatePreview);
			durationInput.addEventListener('input', updatePreview);
			salaryInput.addEventListener('input', updatePreview);
			descInput.addEventListener('input', updatePreview);
			
			const radioBtns = document.querySelectorAll('input[name="arrangement"]');
			for (let i = 0; i < radioBtns.length; i++) {
				radioBtns[i].addEventListener('change', updatePreview);
			}

			// --- Interactive Skill Tags Handler ---
			const skillInput = document.getElementById('skillInput');
			const skillsTagContainer = document.getElementById('skillsTagContainer');

			skillInput.addEventListener('keypress', function(e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					const value = skillInput.value.trim();
					if (value !== "" && !skillTags.includes(value)) {
						skillTags.push(value);
						renderTags();
						skillInput.value = "";
						updatePreview();
					}
				}
			});

			function renderTags() {
				const existingChips = skillsTagContainer.querySelectorAll('.tag-chip');
				for(let i=0; i<existingChips.length; i++) {
					existingChips[i].remove();
				}

				for (let i = 0; i < skillTags.length; i++) {
					const chip = document.createElement('div');
					chip.className = 'tag-chip';
					chip.innerHTML = skillTags[i] + ' <span data-index="' + i + '">&times;</span>';
					skillsTagContainer.insertBefore(chip, skillInput);
				}
			}

			skillsTagContainer.addEventListener('click', function(e) {
				if (e.target.tagName === 'SPAN' && e.target.getAttribute('data-index') !== null) {
					const idx = e.target.getAttribute('data-index');
					skillTags.splice(idx, 1);
					renderTags();
					updatePreview();
				}
			});

			// --- Post Job & LocalStorage Handling ---
			form.addEventListener('submit', function(e) {
				e.preventDefault();

				const newJob = {
					id: Date.now(),
					title: titleInput.value.trim(),
					department: document.getElementById('f-dept').value,
					location: locationInput.value.trim(),
					arrangement: getArrangementValue(),
					type: typeSelect.value,
					duration: durationInput.value.trim(),
					salary: salaryInput.value.trim(),
					positions: document.getElementById('f-positions').value,
					deadline: document.getElementById('f-deadline').value,
					description: descInput.value.trim(),
					requirements: document.getElementById('f-reqs').value.trim(),
					skills: skillTags,
					postedDate: new Date().toLocaleDateString(),
					status: 'active',
					applicants: 0,
					views: 0
				};

				// Save into LocalStorage array
				let jobs = JSON.parse(localStorage.getItem('postedJobs')) || [];
				jobs.unshift(newJob); // Push newest to top
				localStorage.setItem('postedJobs', JSON.stringify(jobs));

				alert('Job successfully published!');
				window.location.href = 'jobs.php'; // Navigate straight to My Jobs page
			});

			// Draft Button Handler
			document.getElementById('saveDraftBtn').addEventListener('click', function() {
				const draftJob = {
					id: Date.now(),
					title: titleInput.value.trim() || 'Untitled Job',
					department: document.getElementById('f-dept').value,
					location: locationInput.value.trim(),
					arrangement: getArrangementValue(),
					type: typeSelect.value,
					duration: durationInput.value.trim(),
					salary: salaryInput.value.trim(),
					positions: document.getElementById('f-positions').value,
					deadline: document.getElementById('f-deadline').value,
					description: descInput.value.trim(),
					requirements: document.getElementById('f-reqs').value.trim(),
					skills: skillTags,
					postedDate: new Date().toLocaleDateString(),
					status: 'draft',
					applicants: 0,
					views: 0
				};

				let jobs = JSON.parse(localStorage.getItem('postedJobs')) || [];
				jobs.unshift(draftJob);
				localStorage.setItem('postedJobs', JSON.stringify(jobs));

				alert('Job saved as a draft!');
				window.location.href = 'jobs.php';
			});

			// Cancel Button Handler
			document.getElementById('cancelBtn').addEventListener('click', function() {
				if (confirm('Are you sure you want to cancel? Any unsaved entries will be lost.')) {
					form.reset();
					skillTags = [];
					renderTags();
					updatePreview();
				}
			});
		</script>
	</body>
</html>