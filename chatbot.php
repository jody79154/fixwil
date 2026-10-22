<?php
/**
 * WIL Connect public rule-based help endpoint.
 * No accounts, sessions, database queries or external AI providers required.
 * Do not add personal information or account actions to this public endpoint.
 */
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['response' => 'Send a question using the WIL Connect chat window.']);
    exit;
}
if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) {
    http_response_code(415);
    echo json_encode(['response' => 'Please send a valid chat message.']);
    exit;
}
$raw = file_get_contents('php://input');
if (strlen($raw) > 3000) {
    http_response_code(413);
    echo json_encode(['response' => 'Your message is too long. Please send a shorter question.']);
    exit;
}
$body = json_decode($raw, true);
$message = is_array($body) && isset($body['message']) && is_string($body['message']) ? trim($body['message']) : '';
if (strlen($message) > 1200) {
    http_response_code(422);
    echo json_encode(['response' => 'Please shorten your question to 1,200 characters.']);
    exit;
}
if ($message === '') {
    echo json_encode(['response' => 'Hi! I can guide you through student registration, email verification, certifications and badges, finding WIL jobs and applying. What would you like to know?']);
    exit;
}
$q = strtolower(preg_replace('/\s+/u', ' ', $message));
$rules = [
    ['terms' => ['verification code expired', 'expired verification code', 'code expired', 'ten minutes', '10 minutes'], 'answer' => 'Registration verification codes expire after 10 minutes. If yours has expired, use the available resend-code option on the verification screen and check your student email.'],
    ['terms' => ['verification email', 'verification code', 'verify my email', 'verify account', 'verify my account', 'not received email', 'email not received'], 'answer' => 'After registering, WIL Connect sends a verification code to your email. Enter it on the verification page within 10 minutes. Check spam or junk if it does not arrive, and use the resend option if offered.'],
    ['terms' => ['richfield email', 'school email', 'student email', '@my.richfield.ac.za', 'personal email', 'gmail for registration', 'email address for registration'], 'answer' => 'Student registration requires your Richfield student email ending in @my.richfield.ac.za. A personal Gmail or other email address will not pass student registration validation.'],
    ['terms' => ['password requirements', 'password rules', 'special character', 'minimum password', 'strong password', 'password length'], 'answer' => 'When registering, your password must be at least 8 characters long and include at least one special character. Enter the same password in the confirmation field, and do not send it to this chatbot.'],
    ['terms' => ['passwords do not match', 'confirm password', 'password mismatch'], 'answer' => 'Enter the same password in both password fields. The system also requires at least 8 characters and one special character.'],
    ['terms' => ['email already exists', 'already registered', 'account exists', 'duplicate email'], 'answer' => 'If your email is already registered, use Log in instead of creating another account. If you cannot access it, use Forgot Password from the login page.'],
    ['terms' => ['how to register', 'register as a student', 'student registration', 'sign up as a student', 'create student account', 'how do i sign up', 'registration steps'], 'answer' => 'To register: 1. From the WIL Connect home page select Sign Up/Register. 2. Choose "I\'m a Student." 3. Enter your name, surname, Richfield email (@my.richfield.ac.za), password and matching confirmation. 4. Accept the Terms & Conditions and submit. 5. Enter the emailed verification code within 10 minutes. Never share the code or password in chat.'],
    ['terms' => ['register','registration','sign up','signup','create an account','join wil'], 'answer' => 'To join WIL Connect, open Register and choose "I\'m a Student." You need your name, surname, @my.richfield.ac.za email, a password of at least 8 characters including a special character, and agreement to the Terms & Conditions. You will then verify your email.'],
    ['terms' => ['forgot password', 'reset password', 'forgot my password', 'cannot login', 'cant login', 'log in', 'sign in', 'login'], 'answer' => 'Use Log in on the home page. If you forgot your password, choose Forgot Password on the login page. Never share passwords or verification codes in chat.'],
    ['terms' => ['badge credential url', 'credential link', 'credential id', 'badge id', 'verify my badge', 'verified badge', 'badge verification'], 'answer' => 'The student profile accepts a Credential ID and a Credential URL for a certification. Add the link from the issuer if you have one. The current profile form stores these details, but I cannot confirm that badges are automatically verified or officially endorsed.'],
    ['terms' => ['badge expiration', 'no expiration', 'badge expiry', 'expired badge', 'issue date', 'expiry date'], 'answer' => 'When adding a certification, you can enter an issue month and year, expiration month and year, or select "This certification does not expire" when applicable. Use the dates supplied by the issuing organisation.'],
    ['terms' => ['which badges', 'supported badges', 'accepted badges', 'badge providers', 'badge examples', 'what badges', 'which certificates', 'supported certificates', 'what certifications'], 'answer' => 'Your profile has a general Certifications & Badges form rather than a fixed approved list. You can enter the certificate name, issuing organisation, issue/expiry dates and optional Credential ID or URL. Examples of issuers are IBM, Cisco, AWS, Oracle, Microsoft and CompTIA, but their badges are not automatically guaranteed to meet any job\'s requirements. Check the job listing or ask the WIL coordinator.'],
    ['terms' => ['add badge', 'add badges', 'upload badge', 'add certificate', 'add certification', 'edit badge', 'add credentials', 'add my badge'], 'answer' => 'After logging in as a student, open your Profile, choose Edit Profile, then find "Certifications & Badges." Add the certification name, issuing organisation, dates and any Credential ID/URL. Save your profile. The current form collects these details rather than requiring a badge PDF upload.'],
    ['terms' => ['ibm', 'cisco', 'aws', 'amazon web services', 'oracle', 'microsoft', 'comptia'], 'answer' => 'Credentials from organisations such as IBM, Cisco, AWS, Oracle, Microsoft or CompTIA can be described in the profile\'s Certifications & Badges fields. The repository does not define a formal approved-provider whitelist or automatic badge verification. Review each job\'s specific requirements.'],
    ['terms' => ['certifications', 'certification', 'certificate', 'badges', 'badge', 'credentials'], 'answer' => 'WIL Connect lets students record Certifications & Badges in Edit Profile. The fields include certification name, issuing organisation, issue/expiry dates, Credential ID and Credential URL. This is not proof of automatic verification or job eligibility.'],
    ['terms' => ['already applied', 'duplicate application', 'applied twice', 'already_applied'], 'answer' => 'The system prevents submitting a second application for the same job. Return to your dashboard to browse other openings.'],
    ['terms' => ['file too large', 'file_too_large', '5mb', '5 mb', 'size limit'], 'answer' => 'Your CV must be a PDF no larger than 5 MB. Compress or re-export it, then apply again.'],
    ['terms' => ['invalid file', 'invalid_file_type', 'file type', 'pdf only', 'docx', 'word document'], 'answer' => 'The job application form accepts PDF CVs. Export your CV to PDF and make sure it is under 5 MB.'],
    ['terms' => ['upload failed', 'upload_failed', 'cannot upload', 'cant upload'], 'answer' => 'Re-select your PDF CV, ensure it is under 5 MB and retry. If the upload repeatedly fails, contact your WIL coordinator.'],
    ['terms' => ['application status', 'track application', 'my applications', 'pending', 'accepted', 'denied', 'rejected'], 'answer' => 'After logging in, open your student Profile to see My Applications and their stored statuses (pending, accepted or denied). This public helper cannot access your private account records.'],
    ['terms' => ['deadline', 'closing date', 'due date'], 'answer' => 'Job postings have their own deadlines. Log in, view open postings on your student dashboard and check the deadline on each listing.'],
    ['terms' => ['stipend', 'salary', 'pay', 'allowance', 'remuneration'], 'answer' => 'Stipends and salaries depend on the employer and vacancy. Review the role details or ask the recruiter rather than assuming a fixed amount.'],
    ['terms' => ['requirements', 'eligible', 'eligibility', 'qualification', 'qualify'], 'answer' => 'Each vacancy has its own requirements. Read the description and requirements on its job details page before applying.'],
    ['terms' => ['cv', 'resume', 'pdf'], 'answer' => 'To apply for a job, select a CV in PDF format, at most 5 MB, on that job\'s details page. Then click Apply.'],
    ['terms' => ['profile', 'edit my details', 'update details', 'skills'], 'answer' => 'After student login, open your Profile and choose Edit Profile. You can manage your bio, skills, project links and Certifications & Badges.'],
    ['terms' => ['find jobs', 'browse jobs', 'available jobs', 'vacancy', 'vacancies', 'internships'], 'answer' => 'Log in as a student to browse open vacancies on your dashboard. Open View Details to see responsibilities, requirements and the application form.'],
    ['terms' => ['how do i apply', 'apply for a job', 'job application', 'apply', 'application'], 'answer' => 'First register and verify your account, then log in as a student. On your dashboard select an open job, click View Details, review the requirements, upload a PDF CV (5 MB maximum) and click Apply.'],
    ['terms' => ['help', 'support', 'coordinator', 'contact'], 'answer' => 'I can help with registration, verification, badges, profiles, jobs and applications. For account-specific issues, contact your WIL coordinator; never share your password or verification code here.'],
    ['terms' => ['thanks', 'thank you'], 'answer' => 'You are welcome! I can also help you register or add certificates to your student profile.'],
    ['terms' => ['hi', 'hello', 'hey', 'good morning', 'good afternoon'], 'answer' => 'Hello! Do you want help registering as a student, verifying your email, adding badges, or applying for WIL jobs?']
];
$best = -1;
$answer = null;
foreach ($rules as $rule) {
    foreach ($rule['terms'] as $term) {
        if (preg_match('/(?<![a-z0-9])'.preg_quote($term, '/').'(?=$|[^a-z0-9])/i', $q)) {
            $score = strlen($term);
            if ($score > $best) { $best = $score; $answer = $rule['answer']; }
        }
    }
}
echo json_encode(['response' => $answer ?? 'I can help with student sign-up, school email requirements, verification codes, certificates and badges, profiles, and WIL job applications. Could you rephrase your question?'], JSON_UNESCAPED_UNICODE);
