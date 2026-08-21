<?php

function startSecureSession()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' =>
            !empty($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

function isAuthenticated()
{
    startSecureSession();

    $validRoles = ['student', 'admin'];

    return !empty($_SESSION['user_id'])
        && isset($_SESSION['user_role'])
        && in_array(
            $_SESSION['user_role'],
            $validRoles,
            true
        );
}

function currentUserRole()
{
    startSecureSession();

    return $_SESSION['user_role'] ?? null;
}

function isStudent()
{
    return currentUserRole() === 'student';
}

function isAdmin()
{
    return currentUserRole() === 'admin';
}

function authenticatedHomeUrl()
{
    return isAdmin()
        ? '/student-routine-organizer/admin/index.php'
        : '/student-routine-organizer/index.php';
}

function requireLogin()
{
    if (!isAuthenticated()) {
        $_SESSION['intended_url'] =
            $_SERVER['REQUEST_URI'] ?? null;

        header(
            'Location: /student-routine-organizer/login.php'
        );
        exit;
    }
}

function requireRole($requiredRole)
{
    requireLogin();

    if (currentUserRole() !== $requiredRole) {
        header(
            'Location: ' . authenticatedHomeUrl()
        );
        exit;
    }
}

function requireStudent()
{
    requireRole('student');
}

function requireAdmin()
{
    requireRole('admin');
}

function redirectIfAuthenticated()
{
    if (isAuthenticated()) {
        header(
            'Location: ' . authenticatedHomeUrl()
        );
        exit;
    }
}

function getAuthToken()
{
    startSecureSession();

    if (empty($_SESSION['auth_token'])) {
        $_SESSION['auth_token'] =
            bin2hex(random_bytes(32));
    }

    return $_SESSION['auth_token'];
}

function isValidAuthToken($token)
{
    startSecureSession();

    return is_string($token)
        && isset($_SESSION['auth_token'])
        && hash_equals(
            $_SESSION['auth_token'],
            $token
        );
}