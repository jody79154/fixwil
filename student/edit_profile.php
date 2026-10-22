<?php
require_once "../includes/auth.inc.php";
require_role('student');
require_once "../includes/dbh.inc.php";

$studentID = $_SESSION['userID'];


/* SAVE PROFILE (runs before we load data, so the page below always
   shows the freshly saved state after a redirect) */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {
        /* PROFILE PICTURE */

        if (
            isset($_FILES["profile_picture"]) &&
            $_FILES["profile_picture"]["error"] === UPLOAD_ERR_OK
        ) {
            $fileName = $_FILES["profile_picture"]["name"];
            $tmpName = $_FILES["profile_picture"]["tmp_name"];

            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedTypes = ["jpg", "jpeg", "png"];

            if (in_array($fileExtension, $allowedTypes)) {

                $uploadDir = "../uploads/profile_pics/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $newFileName = uniqid('profile_', true) . "." . $fileExtension;
                $uploadPath = $uploadDir . $newFileName;

                if (move_uploaded_file($tmpName, $uploadPath)) {
                    $profilePicPath = "uploads/profile_pics/" . $newFileName;

                    $updatePic = $pdo->prepare("UPDATE users SET profilePic = ? WHERE id = ?");
                    $updatePic->execute([$profilePicPath, $studentID]);
                }
            }
        }

        /* BIO */

        $bio = isset($_POST["bio"]) ? trim($_POST["bio"]) : "";
        $updateBio = $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");
        $updateBio->execute([$bio, $studentID]);

        /* SKILLS - replace the student's full skill list with what was submitted */

        $skills = [];
        if (isset($_POST["skills"])) {
            $skillsInput = trim($_POST["skills"]);
            if ($skillsInput !== "") {
                foreach (explode(",", $skillsInput) as $skill) {
                    $skill = trim($skill);
                    if ($skill !== "" && !in_array($skill, $skills)) {
                        $skills[] = $skill;
                    }
                }
            }
        }

        $pdo->prepare("DELETE FROM skills WHERE studentID = ?")->execute([$studentID]);
        $insertSkill = $pdo->prepare("INSERT INTO skills (studentID, skill) VALUES (?, ?)");
        foreach ($skills as $skill) {
            $insertSkill->execute([$studentID, $skill]);
        }

        /* PROJECT LINKS - same replace-all approach */

        $projectLinks = [];
        if (isset($_POST["project_links"])) {
            foreach ($_POST["project_links"] as $link) {
                $link = trim($link);
                if ($link !== "") {
                    $projectLinks[] = $link;
                }
            }
        }

        $pdo->prepare("DELETE FROM project_links WHERE studentID = ?")->execute([$studentID]);
        $insertLink = $pdo->prepare("INSERT INTO project_links (studentID, url) VALUES (?, ?)");
        foreach ($projectLinks as $link) {
            $insertLink->execute([$studentID, $link]);
        }

        /* CERTIFICATIONS - same replace-all approach */

        $pdo->prepare("DELETE FROM certifications WHERE studentID = ?")->execute([$studentID]);

        if (isset($_POST["badge_name"])) {
            $insertBadge = $pdo->prepare(
                "INSERT INTO certifications
                (studentID, name, organization, issue_month, issue_year, no_expiration, expiration_month, expiration_year, credential_id, credential_url)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $count = count($_POST["badge_name"]);
            for ($i = 0; $i < $count; $i++) {
                $name = trim($_POST["badge_name"][$i] ?? "");
                if ($name === "") {
                    continue;
                }

                $organization = trim($_POST["badge_organization"][$i] ?? "");
                $issueMonth = $_POST["badge_issue_month"][$i] ?? "";
                $issueYear = $_POST["badge_issue_year"][$i] ?? "";
                $expirationMonth = $_POST["badge_expiration_month"][$i] ?? "";
                $expirationYear = $_POST["badge_expiration_year"][$i] ?? "";
                $credentialId = trim($_POST["badge_credential_id"][$i] ?? "");
                $credentialUrl = trim($_POST["badge_credential_url"][$i] ?? "");
                $noExpiration = isset($_POST["badge_no_expiration"][$i]) ? 1 : 0;

                $insertBadge->execute([
                    $studentID,
                    $name,
                    $organization,
                    $issueMonth,
                    $issueYear,
                    $noExpiration,
                    $expirationMonth,
                    $expirationYear,
                    $credentialId,
                    $credentialUrl
                ]);
            }
        }

        // Post-redirect-get: avoids a resubmission popup on refresh
        // and guarantees the page below always shows what's really saved.
        header("Location: edit_profile.php?success=1");
        exit();

    } catch (PDOException $e) {
        error_log("Edit profile save failed: " . $e->getMessage());
        die("Something went wrong saving your profile. Please try again.");
    }
}


