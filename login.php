<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

redirectIfAuthenticated();

$errors = [];

$isPostRequest =
    $_SERVER['REQUEST_METHOD'] === 'POST';

$rememberedEmail =
    $_COOKIE['remembered_email'] ?? '';

$email = trim(
    $_POST['email'] ?? $rememberedEmail
);

$rememberMe = $isPostRequest
    ? isset($_POST['remember_me'])
    : $rememberedEmail !== '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if (
        !isValidAuthToken(
            $_POST['csrf_token'] ?? null
        )
    ) {
        $errors[] =
            'Your session expired. Please try again.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] =
            'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (!$errors) {
        $statement = $pdo->prepare(
            'SELECT
                id,
                name,
                email,
                password_hash,
                role
             FROM users
             WHERE email = ?
             LIMIT 1'
        );

        $statement->execute([$email]);
        $user = $statement->fetch();

        if (
            !$user ||
            !password_verify(
                $password,
                $user['password_hash']
            )
        ) {
            $errors[] =
                'The email or password is incorrect.';
        } else {
            session_regenerate_id(true);

            $_SESSION['user_id'] =
                (int) $user['id'];

            $_SESSION['user_name'] =
                $user['name'];

            $_SESSION['user_email'] =
                $user['email'];

            $_SESSION['user_role'] =
                $user['role'];

            if ($rememberMe) {
                setcookie(
                    'remembered_email',
                    $user['email'],
                    [
                        'expires' =>
                            time() + (30 * 24 * 60 * 60),
                        'path' =>
                            '/student-routine-organizer',
                        'secure' =>
                            !empty($_SERVER['HTTPS']) &&
                            $_SERVER['HTTPS'] !== 'off',
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]
                );
            } else {
                setcookie(
                    'remembered_email',
                    '',
                    [
                        'expires' => time() - 3600,
                        'path' =>
                            '/student-routine-organizer',
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]
                );
            }

            $destination =
                $_SESSION['intended_url']
                ?? '/student-routine-organizer/index.php';

            unset(
                $_SESSION['intended_url'],
                $_SESSION['auth_token']
            );

            header('Location: ' . $destination);
            exit;
        }
    }
}

$cssPath = __DIR__ . '/assets/css/auth.css';
$cssVersion =
    file_exists($cssPath) ? filemtime($cssPath) : time();

$baseUrl = '/student-routine-organizer';
$showNavbarScript = false;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sign In | Student Routine Organizer</title>

    <link rel="stylesheet" href="/student-routine-organizer/assets/css/auth.css?v=<?= $cssVersion ?>">
</head>

<body class="auth-page">
    <main class="auth-shell">
        <a class="auth-brand" href="login.php">
            Student Routine Organizer
        </a>

        <h1>ACCOUNT</h1>

        <nav class="auth-tabs">
            <a class="active" href="login.php">
                SIGN IN
            </a>

            <a href="register.php">
                REGISTER
            </a>
        </nav>

        <section class="auth-card">
            <?php if (isset($_GET['registered'])): ?>
                <div class="auth-message success-message">
                    Account created successfully.
                    You can sign in now.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['logged_out'])): ?>
                <div class="auth-message success-message">
                    You have logged out successfully.
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="auth-message error-message">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li>
                                <?= htmlspecialchars(
                                    $error,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
                    getAuthToken(),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>">

                <div class="auth-field">
                    <label for="email">Email</label>

                    <input id="email" type="email" name="email" autocomplete="email" value="<?= htmlspecialchars(
                        $email,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>" required>
                </div>

                <div class="auth-field password-field">
                    <label for="password">Password</label>

                    <input id="password" type="password" name="password" autocomplete="current-password" required>

                    <button class="password-toggle" type="button" data-password-toggle="password"
                        aria-label="Show password">
                        <svg class="eye-icon eye-open" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>

                        <svg class="eye-icon eye-closed" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 3l18 18" />
                            <path d="M10.6 5.2A10.6 10.6 0 0 1 12 5c6.5 0 10 7 10 7a17.6 17.6 0 0 1-2.1 3.1" />
                            <path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 7 10 7a10 10 0 0 0 4.1-.9" />
                        </svg>
                    </button>
                </div>

                <label class="checkbox-field">
                    <input type="checkbox" name="remember_me" <?= $rememberMe ? 'checked' : '' ?>> <span>Remember my
                        email (optional)</span>
                </label>

                <button class="auth-submit" type="submit">
                    SIGN IN
                </button>
            </form>
        </section>
    </main>

    <script src="<?= $baseUrl ?>/assets/js/auth.js"></script>

    <?php require __DIR__ . '/includes/footer.php'; ?>
</body>

</html>