<?php
require_once __DIR__ . '/config.php';

function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: /iPortfolio/admin/login.php');
        exit;
    }
}

function attemptLogin(string $username, string $password): bool {
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $username;
        return true;
    }
    return false;
}

function logout(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION = [];
    session_destroy();
    header('Location: /iPortfolio/admin/login.php');
    exit;
}
