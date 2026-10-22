<?php
require_once "../includes/auth.inc.php";
require_once "../includes/session_timeout.inc.php";
require_role('lecturer');

//get the data
$userID = $_SESSION['userID'];
$username = $_SESSION['fullName'];

// read the search term (empty string if none was submitted)
$search = trim($_GET['q'] ?? '');

try {
    require_once "../includes/dbh.inc.php";

    if ($search !== '') {
        $studentsQuery = "SELECT id, fullName, email FROM users
                          WHERE role = 'student'
                          AND (fullName LIKE :name OR email LIKE :email)
                          ORDER BY fullName";
        $studentsStmt = $pdo->prepare($studentsQuery);
        $studentsStmt->execute([
            ':name'  => '%' . $search . '%',
            ':email' => '%' . $search . '%'
        ]);
    } else {
        $studentsQuery = "SELECT id, fullName, email FROM users
                          WHERE role = 'student'
                          ORDER BY fullName";
        $studentsStmt = $pdo->prepare($studentsQuery);
        $studentsStmt->execute();
    }

    $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $students = [];
    $studentsError = "Error loading students.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="#">
</head>

<body>
    <h2>Students</h2>

    <form method="GET" action="dashboard.php">
    <input type="text" name="q" placeholder="Search by name or email"
           value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Search</button>
    <?php if ($search !== ''): ?>
        <a href="dashboard.php">Clear</a>
    <?php endif; ?>
    </form>

    <div id="student-cards">
    <?php if (isset($studentsError)): ?>
        <p><?php echo $studentsError; ?></p>
    <?php elseif (empty($students)): ?>
        <p><?php echo $search !== '' ? 'No students match your search.' : 'No Students Yet'; ?></p>
    <?php else: ?>
        <?php foreach ($students as $student): ?>
            <div>
                <h3><?php echo htmlspecialchars($student['fullName']); ?></h3>
                <a href="view_student.php?id=<?php echo $student['id']; ?>">View Student</a> 
            </div>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>


