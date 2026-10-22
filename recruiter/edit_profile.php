<?php
	/* =============================================================
	   edit_profile.php
	   Same form as profile_setup.php, but every field starts filled
	   in with whatever is already saved in the database, and the
	   page title/photo up top show the CURRENT saved values too.
	   If the recruiter has never saved a profile at all, we send
	   them to the setup page instead.
	   ============================================================= */
	require_once __DIR__ . '/../includes/session_init.php';
	require_once __DIR__ . '/../includes/profile_store.php';

	$profile = getRecruiterProfile();
	if ($profile === null) {
		header("Location: profile_setup.php");
		exit;
	}

	$errors = $_SESSION['profile_errors'] ?? [];
	unset($_SESSION['profile_errors']);

	// Small helper so every value below is safely escaped for HTML in one go.
	function v($value) { return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); }

	$logoSrc = $profile['logo_path'] !== '' ? v($profile['logo_path']) : 'icons/user2.svg';
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>WIL-Connect | Edit Profile</title>
		<link rel="stylesheet" href="../css/profile.css" />
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
					<a href="recruiter_profile.php" class="goBack" target="_self" title="Go to Profile">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20px" height="20px">
							<path fill="#0b1f4d" d="M73.4 297.4C60.9 309.9 60.9 330.2 73.4 342.7L233.4 502.7C245.9 515.2 266.2 515.2 278.7 502.7C291.2 490.2 291.2 469.9 278.7 457.4L173.3 352L544 352C561.7 352 576 337.7 576 320C576 302.3 561.7 288 544 288L173.3 288L278.7 182.6C291.2 170.1 291.2 149.8 278.7 137.3C266.2 124.8 245.9 124.8 233.4 137.3L73.4 297.3z"/>
						</svg>
					</a>
				</div>

				<div class="profile-shell">

					<?php if (!empty($errors)): ?>
						<div class="form-card" style="border-left:4px solid #d13a3a;">
							<?php foreach ($errors as $error): ?>
								<p style="color:#d13a3a; font-size:13.5px;"><?php echo v($error); ?></p>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="profile-cover" id="profileCover" style="background:<?php echo v($profile['cover_color']); ?>; border-color:<?php echo v($profile['cover_color']); ?>;">
						<button type="button" class="cover-edit-btn" id="coverEditBtn">Change cover</button>
						<input type="color" id="coverColorPicker" value="<?php echo v($profile['cover_color']); ?>">
					</div>

					<div class="profile-identity">
						<div class="company-logo" id="logoInitial" title="Change profile picture">
							<img src="<?php echo $logoSrc; ?>" class="profile-img" id="profileImage" alt="Profile Picture"/>
							<input type="file" id="profileImageInput" name="profileImage" accept="image/*" form="profileForm">
							<button type="button" class="camera-btn" id="cameraBtn" title="Change profile picture">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
									<path fill="currentColor" d="M213.1 128C196.7 128 181.4 136.4 172.6 150.2L151.1 184L128 184C92.7 184 64 212.7 64 248L64 488C64 523.3 92.7 552 128 552L512 552C547.3 552 576 523.3 576 488L576 248C576 212.7 547.3 184 512 184L488.9 184L467.4 150.2C458.6 136.4 443.3 128 426.9 128L213.1 128zM320 456C257.1 456 206 404.9 206 342C206 279.1 257.1 228 320 228C382.9 228 434 279.1 434 342C434 404.9 382.9 456 320 456zM320 416C360.9 416 394 382.9 394 342C394 301.1 360.9 268 320 268C279.1 268 246 301.1 246 342C246 382.9 279.1 416 320 416z"/>
								</svg>
							</button>
						</div>

						<div class="identity-text">
							<h1 id="displayCompanyName"><?php echo v($profile['company_name']); ?></h1>
							<div class="company-tag" id="displayCompanyTag"><?php echo v($profile['company_tagline']); ?></div>
						</div>
					</div>

					<div class="profile-layout">
						<nav class="section-nav">
							<a href="#companyInfo" class="active">Company information</a>
							<a href="#contactDetails">Contact details</a>
							<a href="#about">About</a>
							<a href="#social">Social presence</a>
						</nav>

						<form class="profile-form" id="profileForm" action="save_profile.php" method="POST" enctype="multipart/form-data">
							<input type="hidden" name="form_source" value="edit_profile.php">
							<input type="hidden" name="coverColor" id="coverColorField" value="<?php echo v($profile['cover_color']); ?>">

							<!-- SECTION 1: Company Info -->
							<section class="form-card" id="companyInfo">
								<h2>Company Information</h2>
								<div class="grid-2">
									<div class="input-group">
										<input type="text" id="companyNameField" name="companyName" class="track" placeholder=" " value="<?php echo v($profile['company_name']); ?>" required />
										<label for="companyNameField">Company Name</label>
										<div class="field-error">Company name is required.</div>
									</div>
									<div class="input-group">
										<input type="text" id="companyTagLineField" name="companyTagline" class="track" placeholder=" " value="<?php echo v($profile['company_tagline']); ?>" />
										<label for="companyTagLineField">Company Tagline</label>
									</div>
								</div>
								<div class="grid-2">
									<div class="input-group">
										<select id="industry" name="industry" class="track select-field" data-empty="<?php echo $profile['industry'] === '' ? 'true' : 'false'; ?>">
											<option value="" disabled hidden></option>
											<?php
											$industries = ["Information Technology", "Finance & Banking", "Engineering", "Healthcare", "Retail", "Manufacturing", "Other"];
											foreach ($industries as $option) {
												$selected = ($profile['industry'] === $option) ? 'selected' : '';
												echo "<option value=\"" . v($option) . "\" $selected>" . v($option) . "</option>";
											}
											?>
										</select>
										<label for="industry">Industry</label>
									</div>
									<div class="input-group">
										<select id="companySize" name="companySize" class="track select-field" data-empty="<?php echo $profile['company_size'] === '' ? 'true' : 'false'; ?>">
											<option value="" disabled hidden></option>
											<?php
											$sizes = ["1 – 10 employees", "11 – 50 employees", "51 – 200 employees", "201 – 500 employees", "500+ employees"];
											foreach ($sizes as $option) {
												$selected = ($profile['company_size'] === $option) ? 'selected' : '';
												echo "<option value=\"" . v($option) . "\" $selected>" . v($option) . "</option>";
											}
											?>
										</select>
										<label for="companySize">Company Size</label>
									</div>
								</div>
							</section>

							<!-- SECTION 2: Contact Details -->
							<section class="form-card" id="contactDetails">
								<h2>Contact Details</h2>
								<div class="grid-2">
									<div class="input-group">
										<input type="email" id="profileEmail" name="email" class="track" placeholder=" " value="<?php echo v($profile['email']); ?>" />
										<label for="profileEmail">Company Email</label>
									</div>
									<div class="input-group">
										<input type="tel" id="profilePhone" name="phone" class="track" placeholder=" " value="<?php echo v($profile['phone']); ?>" />
										<label for="profilePhone">Phone Number</label>
									</div>
								</div>
								<div class="grid-2">
									<div class="input-group">
										<input type="text" id="website" name="website" class="track" placeholder=" " value="<?php echo v($profile['website']); ?>" />
										<label for="website">Company Website</label>
									</div>
									<div class="input-group">
										<input type="text" id="location" name="location" class="track" placeholder=" " value="<?php echo v($profile['location']); ?>" />
										<label for="location">Location</label>
									</div>
								</div>
							</section>

							<!-- SECTION 3: About -->
							<section class="form-card" id="about">
								<h2>About Us</h2>
								<div class="input-group">
									<textarea id="description" name="description" class="track" rows="5" maxlength="600" placeholder=" "><?php echo v($profile['description']); ?></textarea>
									<label for="description" class="textarea-label">Company Description</label>
									<div class="char-count"><span id="charCount">0</span>/600</div>
								</div>
							</section>

							<!-- SECTION 4: Social Presence -->
							<section class="form-card" id="social">
								<h2>Social Presence</h2>
								<div class="grid-2">
									<div class="input-group">
										<input type="text" id="linkedin" name="linkedin" class="track" placeholder=" " value="<?php echo v($profile['linkedin']); ?>" />
										<label for="linkedin">LinkedIn</label>
									</div>
									<div class="input-group">
										<input type="text" id="xLink" name="xLink" class="track" placeholder=" " value="<?php echo v($profile['x_twitter']); ?>" />
										<label for="xLink">X / Twitter</label>
									</div>
								</div>
							</section>

							<div class="save-bar">
								<a href="recruiter_profile.php" class="btn-ghost" style="display:inline-flex; align-items:center; text-decoration:none;">Cancel</a>
								<button type="submit" class="btn-primary">Save profile</button>
							</div>
						</form>
					</div>
				</div>
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

			   
				if (window.location.search.indexOf('saved=1') !== -1) {
					var toast = document.createElement('div');
					toast.className = 'save-toast';
					toast.textContent = 'Profile saved successfully!';
					document.body.appendChild(toast);
					setTimeout(function () { toast.classList.add('show'); }, 50);
					setTimeout(function () { toast.classList.remove('show'); }, 3000);
				}

			});

		
			document.getElementById('coverColorPicker').addEventListener('input', function () {
				document.getElementById('coverColorField').value = this.value;
			});
		</script>
	</body>
</html>