/* LOAD PROFILE DATA */

try {
    $userStmt = $pdo->prepare("SELECT bio, profilePic FROM users WHERE id = ?");
    $userStmt->execute([$studentID]);
    $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);

    $bio = $userRow["bio"] ?? "";
    $profilePicture = $userRow["profilePic"] ?? "";

    $skillsStmt = $pdo->prepare("SELECT skill FROM skills WHERE studentID = ? ORDER BY id");
    $skillsStmt->execute([$studentID]);
    $skills = $skillsStmt->fetchAll(PDO::FETCH_COLUMN);

    $linksStmt = $pdo->prepare("SELECT url FROM project_links WHERE studentID = ? ORDER BY id");
    $linksStmt->execute([$studentID]);
    $projectLinks = $linksStmt->fetchAll(PDO::FETCH_COLUMN);

    $badgesStmt = $pdo->prepare("SELECT * FROM certifications WHERE studentID = ? ORDER BY id");
    $badgesStmt->execute([$studentID]);
    $badges = $badgesStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Query Failed: " . $e->getMessage());
}

// Keep the same shape the original template expects, so the HTML below
// (borrowed from the original prototype) needs no further changes.
$profileData = [
    "bio" => $bio,
    "skills" => $skills,
    "project_links" => $projectLinks,
    "badges" => $badges
];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <!-- PAGE SETTINGS -->

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Profile - WIL-CONNECT</title>

    <link rel="stylesheet" href="../css/edit_profile.css">

</head>

<body>


<!-- NAVIGATION BAR -->

<header class="navbar">

    <div class="logo-section">

        <div class="logo-box">
            WC
        </div>

        <div class="logo-text">

            <h2>WIL-CONNECT</h2>

            <p>
                Unlocking Opportunities • Building Futures
            </p>

        </div>

    </div>


    <div class="nav-links">

        <div class="role-switch">

            <span>Switch Role:</span>

            <span class="student-role">
                Student
            </span>

        </div>

        <a href="dashboard.php">
            Jobs
        </a>

        <a href="edit_profile.php">
            My Profile & Notes
        </a>

    </div>

</header>


<!-- MAIN PAGE -->

