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

function requireLogin()
{
    startSecureSession();

    if (empty($_SESSION['user_id'])) {
        $_SESSION['intended_url'] =
            $_SERVER['REQUEST_URI'] ?? null;

        header(
            'Location: /student_routine_organizer/login.php'
        );
        exit;
    }
}

function redirectIfAuthenticated()
{
    startSecureSession();

    if (!empty($_SESSION['user_id'])) {
        header(
            'Location: /student_routine_organizer/exercise/index.php'
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
        && hash_equals($_SESSION['auth_token'], $token);
}