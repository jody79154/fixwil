<?php
/**
 * remember_me.inc.php
 * -----------------------------------------------------------------------
 * Cookie helpers shared by login, logout and session_init.
 *
 * "Remember me" uses a random selector + validator pair:
 *   - the cookie holds  selector:validator
 *   - the database (remember_tokens) holds selector + SHA-256(validator)
 * so a leaked database can't be turned into working cookies, and every
 * token can be revoked server-side (logout, password reset).
 * -----------------------------------------------------------------------
 */

const REMEMBER_COOKIE = 'remember_me';
const REMEMBER_DAYS   = 30;

/** True when the request arrived over HTTPS (directly or via a proxy/load balancer). */
function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

/** One set of cookie attributes, used for setting AND deleting the cookie. */
function remember_cookie_options(int $expires): array
{
    return [
        'expires'  => $expires,
        'path'     => '/',
        'domain'   => '',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Strict',
    ];
}

/** Put a user into the session (used by normal login and remember-me login). */
function start_user_session(array $user): void
{
    session_regenerate_id(true);   // prevents session fixation
    $_SESSION['userID']     = $user['id'];
    $_SESSION['fullName']   = $user['fullName'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['role']       = $user['role'];
    $_SESSION['company']    = $user['company'];
    $_SESSION['profilePic'] = $user['profilePic'] ?? null;
}

/** Create a remember-me token and send the cookie. */
function remember_me_issue(PDO $pdo, int $userID): void
{
    $selector  = bin2hex(random_bytes(12));   // 24 hex chars
    $validator = bin2hex(random_bytes(32));   // 64 hex chars
    $expires   = time() + REMEMBER_DAYS * 86400;

    $pdo->exec("DELETE FROM remember_tokens WHERE expiresAt < NOW()");   // tidy up

    $stmt = $pdo->prepare(
        "INSERT INTO remember_tokens (userID, selector, validatorHash, expiresAt)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$userID, $selector, hash('sha256', $validator), date('Y-m-d H:i:s', $expires)]);

    setcookie(REMEMBER_COOKIE, $selector . ':' . $validator, remember_cookie_options($expires));
}

/** Delete this browser's token (if any) and expire the cookie. */
function remember_me_clear(PDO $pdo): void
{
    if (!isset($_COOKIE[REMEMBER_COOKIE])) {
        return;
    }
    $selector = explode(':', (string) $_COOKIE[REMEMBER_COOKIE], 2)[0];
    $pdo->prepare("DELETE FROM remember_tokens WHERE selector = ?")->execute([$selector]);

    setcookie(REMEMBER_COOKIE, '', remember_cookie_options(time() - 42000));
    unset($_COOKIE[REMEMBER_COOKIE]);
}

/** Revoke every remember-me token for a user (e.g. after a password reset). */
function remember_me_revoke_all(PDO $pdo, int $userID): void
{
    $pdo->prepare("DELETE FROM remember_tokens WHERE userID = ?")->execute([$userID]);
}

/** Called by session_init.php when there is a cookie but no active login. */
function remember_me_login(PDO $pdo): void
{
    $parts = explode(':', (string) ($_COOKIE[REMEMBER_COOKIE] ?? ''));
    if (count($parts) !== 2 || !ctype_xdigit($parts[0]) || !ctype_xdigit($parts[1])) {
        remember_me_clear($pdo);
        return;
    }
    [$selector, $validator] = $parts;

    $stmt = $pdo->prepare(
        "SELECT t.validatorHash, t.expiresAt, u.*
           FROM remember_tokens t
           JOIN users u ON u.id = t.userID
          WHERE t.selector = ? AND u.isVerified = 1"
    );
    $stmt->execute([$selector]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || strtotime($row['expiresAt']) < time()) {
        remember_me_clear($pdo);
        return;
    }

    if (!hash_equals($row['validatorHash'], hash('sha256', $validator))) {
        // Right selector, wrong validator: the cookie may have been stolen.
        remember_me_revoke_all($pdo, (int) $row['id']);
        remember_me_clear($pdo);
        return;
    }

    start_user_session($row);
}
