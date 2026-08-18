<?php

require_once __DIR__ . '/includes/auth.php';

startSecureSession();

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $parameters = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        [
            'expires' => time() - 42000,
            'path' => $parameters['path'],
            'domain' => $parameters['domain'],
            'secure' => $parameters['secure'],
            'httponly' => $parameters['httponly'],
            'samesite' => 'Lax'
        ]
    );
}

session_destroy();

header('Location: login.php?logged_out=1');
exit;