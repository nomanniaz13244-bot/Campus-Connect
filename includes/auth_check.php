<?php
/**
 * Session + Role Guard
 * Include this at the TOP of any protected page.
 *
 * Usage:
 *   require_once '../includes/auth_check.php';
 *   require_role(['admin']);              // only admin
 *   require_role(['admin', 'club_admin']); // admin or club_admin
 *   require_login();                       // any logged-in user
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_role() {
    return $_SESSION['role'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: /campus-connect/auth/login.php");
        exit;
    }
}

function require_role(array $allowed_roles) {
    require_login();
    if (!in_array(current_role(), $allowed_roles, true)) {
        http_response_code(403);
        die("<h2>403 Forbidden</h2><p>You don't have permission to access this page.</p><a href='/campus-connect/index.php'>Go home</a>");
    }
}
