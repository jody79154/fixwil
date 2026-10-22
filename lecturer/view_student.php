<?php
require_once "../includes/auth.inc.php";
require_once "../includes/session_timeout.inc.php";
require_role('lecturer');

$studentID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$studentID) {
    header("Location: dashboard.php");
    die();
}
$lecturerID = $_SESSION['userID'];

require_once "../includes/dbh.inc.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $note = trim($_POST['note'] ?? '');

    if ($note === '') {
        header("Location: view_student.php?id=" . $studentID . "&error=empty_note");
        die();
    }

    if (mb_strlen($note) > 1000) {
        header("Location: view_student.php?id=" . $studentID . "&error=note_too_long");
        die();
    }

    try {
        $notesQuery = "INSERT INTO lecturer_notes(lecturerID, studentID, note)
                        VALUES (:lecturerID, :studentID, :note)";
        $stmtNotes = $pdo->prepare($notesQuery);
        $stmtNotes->bindParam(":lecturerID", $lecturerID);
        $stmtNotes->bindParam(":studentID", $studentID);
        $stmtNotes->bindParam(":note", $note);

        if ($stmtNotes->execute()) {
            header("Location: view_student.php?id=" . $studentID . "&success=note_added");
        } else {
            header("Location: view_student.php?id=" . $studentID . "&error=database_error");
        }
        die();
    } catch (PDOException $e) {
        error_log("Note creation failed: " . $e->getMessage());
        header("Location: view_student.php?id=" . $studentID . "&error=database_error");
        die();
    }
}

try {
    $studentDetailsQuery = "SELECT * FROM users WHERE id = :studentID";
    $fetchNotesQuery = "SELECT lecturer_notes.*, users.fullName AS lecturerName FROM lecturer_notes
                    JOIN users ON lecturer_notes.lecturerID = users.id
                    WHERE lecturer_notes.studentID = :studentID
                    ORDER BY lecturer_notes.createdAt DESC";
                 
    $stmtStudentDetail = $pdo->prepare($studentDetailsQuery);
    $stmtFetchNotes = $pdo->prepare($fetchNotesQuery);
    $stmtStudentDetail->bindParam(":studentID", $studentID);
    $stmtFetchNotes->bindParam(":studentID", $studentID);
    $stmtFetchNotes->execute();
    $stmtStudentDetail->execute();
    $studentDetails = $stmtStudentDetail->fetch(PDO::FETCH_ASSOC);
    $fetchNotes = $stmtFetchNotes->fetchAll(PDO::FETCH_ASSOC);
    if($stmtStudentDetail->rowCount() == 0){
        header("Location:../lecturer/dashboard.php");
        die();
    }
    
} catch (PDOException $e)
{
    die("Query Failed: " . $e->getMessage());
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student</title>
</head>

<body>
    <a href="dashboard.php">&larr; Back to Students</a>

    <h2><?php echo htmlspecialchars($studentDetails['fullName']); ?></h2>
    <p>Email: <?php echo htmlspecialchars($studentDetails['email']); ?></p>

    <h3>Add a Lecturer's Note</h3>
    <form method="POST" action="view_student.php?id=<?php echo (int)$studentID; ?>">
        <textarea name="note" rows="5" cols="50" required></textarea>
        <br>
        <button type="submit">Save Note</button>
    </form>

    <?php if (isset($_GET['success'])): ?>
    <p>Note saved.</p>
<?php elseif (($_GET['error'] ?? '') === 'empty_note'): ?>
    <p>The note cannot be empty.</p>
<?php elseif (($_GET['error'] ?? '') === 'note_too_long'): ?>
    <p>The note cannot be longer than 1000 characters.</p>
<?php elseif (isset($_GET['error'])): ?>
    <p>Something went wrong. Please try again.</p>
<?php endif; ?>

    <h3>Notes</h3>
    <?php if (empty($fetchNotes)): ?>
        <p>No notes yet.</p>
    <?php else: ?>
        <?php foreach ($fetchNotes as $n): ?>
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
