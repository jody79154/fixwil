<?php
require_once "../includes/session_init.php";
require_once "../includes/auth.inc.php";
require_role('student');
require_once "../includes/session_timeout.inc.php";


// Get the userID from the session
$userID = $_SESSION['userID'];

try {
    // Connect to database
    require_once "../includes/dbh.inc.php";

    //  Get studens details
    $studentQuery = "SELECT fullName, email, role, profilePic, bio
                     FROM users
                     WHERE id = :userID";

    $stmt = $pdo->prepare($studentQuery);
    $stmt->bindParam(":userID", $userID);
    $stmt->execute();

    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    // checks If student doesn't exist
    if (!$student) {
        header("Location: ../login.php");
        die();
    }


    // 5. Get student's applications
    $applicationsQuery = "SELECT applications.*, 
                                 job_postings.jobTitle,
                                 job_postings.company
                          FROM applications
                          JOIN job_postings 
                          ON applications.jobID = job_postings.id
                          WHERE applications.studentID = :userID";

    $stmt = $pdo->prepare($applicationsQuery);
    $stmt->bindParam(":userID", $userID);
    $stmt->execute();

    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // 6. Get lecturer notes
    $notesQuery = "SELECT lecturer_notes.note,
                          users.fullName
                   FROM lecturer_notes
                   JOIN users 
                   ON lecturer_notes.lecturerID = users.id
                   WHERE lecturer_notes.studentID = :userID";

    $stmt = $pdo->prepare($notesQuery);
    $stmt->bindParam(":userID", $userID);
    $stmt->execute();

    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // Get skills
    $skillsQuery = "SELECT skill FROM skills WHERE studentID = :userID ORDER BY id";
    $stmt = $pdo->prepare($skillsQuery);
    $stmt->bindParam(":userID", $userID);
    $stmt->execute();
    $skills = $stmt->fetchAll(PDO::FETCH_COLUMN);


    // Get project links
    $linksQuery = "SELECT url FROM project_links WHERE studentID = :userID ORDER BY id";
    $stmt = $pdo->prepare($linksQuery);
    $stmt->bindParam(":userID", $userID);
    $stmt->execute();
    $projectLinks = $stmt->fetchAll(PDO::FETCH_COLUMN);


    // Get certifications
    $certsQuery = "SELECT * FROM certifications WHERE studentID = :userID ORDER BY id";
    $stmt = $pdo->prepare($certsQuery);
    $stmt->bindParam(":userID", $userID);
    $stmt->execute();
    $certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    die("Query Failed: " . $e->getMessage());
}

// Month numbers -> names, for displaying certification dates
$months = [
    "01" => "January", "02" => "February", "03" => "March", "04" => "April",
    "05" => "May", "06" => "June", "07" => "July", "08" => "August",
    "09" => "September", "10" => "October", "11" => "November", "12" => "December"
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile</title>

    <link rel="stylesheet" href="#">
</head>

<body>

    <!-- Student Profile-->

    <h1>My Profile</h1>

    <?php if (!empty($student['profilePic'])): ?>

        <img src="../<?php echo htmlspecialchars($student['profilePic']); ?>"
             alt="Profile Picture"
             width="150">

    <?php endif; ?>


    <h2>
        <?php echo htmlspecialchars($student['fullName']); ?>
    </h2>

    <p>
        <strong>Email:</strong>
        <?php echo htmlspecialchars($student['email']); ?>
    </p>

    <p>
        <strong>Role:</strong>
        <?php echo htmlspecialchars($student['role']); ?>
    </p>

    <p>
        <a href="edit_profile.php">Edit Profile</a>
    </p>


    <!-- Bio -->

    <h2>About Me</h2>

    <?php if (!empty($student['bio'])): ?>
        <p><?php echo nl2br(htmlspecialchars($student['bio'])); ?></p>
    <?php else: ?>
        <p>No bio added yet.</p>
    <?php endif; ?>


    <!-- Skills -->

    <h2>Skills</h2>

    <?php if (empty($skills)): ?>
        <p>No skills added yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($skills as $skill): ?>
                <li><?php echo htmlspecialchars($skill); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>


    <!-- Project Links -->

    <h2>Project Links</h2>

    <?php if (empty($projectLinks)): ?>
        <p>No project links added yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($projectLinks as $link): ?>
                <li>
                    <a href="<?php echo htmlspecialchars($link); ?>" target="_blank" rel="noopener noreferrer">
                        <?php echo htmlspecialchars($link); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>


    <!-- Certifications -->

    <h2>Certifications & Badges</h2>

    <?php if (empty($certifications)): ?>
        <p>No certifications added yet.</p>
    <?php else: ?>
        <?php foreach ($certifications as $cert): ?>
            <div>
                <p>
                    <strong><?php echo htmlspecialchars($cert['name']); ?></strong>
                    <?php if (!empty($cert['organization'])): ?>
                        &mdash; <?php echo htmlspecialchars($cert['organization']); ?>
                    <?php endif; ?>
                </p>

                <?php if (!empty($cert['issue_month']) || !empty($cert['issue_year'])): ?>
                    <p>
                        Issued:
                        <?php echo htmlspecialchars($months[$cert['issue_month']] ?? ''); ?>
                        <?php echo htmlspecialchars($cert['issue_year']); ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($cert['no_expiration'])): ?>
                    <p>Does not expire</p>
                <?php elseif (!empty($cert['expiration_month']) || !empty($cert['expiration_year'])): ?>
                    <p>
                        Expires:
                        <?php echo htmlspecialchars($months[$cert['expiration_month']] ?? ''); ?>
                        <?php echo htmlspecialchars($cert['expiration_year']); ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($cert['credential_id'])): ?>
                    <p>Credential ID: <?php echo htmlspecialchars($cert['credential_id']); ?></p>
                <?php endif; ?>

                <?php if (!empty($cert['credential_url'])): ?>
                    <p>
                        <a href="<?php echo htmlspecialchars($cert['credential_url']); ?>" target="_blank" rel="noopener noreferrer">
                            View Credential
                        </a>
                    </p>
                <?php endif; ?>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>


    <!-- Applications -->

    <h2>My Applications</h2>

    <table border="1">

        <tr>
            <th>Job Title</th>
            <th>Company</th>
            <th>Date Applied</th>
            <th>Status</th>
        </tr>

        <?php if (empty($applications)): ?>

            <tr>
                <td colspan="4">
                    No Applications Yet
                </td>
            </tr>

        <?php else: ?>

            <?php foreach ($applications as $application): ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($application['jobTitle']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($application['company']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($application['appliedAt']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($application['status']); ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

    </table>


    <!-- Lecture Notes -->

    <h2>Lecturer Notes</h2>

    <?php if (empty($notes)): ?>

        <p>No lecturer notes yet.</p>

    <?php else: ?>

        <?php foreach ($notes as $note): ?>

            <div>

                <p>
                    <strong>Lecturer:</strong>
                    <?php echo htmlspecialchars($note['fullName']); ?>
                </p>

                <p>
                    <strong>Note:</strong>
                    <?php echo htmlspecialchars($note['note']); ?>
                </p>

            </div>

            <hr>

        <?php endforeach; ?>

    <?php endif; ?>

<?php require __DIR__ . '/chat_widget.php'; ?>
</body>
</html>
