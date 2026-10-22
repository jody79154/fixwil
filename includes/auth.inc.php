<?php
/**
 * auth.inc.php
 * -----------------------------------------------------------------------
 * Central authentication & role-based access control (RBAC) helper.
 *
 * Include this file at the very top of any page/handler that must be
 * restricted to logged-in users and/or a specific role, e.g.:
 *
 *   require_once "../includes/auth.inc.php";
 *   require_role('student');
 *
 * or, if a page should be reachable by more than one role:
 *
 *   require_role(['recruiter', 'lecturer']);
 *
 * This file assumes it is always included from a script that is exactly
 * one folder below the project root (student/, recruiter/, lecturer/,
 * includes/), matching the "../login.php" redirect used elsewhere in
 * this project.
 * -----------------------------------------------------------------------
 */

// Start the session if one isn't already active.
// (Safe to include this file even if session_start() was already called.)
if (session_status() === PHP_SESSION_NONE) {
   require_once __DIR__ . "/session_init.php"; 
}

/**
 * Ensures the visitor is logged in.
 * Redirects to the login page and stops execution if there is no
 * active, valid session.
 */
function require_login(): void {
    if (!isset($_SESSION['userID']) || !isset($_SESSION['role'])) {
        header("Location: ../login.php");
        exit();
    }
}

/**
 * Ensures the logged-in user holds one of the allowed roles.
 * Implicitly requires login first.
 *
 * @param string|string[] $allowedRoles e.g. 'student' or ['recruiter','lecturer']
 */
function require_role($allowedRoles): void {
    require_login();

    $allowedRoles = (array) $allowedRoles;

    // Strict comparison so role values must match exactly (e.g. "student").
    if (!in_array($_SESSION['role'], $allowedRoles, true)) {
        http_response_code(403);
        die("Access denied: you do not have permission to view this page.");
    }
}