<main class="page-container">

    <?php if (isset($_GET['success'])): ?>
        <p class="success-message">Profile saved.</p>
    <?php endif; ?>


    <!-- PAGE HEADER -->

    <div class="page-header">

        <h1>
            Edit Profile
        </h1>

        <p>
            Update your profile information to help employers learn more about you.
        </p>

    </div>


    <!-- PROFILE FORM -->

    <form
        action="edit_profile.php"
        method="POST"
        enctype="multipart/form-data"
    >

        <div class="profile-card">


            <!-- PROFILE PICTURE -->

            <section class="form-section">

                <h2>
                    Profile Picture
                </h2>

                <p class="section-description">
                    Add a professional picture to your profile.
                </p>

                <div class="profile-picture-area">

                    <div class="profile-preview">

                        <span
                            id="profilePlaceholder"
                            style="<?php
                                echo $profilePicture != ""
                                    ? "display:none;"
                                    : "";
                            ?>"
                        ></span>

                        <img
                            id="profilePreview"
                            src="<?php
                                echo htmlspecialchars($profilePicture ? "../" . $profilePicture : "");
                            ?>"
                            alt="Profile Picture"
                            style="<?php
                                echo $profilePicture == ""
                                    ? "display:none;"
                                    : "display:block;";
                            ?>"
                        >

                    </div>


                    <div class="upload-information">

                        <h3>
                            Choose a profile picture
                        </h3>

                        <p>
                            JPG, PNG or JPEG. Recommended size: 400 × 400px.
                        </p>

                        <label
                            for="profilePicture"
                            class="upload-button"
                        >
                            Choose Picture
                        </label>

                        <input
                            type="file"
                            id="profilePicture"
                            name="profile_picture"
                            accept=".jpg,.jpeg,.png"
                        >

                    </div>

                </div>

            </section>


            <!-- BIO -->

            <section class="form-section">

                <h2>
                    About Me / Bio
                </h2>

                <p class="section-description">
                    Tell employers a little about yourself.
                </p>

                <label
                    for="bio"
                    class="input-label"
                >
                    Bio
                </label>

                <textarea
                    id="bio"
                    name="bio"
                    maxlength="500"
                    placeholder="Tell employers about yourself, your interests, career goals and what you are looking for..."
                ><?php
                    echo htmlspecialchars($profileData["bio"]);
                ?></textarea>

                <p class="input-help">
                    Maximum 500 characters.
                </p>

            </section>


            <!-- SKILLS -->

            <section class="form-section">

                <h2>
                    Skills
                </h2>

                <p class="section-description">
                    Add skills that you have developed through your studies, projects or experience.
                </p>


                <div
                    class="skills-box"
                    id="skillsBox"
                >

                    <?php foreach (
                        $profileData["skills"]
                        as $index => $skill
                    ): ?>

                        <div class="skill-tag">

                            <span>
                                <?php
                                    echo htmlspecialchars($skill);
                                ?>
                            </span>

                            <button
                                type="button"
                                onclick="removeSkill(<?php echo $index; ?>)"
                            >
                                ×
                            </button>

                        </div>

                    <?php endforeach; ?>


                    <input
                        type="text"
                        id="skillInput"
                        placeholder="Type a skill and press Enter..."
                    >

                </div>


                <input
                    type="hidden"
                    name="skills"
                    id="skillsHidden"
                    value="<?php
                        echo htmlspecialchars(
                            implode(
                                ",",
                                $profileData["skills"]
                            )
                        );
                    ?>"
                >


                <p class="input-help">
                    Example: Java, Python, HTML, CSS, SQL, Machine Learning.
                </p>

            </section>


            <!-- PROJECT LINKS -->

            <section class="form-section">

                <h2>
                    Project Links
                </h2>

                <p class="section-description">
                    Add links to projects that you would like employers to see.
                </p>


                <div id="projectsContainer">

                    <?php if (
                        count($profileData["project_links"]) > 0
                    ): ?>

                        <?php foreach (
                            $profileData["project_links"]
                            as $link
                        ): ?>

                            <div class="project-row">

                                <input
                                    type="url"
                                    name="project_links[]"
                                    value="<?php
                                        echo htmlspecialchars($link);
                                    ?>"
                                    placeholder="https://github.com/yourusername/project"
                                >

                                <button
                                    type="button"
                                    class="remove-project"
                                    onclick="removeProject(this)"
                                >
                                    ×
                                </button>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="project-row">

                            <input
                                type="url"
                                name="project_links[]"
                                placeholder="https://github.com/yourusername/project"
                            >

                            <button
                                type="button"
                                class="remove-project"
                                onclick="removeProject(this)"
                            >
                                ×
                            </button>

                        </div>

                    <?php endif; ?>

                </div>


                <button
                    type="button"
                    class="outline-button"
                    onclick="addProject()"
                >
                    + Add Project
                </button>


                <p class="input-help">
                    You can add GitHub, portfolio or other relevant project links.
                </p>

            </section>


            <!-- CERTIFICATIONS & BADGES -->

            <section class="form-section">

                <h2>
                    Certifications & Badges
                </h2>

                <p class="section-description">
                    Add industry certifications and credentials that you have earned.
                </p>


                <div id="badgesContainer">

                    <?php foreach (
                        $profileData["badges"]
                        as $index => $badge
                    ): ?>

                        <div class="badge-item">

                            <div class="badge-left">

                                <div class="badge-icon">
                                    🏅
                                </div>


                                <div class="badge-fields">

                                    <input
                                        type="text"
                                        name="badge_name[]"
                                        value="<?php
                                            echo htmlspecialchars(
                                                $badge["name"] ?? ""
                                            );
                                        ?>"
                                        placeholder="Certification name"
                                    >


                                    <input
                                        type="text"
                                        name="badge_organization[]"
                                        value="<?php
                                            echo htmlspecialchars(
                                                $badge["organization"] ?? ""
                                            );
                                        ?>"
                                        placeholder="Issuing organisation"
                                    >


                                    <div class="date-row">

                                        <select name="badge_issue_month[]">

                                            <option value="">
                                                Issue Month
                                            </option>

                                            <?php
                                            $months = [
                                                "01" => "January",
                                                "02" => "February",
                                                "03" => "March",
                                                "04" => "April",
                                                "05" => "May",
                                                "06" => "June",
                                                "07" => "July",
                                                "08" => "August",
                                                "09" => "September",
                                                "10" => "October",
                                                "11" => "November",
                                                "12" => "December"
                                            ];

                                            foreach (
                                                $months
                                                as $number => $month
                                            ):
                                            ?>

                                                <option
                                                    value="<?php echo $number; ?>"
                                                    <?php
                                                    echo (
                                                        ($badge["issue_month"] ?? "")
                                                        == $number
                                                    )
                                                    ? "selected"
                                                    : "";
                                                    ?>
                                                >
                                                    <?php echo $month; ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>


                                        <select name="badge_issue_year[]">

                                            <option value="">
                                                Issue Year
                                            </option>

                                            <?php
                                            for (
                                                $year = date("Y");
                                                $year >= 2000;
                                                $year--
                                            ):
                                            ?>

                                                <option
                                                    value="<?php echo $year; ?>"
                                                    <?php
                                                    echo (
                                                        ($badge["issue_year"] ?? "")
                                                        == $year
                                                    )
                                                    ? "selected"
                                                    : "";
                                                    ?>
                                                >
                                                    <?php echo $year; ?>
                                                </option>

                                            <?php endfor; ?>

                                        </select>

                                    </div>


                                    <label class="expiration-option">

                                        <input
                                            type="checkbox"
                                            name="badge_no_expiration[<?php echo $index; ?>]"
                                            <?php
                                            echo !empty(
                                                $badge["no_expiration"]
                                            )
                                            ? "checked"
                                            : "";
                                            ?>
                                        >

                                        This certification does not expire

                                    </label>


                                    <div class="date-row">

                                        <select name="badge_expiration_month[]">

                                            <option value="">
                                                Expiration Month
                                            </option>

                                            <?php foreach (
                                                $months
                                                as $number => $month
                                            ): ?>

                                                <option
                                                    value="<?php echo $number; ?>"
                                                    <?php
                                                    echo (
                                                        ($badge["expiration_month"] ?? "")
                                                        == $number
                                                    )
                                                    ? "selected"
                                                    : "";
                                                    ?>
                                                >
                                                    <?php echo $month; ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>


                                        <select name="badge_expiration_year[]">

                                            <option value="">
                                                Expiration Year
                                            </option>

                                            <?php
                                            for (
                                                $year = date("Y");
                                                $year <= date("Y") + 15;
                                                $year++
                                            ):
                                            ?>

                                                <option
                                                    value="<?php echo $year; ?>"
                                                    <?php
                                                    echo (
                                                        ($badge["expiration_year"] ?? "")
                                                        == $year
                                                    )
                                                    ? "selected"
                                                    : "";
                                                    ?>
                                                >
                                                    <?php echo $year; ?>
                                                </option>

                                            <?php endfor; ?>

                                        </select>

                                    </div>


                                    <input
                                        type="text"
                                        name="badge_credential_id[]"
                                        value="<?php
                                            echo htmlspecialchars(
                                                $badge["credential_id"] ?? ""
                                            );
                                        ?>"
                                        placeholder="Credential ID"
                                    >


                                    <input
                                        type="url"
                                        name="badge_credential_url[]"
                                        value="<?php
                                            echo htmlspecialchars(
                                                $badge["credential_url"] ?? ""
                                            );
                                        ?>"
                                        placeholder="Credential URL (Credly, etc.)"
                                    >

                                </div>

                            </div>


                            <button
                                type="button"
                                class="remove-badge"
                                onclick="this.parentElement.remove()"
                            >
                                Remove
                            </button>

                        </div>

                    <?php endforeach; ?>

                </div>


                <button
                    type="button"
                    class="outline-button"
                    onclick="addBadge()"
                >
                    + Add Certification
                </button>


                <p class="input-help">
                    Add the credential link so employers can view the certification.
                </p>

            </section>


            <!-- FORM BUTTONS -->

            <div class="form-footer">

                <button
                    type="button"
                    class="cancel-button"
                    onclick="window.history.back()"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="save-button"
                >
                    Save Profile
                </button>

            </div>

        </div>

    </form>

