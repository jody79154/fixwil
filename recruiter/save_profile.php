<?php

	require_once __DIR__ . '/../includes/session_init.php';
	require_once __DIR__ . '/../includes/profile_store.php';

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		header("Location: recruiter_profile.php");
		exit;
	}

	// trim() removes stray spaces, htmlspecialchars() stops someone typing HTML/
	// script tags into a text box (basic protection called "XSS").
	function cleanInput(string $value): string
	{
		return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
	}

	$companyName    = cleanInput($_POST['companyName'] ?? '');
	$companyTagline = cleanInput($_POST['companyTagline'] ?? '');
	$industry       = cleanInput($_POST['industry'] ?? '');
	$companySize    = cleanInput($_POST['companySize'] ?? '');
	$email          = cleanInput($_POST['email'] ?? '');
	$phone          = cleanInput($_POST['phone'] ?? '');
	$website        = cleanInput($_POST['website'] ?? '');
	$location       = cleanInput($_POST['location'] ?? '');
	$description    = cleanInput($_POST['description'] ?? '');
	$linkedin       = cleanInput($_POST['linkedin'] ?? '');
	$xTwitter       = cleanInput($_POST['xLink'] ?? '');
	$coverColor     = cleanInput($_POST['coverColor'] ?? '#b8d3f8');

	// --- Basic validation --------------------------------------------------
	// The browser already checked "required" fields (profile.js), but a user
	// could have JavaScript turned off, so we always check again here too.
	$errors = [];
	if ($companyName === '') $errors[] = "Company name is required.";
	if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errors[] = "That email address doesn't look valid.";
	}

	if (!empty($errors)) {
		$_SESSION['profile_errors'] = $errors;
		$back = $_POST['form_source'] ?? 'edit_profile.php';
		header("Location: $back?saved=0");
		exit;
	}

	// --- Keep the existing logo unless a new one was chosen -----------------
	$existingProfile = getRecruiterProfile();
	$logoPath = $existingProfile['logo_path'] ?? '';

	if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
		$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
		$fileType = mime_content_type($_FILES['profileImage']['tmp_name']);

		if (in_array($fileType, $allowedTypes, true)) {
			$uploadDir = __DIR__ . '/uploads/logos/';
			if (!is_dir($uploadDir)) {
				mkdir($uploadDir, 0755, true);
			}
			$extension = pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION);
			$newFileName = 'logo_' . time() . '.' . $extension;

			if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $uploadDir . $newFileName)) {
				$logoPath = 'uploads/logos/' . $newFileName;
			}
		}
	}

	// --- Save it (create or overwrite - saveRecruiterProfile handles both) --
	saveRecruiterProfile([
		'company_name'    => $companyName,
		'company_tagline' => $companyTagline,
		'industry'        => $industry,
		'company_size'    => $companySize,
		'email'           => $email,
		'phone'           => $phone,
		'website'         => $website,
		'location'        => $location,
		'description'     => $description,
		'linkedin'        => $linkedin,
		'x_twitter'       => $xTwitter,
		'logo_path'       => $logoPath,
		'cover_color'     => $coverColor,
	]);

	header("Location: recruiter_profile.php?saved=1");
	exit;
?>
