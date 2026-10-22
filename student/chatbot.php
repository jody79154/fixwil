<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.inc.php';
require_role('student');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
 http_response_code(405); header('Allow: POST');
 echo json_encode(['response'=>'Use the chat window to send a question.']); exit;
}
if (!isset($_SERVER['CONTENT_TYPE']) || stripos($_SERVER['CONTENT_TYPE'],'application/json') !== 0) {
 http_response_code(415); echo json_encode(['response'=>'Please send a valid chat message.']); exit;
}
$body = json_decode(file_get_contents('php://input'), true);
$message = is_array($body) && isset($body['message']) && is_string($body['message']) ? trim($body['message']) : '';
if ($message === '') {
 echo json_encode(['response'=>'Hi! I am the WIL Connect Helper. Ask about available jobs, applying, CV uploads, deadlines or application problems.']); exit;
}
if (strlen($message) > 1200) {
 http_response_code(422); echo json_encode(['response'=>'Please shorten your question to 1,200 characters.']); exit;
}
$q = strtolower(preg_replace('/\s+/u', ' ', $message));
$rules = [
 ['terms'=>['already applied','duplicate application','applied twice','already_applied'], 'answer'=>'The system prevents duplicate applications for the same job. Return to your dashboard and choose a different open position.'],
 ['terms'=>['file too large','file_too_large','5mb','5 mb','size limit','large pdf'], 'answer'=>'CV files must be no larger than 5 MB. Compress or re-export your PDF, then try again.'],
 ['terms'=>['invalid file','invalid_file_type','file type','pdf only','word document','docx'], 'answer'=>'The application form accepts a PDF CV. Save or export your CV as a PDF and upload that file.'],
 ['terms'=>['upload failed','upload_failed','upload error','cannot upload','cant upload'], 'answer'=>'Try choosing your PDF again and submitting. Check that it is under 5 MB. If the upload still fails, report the error to your WIL coordinator.'],
 ['terms'=>['database error','database_error','something went wrong','server error'], 'answer'=>'An application could not be saved. Try again later, and report repeated errors to your WIL coordinator. Do not assume the application went through unless you see the success message.'],
 ['terms'=>['submitted successfully','application_submitted','did it submit','did my application go through','successful application'], 'answer'=>'After a successful submission, your dashboard displays “Application submitted successfully.” If you did not see that confirmation, check before attempting another submission.'],
 ['terms'=>['application status','track application','track my application','pending','shortlisted','under review','accepted','rejected','application progress'], 'answer'=>'Application decisions depend on the recruiter. The chatbot cannot view your private status. Check your available application information or contact your WIL coordinator for an update.'],
 ['terms'=>['forgot password','reset password','forgot my password','cannot login','cant login','log in','sign in','login'], 'answer'=>'Use the Forgot Password link on the WIL Connect login page to begin password recovery. Never share your password in chat.'],
 ['terms'=>['deadline','closing date','due date','when does','last day','application date'], 'answer'=>'Each job posting shows its own deadline. Check the date on the dashboard or job details page before applying; there is no single confirmed deadline for all jobs.'],
 ['terms'=>['interview','shortlist','selection process','recruiter contact'], 'answer'=>'Recruiters review job applications and may contact shortlisted candidates. Keep your contact details current and check your registered email. An interview is not guaranteed.'],
 ['terms'=>['stipend','salary','salary amount','payment','paid internship','remuneration','allowance','how much money'], 'answer'=>'Any stipend or pay depends on the individual job and employer. Check the job details or ask the recruiter; the chatbot cannot guarantee a rate.'],
 ['terms'=>['on site','onsite','on-site','hybrid','remote','work setup','work from home'], 'answer'=>'Job postings show the work setup, such as On-Site or Hybrid. On-Site and Hybrid postings may also display a location. Review each job before applying.'],
 ['terms'=>['requirements','eligible','eligibility','qualification','qualify','experience required','who can apply'], 'answer'=>'Read the requirements on each job details page. Eligibility depends on that particular role, so do not assume every learner qualifies for every opening.'],
 ['terms'=>['badges','badge','certificate','certification','skills'], 'answer'=>'Check the requirements on the specific job listing for any certificates or skills requested. The current application form uploads a CV PDF; do not assume badge upload is required for every job.'],
 ['terms'=>['documents','document needed','cv','resume','upload','pdf'], 'answer'=>'On a job details page, use the CV upload field to select a PDF, maximum 5 MB, then press Apply. Review the position description for any additional requirements.'],
 ['terms'=>['profile','edit my details','update details','contact details','student profile'], 'answer'=>'Open Student Profile from the dashboard. Use the available profile editing options to keep your information up to date.'],
 ['terms'=>['find jobs','browse jobs','search job','available jobs','vacancy','vacancies','job posting','no jobs','open positions'], 'answer'=>'Your student dashboard lists open job postings. Each card shows the title, company, work setup and deadline. Select View Details to learn more. If you see No Jobs Yet, no open postings are currently listed.'],
 ['terms'=>['apply','applying','application','internship','job'], 'answer'=>'To apply: (1) open your student dashboard, (2) select View Details on an open job, (3) review its description and requirements, (4) select a PDF CV no larger than 5 MB, and (5) click Apply. A success message appears on the dashboard when submission succeeds.'],
 ['terms'=>['contact','helpdesk','support','coordinator','human','real person'], 'answer'=>'For an issue this helper cannot resolve, contact your WIL coordinator or the support contact supplied by your institution. Avoid sending ID documents or passwords through this chat.'],
 ['terms'=>['thank','thanks'], 'answer'=>'You are welcome! Ask me about jobs, applications, CV uploads or deadlines whenever you need help.'],
 ['terms'=>['hi','hello','hey','good morning','good afternoon','good evening'], 'answer'=>'Hello! I am the WIL Connect Helper. Need help finding jobs, applying, uploading your CV or checking deadlines?']
];
$best = -1; $answer = null;
foreach ($rules as $rule) {
 foreach ($rule['terms'] as $term) {
  // Match whole words/phrases rather than matching short words inside other words.
  if (preg_match('/(?<![a-z0-9])'.preg_quote($term, '/').'(?=$|[^a-z0-9])/i', $q)) {
   $score = strlen($term);
   if ($score > $best) { $best = $score; $answer = $rule['answer']; }
  }
 }
}
echo json_encode(['response'=>$answer ?? 'I can help with finding WIL jobs, applying, PDF CV uploads, application errors, deadlines, eligibility, interviews and profiles. Could you rephrase your question?'], JSON_UNESCAPED_UNICODE);