</main>


<script>

/* PROFILE PICTURE */

const profilePicture =
    document.getElementById("profilePicture");

profilePicture.addEventListener("change", function () {

    const file = this.files[0];

    if (file) {

        const reader = new FileReader();

        reader.onload = function (event) {

            const preview =
                document.getElementById("profilePreview");

            const placeholder =
                document.getElementById("profilePlaceholder");

            preview.src =
                event.target.result;

            preview.style.display =
                "block";

            placeholder.style.display =
                "none";
        };

        reader.readAsDataURL(file);
    }
});


/* SKILLS */

const skillInput =
    document.getElementById("skillInput");

const skillsBox =
    document.getElementById("skillsBox");

const skillsHidden =
    document.getElementById("skillsHidden");

let skills =
    <?php echo json_encode(
        array_values($profileData["skills"])
    ); ?>;


skillInput.addEventListener(
    "keydown",
    function (event) {

        if (event.key === "Enter") {

            event.preventDefault();

            const skill =
                skillInput.value.trim();

            if (
                skill !== "" &&
                !skills.includes(skill)
            ) {

                skills.push(skill);

                displaySkills();

                skillInput.value = "";
            }
        }
    }
);


function displaySkills() {

    const oldTags =
        document.querySelectorAll(".skill-tag");

    oldTags.forEach(function (tag) {
        tag.remove();
    });


    skills.forEach(
        function (skill, index) {

            const tag =
                document.createElement("div");

            tag.className =
                "skill-tag";

            tag.innerHTML = `
                <span>${skill}</span>

                <button
                    type="button"
                    onclick="removeSkill(${index})"
                >
                    ×
                </button>
            `;

            skillsBox.insertBefore(
                tag,
                skillInput
            );
        }
    );


    skillsHidden.value =
        skills.join(",");
}


