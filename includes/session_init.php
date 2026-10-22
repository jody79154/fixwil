<?php
/**
 * session_init.php
 * -----------------------------------------------------------------------
 * The ONE place sessions are started. Every page and handler should
 * require this file instead of calling session_start() directly, so the
 * session cookie always gets the same hardened settings.
 *
 * (A bare session_start() earlier in the request creates the cookie with
 * PHP's weak defaults and the later session_set_cookie_params() is ignored.)
 * -----------------------------------------------------------------------
 */
require_once __DIR__ . '/remember_me.inc.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');   // reject session IDs the server didn't issue
    ini_set('session.use_only_cookies', '1');  // never accept IDs from the URL

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => is_https(),   // true on HTTPS, false on plain http://localhost
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// Logged out but holding a valid "remember me" cookie? Log them back in.
// (logout.php / loginhandler.inc.php define SKIP_REMEMBER_ME to bypass this.)
if (!defined('SKIP_REMEMBER_ME')
    && !isset($_SESSION['userID'])
    && isset($_COOKIE[REMEMBER_COOKIE])) {

    require_once __DIR__ . '/dbh.inc.php';
    $GLOBALS['pdo'] = $pdo;            // keep $pdo global even if included from a function
    remember_me_login($pdo);
}
