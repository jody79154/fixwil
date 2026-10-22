<?php
	
	require_once __DIR__ . '/../includes/session_init.php';
	require_once __DIR__ . '/includes/profile_store.php';

	$profile = getRecruiterProfile();
	if ($profile !== null) {
		header("Location: edit_profile.php");
		exit;
	}

	$errors = $_SESSION['profile_errors'] ?? [];
	unset($_SESSION['profile_errors']);
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>WIL-Connect | Set Up Your Profile</title>
		<link rel="stylesheet" href="css/profile.css" />
	</head>
	<body>
	    <?php include 'navbar.php'; ?>
		<div class="dashboard">
			<main>
				<div class="profile-shell">

					<?php if (!empty($errors)): ?>
						<div class="form-card" style="border-left:4px solid #d13a3a;">
							<?php foreach ($errors as $error): ?>
								<p style="color:#d13a3a; font-size:13.5px;"><?php echo htmlspecialchars($error); ?></p>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="profile-cover" id="profileCover">
						<button type="button" class="cover-edit-btn" id="coverEditBtn">Change cover</button>
						<input type="color" id="coverColorPicker" value="#b8d3f8">
					</div>

					<div class="profile-identity">
						<div class="company-logo" id="logoInitial" title="Change profile picture">
							<img src="icons/user2.svg" class="profile-img" id="profileImage" alt="Profile Picture"/>
							<!-- form="profileForm" links this input to the <form> further down the
								 page even though it visually sits above it -->
							<input type="file" id="profileImageInput" name="profileImage" accept="image/*" form="profileForm">
							<button type="button" class="camera-btn" id="cameraBtn" title="Change profile picture">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
									<path fill="currentColor" d="M213.1 128C196.7 128 181.4 136.4 172.6 150.2L151.1 184L128 184C92.7 184 64 212.7 64 248L64 488C64 523.3 92.7 552 128 552L512 552C547.3 552 576 523.3 576 488L576 248C576 212.7 547.3 184 512 184L488.9 184L467.4 150.2C458.6 136.4 443.3 128 426.9 128L213.1 128zM320 456C257.1 456 206 404.9 206 342C206 279.1 257.1 228 320 228C382.9 228 434 279.1 434 342C434 404.9 382.9 456 320 456zM320 416C360.9 416 394 382.9 394 342C394 301.1 360.9 268 320 268C279.1 268 246 301.1 246 342C246 382.9 279.1 416 320 416z"/>
								</svg>
							</button>
						</div>

						<div class="identity-text">
							<h1 id="displayCompanyName">Your company name</h1>
							<div class="company-tag" id="displayCompanyTag">Your company tagline</div>
						</div>

						<!-- Profile strength ring - fills in as fields are completed -->
						<div style="margin-left:auto; display:flex; align-items:center; gap:10px;">
							<svg width="56" height="56" viewBox="0 0 56 56">
								<circle cx="28" cy="28" r="24" fill="none" stroke="#dde8fb" stroke-width="6"/>
								<circle id="ringFill" cx="28" cy="28" r="24" fill="none" stroke="#f5a623"
										stroke-width="6" stroke-dasharray="150.8" stroke-dashoffset="150.8"
										stroke-linecap="round" transform="rotate(-90 28 28)"/>
							</svg>
							<div>
								<div id="ringPercent" style="font-weight:700; font-size:14px;">0%</div>
								<div id="ringSub" style="font-size:11.5px; color:#5b6b79;">Set Up your Profile</div>
							</div>
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
							<input type="hidden" name="form_source" value="profile_setup.php">
							<input type="hidden" name="coverColor" id="coverColorField" value="#b8d3f8">

							<!-- SECTION 1: Company Info -->
							<section class="form-card" id="companyInfo">
								<h2>Company Information</h2>
								<div class="grid-2">
									<div class="input-group">
										<input type="text" id="companyNameField" name="companyName" class="track" placeholder=" " required />
										<label for="companyNameField">Company Name</label>
										<div class="field-error">Company name is required.</div>
									</div>
									<div class="input-group">
										<input type="text" id="companyTagLineField" name="companyTagline" class="track" placeholder=" " />
										<label for="companyTagLineField">Company Tagline</label>
									</div>
								</div>
								<div class="grid-2">
									<div class="input-group">
										<select id="industry" name="industry" class="track select-field" data-empty="true">
											<option value="" selected disabled hidden></option>
											<option value="Information Technology">Information Technology</option>
											<option value="Finance & Banking">Finance &amp; Banking</option>
											<option value="Engineering">Engineering</option>
											<option value="Healthcare">Healthcare</option>
											<option value="Retail">Retail</option>
											<option value="Manufacturing">Manufacturing</option>
											<option value="Other">Other</option>
										</select>
										<label for="industry">Industry</label>
									</div>
									<div class="input-group">
										<select id="companySize" name="companySize" class="track select-field" data-empty="true">
											<option value="" selected disabled hidden></option>
											<option value="1 – 10 employees">1 – 10 employees</option>
											<option value="11 – 50 employees">11 – 50 employees</option>
											<option value="51 – 200 employees">51 – 200 employees</option>
											<option value="201 – 500 employees">201 – 500 employees</option>
											<option value="500+ employees">500+ employees</option>
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
										<input type="email" id="profileEmail" name="email" class="track" placeholder=" " />
										<label for="profileEmail">Company Email</label>
									</div>
									<div class="input-group">
										<input type="tel" id="profilePhone" name="phone" class="track" placeholder=" " />
										<label for="profilePhone">Phone Number</label>
									</div>
								</div>
								<div class="grid-2">
									<div class="input-group">
										<input type="text" id="website" name="website" class="track" placeholder=" " />
										<label for="website">Company Website</label>
									</div>
									<div class="input-group">
										<input type="text" id="location" name="location" class="track" placeholder=" " />
										<label for="location">Location</label>
									</div>
								</div>
							</section>

							<!-- SECTION 3: About -->
							<section class="form-card" id="about">
								<h2>About Us</h2>
								<div class="input-group">
									<textarea id="description" name="description" class="track" rows="5" maxlength="600" placeholder=" "></textarea>
									<label for="description" class="textarea-label">Company Description</label>
									<div class="char-count"><span id="charCount">0</span>/600</div>
								</div>
							</section>

							<!-- SECTION 4: Social Presence -->
							<section class="form-card" id="social">
								<h2>Social Presence</h2>
								<div class="grid-2">
									<div class="input-group">
										<input type="text" id="linkedin" name="linkedin" class="track" placeholder=" " />
										<label for="linkedin">LinkedIn</label>
									</div>
									<div class="input-group">
										<input type="text" id="xLink" name="xLink" class="track" placeholder=" " />
										<label for="xLink">X / Twitter</label>
									</div>
								</div>
							</section>

							<div class="save-bar">
								<button type="submit" class="btn-primary">Save profile</button>
							</div>
						</form>
					</div>
				</div>
			</main>
		</div>
		<?php include 'footer.php'; ?>
		<script src="js/profile.js"></script>
		<script>
			// Keep the hidden "coverColor" field in sync with the colour picker,
			// so the chosen colour actually gets sent to save_profile.php.
			document.getElementById('coverColorPicker').addEventListener('input', function () {
				document.getElementById('coverColorField').value = this.value;
			});
			// The visible file input in profile.js is #profileImageInput - here we
			// reuse the same id, so clicking the avatar/camera opens THIS picker,
			// and its chosen file is what actually gets submitted with the form.
		</script>
	</body>
</html>
