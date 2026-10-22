<?php
// Don't let the remember-me cookie log the user straight back in.
define('SKIP_REMEMBER_ME', true);
require_once "includes/session_init.php";
require_once "includes/dbh.inc.php";

// 1) delete this browser's remember-me token (database row + cookie)
remember_me_clear($pdo);

// 2) clear all the session variables
$_SESSION = [];

// 3) Delete the session cookie itself and not just the server-side session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        [
            'expires'  => time() - 42000,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Strict'
        ]
    );
}

// 4) Destroying session data on the server
session_destroy();

// 5) sends the user back to the login
header("Location: login.php");
exit();
