<?php
/**
 * admin/logout.php
 * Aspirian.pk Online Test System
 * Admin logout — destroys session and redirects to admin login
 */

require_once __DIR__ . '/../config.php';

// Destroy the session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();

header('Location: login.php?msg=logged_out');
exit;
