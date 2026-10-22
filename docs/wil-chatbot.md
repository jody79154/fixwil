# WIL Connect learner chatbot

An offline, rule-based PHP chatbot for authenticated student pages. No external AI API, Python service or new database table is required.

## Files
- `student/chatbot.php`: authenticated POST JSON endpoint (`{"message":"How do I apply?"}`), returning `{"response":"..."}`
- `student/chat_widget.php`: accessible floating chat panel
- `css/wil_chatbot.css`: chat styling
- `js/wil_chatbot.js`: browser interaction and same-origin fetch

Included at the bottom of `student/dashboard.php`, `student/job_detail.php`, and `student/profile.php`.

## Supported questions
Open jobs, job details, applying, PDF CV format and 5 MB upload limit, duplicate applications, errors, deadlines, qualifications, work setup, application status, stipend questions, interviews, profiles and login.

The bot gives guidance only. It does not access personal application records, promise placement, or invent deadline/stipend amounts.

## Local testing
1. Run the website under PHP with existing sessions and database configuration.
2. Sign in as a student and open the dashboard, job detail and profile pages.
3. Open the floating chat button and test common questions and quick prompts.
4. Verify browser Network tab: `POST student/chatbot.php` returns JSON with HTTP 200 for a signed-in student.
5. For PHP syntax: `php -l student/chatbot.php && php -l student/chat_widget.php`.
6. Verify users without student permissions cannot access the endpoint.

## Extending answers
Edit the `$rules` array in `student/chatbot.php`, adding more specific terms before generic ones when possible. The engine chooses the longest matching whole-word phrase. Responses should describe only confirmed features.

No main branch files were changed by this feature branch until its PR is merged.
