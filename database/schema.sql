CREATE DATABASE wil_connect;

USE wil_connect;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullName VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'recruiter', 'lecturer') NOT NULL,
    company VARCHAR(150) DEFAULT NULL,
    profilePic VARCHAR(255) DEFAULT NULL,
    bio TEXT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verificationCode VARCHAR(6) DEFAULT NULL,
    isVerified TINYINT(1) DEFAULT 0,
    codeExpiresAt DATETIME NULL,
    location VARCHAR(150) DEFAULT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expires_at DATETIME DEFAULT NULL
);

CREATE TABLE job_postings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recruiterID INT NOT NULL,
    company VARCHAR(150) NOT NULL,
    jobTitle VARCHAR(150) NOT NULL,
    jobType ENUM('Full-Time', 'Part-Time', 'Internship') NOT NULL,
    workSetup ENUM('Remote', 'On-Site', 'Hybrid') NOT NULL,
    location VARCHAR(150) DEFAULT NULL,
    jobDescription TEXT NOT NULL,
    requirements TEXT NOT NULL,
    deadline DATE NOT NULL,
    status ENUM('open', 'closed') DEFAULT 'open',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (recruiterID)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    studentID INT NOT NULL,
    jobID INT NOT NULL,
    cvFile VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'accepted', 'denied') DEFAULT 'pending',
    appliedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (studentID)
        REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (jobID)
        REFERENCES job_postings(id)
        ON DELETE CASCADE,

    UNIQUE(studentID, jobID)
);

CREATE TABLE lecturer_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lecturerID INT NOT NULL,
    studentID INT NOT NULL,
    note TEXT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lecturerID) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (studentID) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    studentID INT NOT NULL,
    skill VARCHAR(100) NOT NULL,
    FOREIGN KEY (studentID) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE project_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    studentID INT NOT NULL,
    url VARCHAR(255) NOT NULL,
    FOREIGN KEY (studentID) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE certifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    studentID INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    organization VARCHAR(150) DEFAULT NULL,
    issue_month VARCHAR(2) DEFAULT NULL,
    issue_year VARCHAR(4) DEFAULT NULL,
    no_expiration TINYINT(1) DEFAULT 0,
    expiration_month VARCHAR(2) DEFAULT NULL,
    expiration_year VARCHAR(4) DEFAULT NULL,
    credential_id VARCHAR(150) DEFAULT NULL,
    credential_url VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (studentID) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expiresAt DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (userID)
        REFERENCES users(id)
        ON DELETE CASCADE,

    UNIQUE(token)
);

CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    selector CHAR(24) NOT NULL,
    validatorHash CHAR(64) NOT NULL,
    expiresAt DATETIME NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (userID)
        REFERENCES users(id)
        ON DELETE CASCADE,

    UNIQUE(selector)
);