function removeSkill(index) {

    skills.splice(index, 1);

    displaySkills();
}


/* PROJECT LINKS */

function addProject() {

    const container =
        document.getElementById(
            "projectsContainer"
        );

    const projectRow =
        document.createElement("div");

    projectRow.className =
        "project-row";

    projectRow.innerHTML = `
        <input
            type="url"
            name="project_links[]"
            placeholder="https://github.com/yourusername/project"
        >

        <button
            type="button"
            class="remove-project"
            onclick="removeProject(this)"
        >
            ×
        </button>
    `;

    container.appendChild(projectRow);
}


function removeProject(button) {

    const projectRows =
        document.querySelectorAll(
            ".project-row"
        );

    if (projectRows.length > 1) {

        button.parentElement.remove();
    }
}


/* CERTIFICATIONS */

function addBadge() {

    const container =
        document.getElementById(
            "badgesContainer"
        );

    const badgeIndex =
        container.children.length;

    const badge =
        document.createElement("div");

    badge.className =
        "badge-item";

    badge.innerHTML = `

        <div class="badge-left">

            <div class="badge-icon">
                🏅
            </div>

            <div class="badge-fields">

                <input
                    type="text"
                    name="badge_name[]"
                    placeholder="Certification name"
                >

                <input
                    type="text"
                    name="badge_organization[]"
                    placeholder="Issuing organisation"
                >

                <div class="date-row">

                    <select name="badge_issue_month[]">

                        <option value="">
                            Issue Month
                        </option>

                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>

                    </select>


                    <select name="badge_issue_year[]">

                        <option value="">
                            Issue Year
                        </option>

                        <?php
                        for (
                            $year = date("Y");
                            $year >= 2000;
                            $year--
                        ):
                        ?>

                            <option value="<?php echo $year; ?>">
                                <?php echo $year; ?>
                            </option>

                        <?php endfor; ?>

                    </select>

                </div>


                <label class="expiration-option">

                    <input
                        type="checkbox"
                        name="badge_no_expiration[${badgeIndex}]"
                    >

                    This certification does not expire

                </label>


                <div class="date-row">

                    <select name="badge_expiration_month[]">

                        <option value="">
                            Expiration Month
                        </option>

                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>

                    </select>


                    <select name="badge_expiration_year[]">

                        <option value="">
                            Expiration Year
                        </option>

                        <?php
                        for (
                            $year = date("Y");
                            $year <= date("Y") + 15;
                            $year++
                        ):
                        ?>

                            <option value="<?php echo $year; ?>">
                                <?php echo $year; ?>
                            </option>

                        <?php endfor; ?>

                    </select>

                </div>


                <input
                    type="text"
                    name="badge_credential_id[]"
                    placeholder="Credential ID"
                >


                <input
                    type="url"
                    name="badge_credential_url[]"
                    placeholder="Credential URL (Credly, etc.)"
                >

            </div>

        </div>


        <button
            type="button"
            class="remove-badge"
            onclick="this.parentElement.remove()"
        >
            Remove
        </button>
    `;

    container.appendChild(badge);
}

</script>

</body>
</html>
