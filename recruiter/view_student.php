<?php
require_once "../includes/auth.inc.php";
require_once "../includes/session_timeout.inc.php";
require_role('recruiter');

$studentID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$studentID) {
    header("Location: applications.php");
    die();
}
$recruiterID = $_SESSION['userID'];

require_once "../includes/dbh.inc.php";

try {
    // Only return the student if they applied to one of THIS recruiter's jobs
    $studentQuery = "SELECT id, fullName, email, bio, location FROM users
                     WHERE id = ? AND role = 'student'
                     AND EXISTS (
                         SELECT 1 FROM applications
                         JOIN job_postings ON applications.jobID = job_postings.id
                         WHERE applications.studentID = users.id
                         AND job_postings.recruiterID = ?
                     )";
    $stmtStudent = $pdo->prepare($studentQuery);
    $stmtStudent->execute([$studentID, $recruiterID]);
    $student = $stmtStudent->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        header("Location: applications.php");
        die();
    }

    $notesQuery = "SELECT lecturer_notes.note, lecturer_notes.createdAt, users.fullName AS lecturerName
                   FROM lecturer_notes
                   JOIN users ON lecturer_notes.lecturerID = users.id
                   WHERE lecturer_notes.studentID = ?
                   ORDER BY lecturer_notes.createdAt DESC";
    $stmtNotes = $pdo->prepare($notesQuery);
    $stmtNotes->execute([$studentID]);
    $notes = $stmtNotes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Recruiter view_student failed: " . $e->getMessage());
    die("Something went wrong. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
</head>

<body>
    <a href="applications.php">&larr; Back to Applications</a>

    <h2><?php echo htmlspecialchars($student['fullName']); ?></h2>
    <p>Email: <?php echo htmlspecialchars($student['email']); ?></p>

    <?php if (!empty($student['location'])): ?>
        <p>Location: <?php echo htmlspecialchars($student['location']); ?></p>
    <?php endif; ?>

    <?php if (!empty($student['bio'])): ?>
        <h3>About</h3>
        <p><?php echo nl2br(htmlspecialchars($student['bio'])); ?></p>
    <?php endif; ?>

    <h3>Lecturer's Notes</h3>
    <?php if (empty($notes)): ?>
        <p>No lecturer notes for this student.</p>
    <?php else: ?>
        <?php foreach ($notes as $n): ?>
            <div>
                <p><?php echo nl2br(htmlspecialchars($n['note'])); ?></p>
                <small>
                    By <?php echo htmlspecialchars($n['lecturerName']); ?>
                    on <?php echo htmlspecialchars($n['createdAt']); ?>
                </small>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
