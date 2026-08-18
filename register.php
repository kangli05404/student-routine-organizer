<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

redirectIfAuthenticated();

$errors = [];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    $confirmPassword =
        $_POST['confirm_password'] ?? '';

    $acceptedTerms =
        isset($_POST['accepted_terms']);

    if (
        !isValidAuthToken(
            $_POST['csrf_token'] ?? null
        )
    ) {
        $errors[] =
            'Your session expired. Please try again.';
    }

    if (
        $name === '' ||
        strlen($name) < 2
    ) {
        $errors[] = 'Please enter your full name.';
    } elseif (strlen($name) > 100) {
        $errors[] =
            'Name cannot exceed 100 characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] =
            'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] =
            'Password must contain at least 8 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] =
            'Password confirmation does not match.';
    }

    if (!$acceptedTerms) {
        $errors[] =
            'You must agree to the Terms and Privacy notice.';
    }

    if (!$errors) {
        $checkUser = $pdo->prepare(
            'SELECT id
             FROM users
             WHERE email = ?
             LIMIT 1'
        );

        $checkUser->execute([$email]);

        if ($checkUser->fetch()) {
            $errors[] =
                'An account with this email already exists.';
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO users (
                    name,
                    email,
                    password_hash,
                    role
                 )
                 VALUES (?, ?, ?, ?)'
            );

            $statement->execute([
                $name,
                $email,
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                ),
                'student'
            ]);

            unset($_SESSION['auth_token']);

            header(
                'Location: login.php?registered=1'
            );
            exit;
        }
    }
}

$cssPath = __DIR__ . '/assets/css/auth.css';
$cssVersion =
    file_exists($cssPath) ? filemtime($cssPath) : time();

$baseUrl = '/student_routine_organizer';
$showNavbarScript = false;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | Student Routine Organizer</title>

    <link rel="stylesheet" href="/student_routine_organizer/assets/css/auth.css?v=<?= $cssVersion ?>">
</head>

<body class="auth-page">
    <main class="auth-shell">
        <a class="auth-brand" href="login.php">
            Student Routine Organizer
        </a>

        <h1>ACCOUNT</h1>

        <nav class="auth-tabs">
            <a href="login.php">
                SIGN IN
            </a>

            <a class="active" href="register.php">
                REGISTER
            </a>
        </nav>

        <section class="auth-card">
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
                    <label for="name">Full name</label>

                    <input id="name" type="text" name="name" maxlength="100" autocomplete="name" value="<?= htmlspecialchars(
                        $name,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>" required>
                </div>

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

                    <input id="password" type="password" name="password" minlength="8" autocomplete="new-password"
                        required>

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

                <div class="auth-field password-field">
                    <label for="confirm_password">
                        Confirm password
                    </label>

                    <input id="confirm_password" type="password" name="confirm_password" minlength="8"
                        autocomplete="new-password" required>

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
                    <input type="checkbox" name="accepted_terms" required>

                    <span>
                        I agree to the Terms &amp; Privacy notice
                    </span>
                </label>

                <button class="auth-submit" type="submit">
                    CREATE ACCOUNT
                </button>
            </form>
        </section>
    </main>

    <script src="<?= $baseUrl ?>/assets/js/auth.js"></script>

    <?php require __DIR__ . '/includes/footer.php'; ?>
</body>

</html>