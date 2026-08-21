<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$dashboardUrl = isAdmin()
    ? '/student-routine-organizer/admin/index.php'
    : '/student-routine-organizer/index.php';

$userId = (int) $_SESSION['user_id'];

function profileEscape($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

/*
|--------------------------------------------------------------------------
| Retrieve Current User
|--------------------------------------------------------------------------
*/

$statement = $pdo->prepare(
    'SELECT
        id,
        name,
        email,
        password_hash,
        role,
        created_at
     FROM users
     WHERE id = ?
     LIMIT 1'
);

$statement->execute([$userId]);
$user = $statement->fetch();

if (!$user) {
    session_destroy();

    header('Location: login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Form Variables
|--------------------------------------------------------------------------
*/

$profileErrors = [];
$passwordErrors = [];

$profileName = $user['name'];
$profileEmail = $user['email'];

$profileRole =
    $user['role'] ?? 'student';

$activePanel =
    (
        ($_POST['action'] ?? '') === 'change_password'
        || isset($_GET['password_updated'])
    )
    ? 'security'
    : 'details';

/*
|--------------------------------------------------------------------------
| Process Forms
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (!isValidAuthToken($submittedToken)) {
        if ($action === 'change_password') {
            $passwordErrors[] =
                'Invalid request. Please refresh the page and try again.';
        } else {
            $profileErrors[] =
                'Invalid request. Please refresh the page and try again.';
        }
    } elseif ($action === 'update_profile') {
        /*
        |--------------------------------------------------------------------------
        | Update Personal Details
        |--------------------------------------------------------------------------
        */

        $profileName = trim($_POST['name'] ?? '');
        $profileEmail = trim($_POST['email'] ?? '');

        if ($profileName === '') {
            $profileErrors[] = 'Full name is required.';
        } elseif (strlen($profileName) < 2) {
            $profileErrors[] =
                'Full name must contain at least 2 characters.';
        } elseif (strlen($profileName) > 100) {
            $profileErrors[] =
                'Full name cannot exceed 100 characters.';
        }

        if ($profileEmail === '') {
            $profileErrors[] = 'Email address is required.';
        } elseif (!filter_var($profileEmail, FILTER_VALIDATE_EMAIL)) {
            $profileErrors[] =
                'Please enter a valid email address.';
        } elseif (strlen($profileEmail) > 150) {
            $profileErrors[] =
                'Email address cannot exceed 150 characters.';
        }

        if (!$profileErrors) {
            $emailCheck = $pdo->prepare(
                'SELECT id
                 FROM users
                 WHERE email = ?
                   AND id <> ?
                 LIMIT 1'
            );

            $emailCheck->execute([
                $profileEmail,
                $userId
            ]);

            if ($emailCheck->fetch()) {
                $profileErrors[] =
                    'An account with this email already exists.';
            }
        }

        if (!$profileErrors) {
            $update = $pdo->prepare(
                'UPDATE users
                 SET name = ?,
                     email = ?
                 WHERE id = ?'
            );

            $update->execute([
                $profileName,
                $profileEmail,
                $userId
            ]);

            $_SESSION['user_name'] = $profileName;
            $_SESSION['user_email'] = $profileEmail;

            header('Location: profile.php?updated=1');
            exit;
        }
    } elseif ($action === 'change_password') {
        /*
        |--------------------------------------------------------------------------
        | Change Password
        |--------------------------------------------------------------------------
        */

        $activePanel = 'security';

        $currentPassword =
            $_POST['current_password'] ?? '';

        $newPassword =
            $_POST['new_password'] ?? '';

        $confirmNewPassword =
            $_POST['confirm_new_password'] ?? '';

        if ($currentPassword === '') {
            $passwordErrors[] =
                'Current password is required.';
        }

        if ($newPassword === '') {
            $passwordErrors[] =
                'New password is required.';
        } elseif (strlen($newPassword) < 8) {
            $passwordErrors[] =
                'New password must contain at least 8 characters.';
        }

        if ($confirmNewPassword === '') {
            $passwordErrors[] =
                'Please confirm your new password.';
        } elseif ($newPassword !== $confirmNewPassword) {
            $passwordErrors[] =
                'The new password confirmation does not match.';
        }

        if (
            $currentPassword !== ''
            && !password_verify(
                $currentPassword,
                $user['password_hash']
            )
        ) {
            $passwordErrors[] =
                'The current password is incorrect.';
        }

        if (
            $currentPassword !== ''
            && $newPassword !== ''
            && $currentPassword === $newPassword
        ) {
            $passwordErrors[] =
                'The new password must be different from the current password.';
        }

        if (!$passwordErrors) {
            $newPasswordHash =
                password_hash(
                    $newPassword,
                    PASSWORD_DEFAULT
                );

            $updatePassword = $pdo->prepare(
                'UPDATE users
                 SET password_hash = ?
                 WHERE id = ?'
            );

            $updatePassword->execute([
                $newPasswordHash,
                $userId
            ]);

            session_regenerate_id(true);

            header(
                'Location: profile.php?password_updated=1'
            );
            exit;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Profile Display Information
|--------------------------------------------------------------------------
*/

$nameParts = preg_split(
    '/\s+/',
    trim($profileName)
);

$initials = '';

if (!empty($nameParts[0])) {
    $initials .= strtoupper(
        substr($nameParts[0], 0, 1)
    );
}

if (!empty($nameParts[1])) {
    $initials .= strtoupper(
        substr($nameParts[1], 0, 1)
    );
}

if ($initials === '') {
    $initials = 'U';
}

$memberSince = !empty($user['created_at'])
    ? date(
        'F Y',
        strtotime($user['created_at'])
    )
    : 'Not available';

$pageTitle = 'My Profile';
$activePage = 'profile';
$pageStylesheet = 'profile.css';

require_once __DIR__ . '/includes/header.php';
?>

<section class="profile-hero">
    <div class="profile-avatar" aria-hidden="true">
        <?= profileEscape($initials) ?>
    </div>

    <div class="profile-identity">
        <p class="profile-label">
            My Account
        </p>

        <h1>
            <?= profileEscape($profileName) ?>
        </h1>

        <p class="profile-email">
            <?= profileEscape($profileEmail) ?>
        </p>

        <p class="profile-role">
            <span class="profile-role-badge profile-role-<?= profileEscape(
                $profileRole
            ) ?>">
                <?= profileEscape(
                    ucfirst($profileRole)
                ) ?>
            </span>
        </p>

        <p class="member-since">
            Member since
            <strong>
                <?= profileEscape($memberSince) ?>
            </strong>
        </p>
    </div>
</section>

<section class="profile-content">
    <div class="profile-tabs" role="tablist" aria-label="Profile settings">
        <button class="profile-tab <?= $activePanel === 'details'
            ? 'active'
            : '' ?>" type="button" role="tab" data-profile-tab="details" aria-controls="details-panel" aria-selected="<?= $activePanel === 'details'
              ? 'true'
              : 'false' ?>">
            Personal Details
        </button>

        <button class="profile-tab <?= $activePanel === 'security'
            ? 'active'
            : '' ?>" type="button" role="tab" data-profile-tab="security" aria-controls="security-panel" aria-selected="<?= $activePanel === 'security'
              ? 'true'
              : 'false' ?>">
            Password &amp; Security
        </button>
    </div>

    <div id="details-panel" class="profile-panel <?= $activePanel === 'details'
        ? 'active'
        : '' ?>" role="tabpanel" data-profile-panel="details">
        <div class="panel-heading">
            <h2>Personal Details</h2>

            <p>
                Update your name and email address.
            </p>
        </div>

        <?php if (isset($_GET['updated'])): ?>
            <div class="profile-message success-message" role="status">
                Profile details
                <strong>updated</strong>
                successfully.
            </div>
        <?php endif; ?>

        <?php if ($profileErrors): ?>
            <div class="profile-message error-message" role="alert">
                <strong>
                    Please correct the following:
                </strong>

                <ul>
                    <?php foreach ($profileErrors as $error): ?>
                        <li>
                            <?= profileEscape($error) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="profile-form">
            <input type="hidden" name="action" value="update_profile">

            <input type="hidden" name="csrf_token" value="<?= profileEscape(getAuthToken()) ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">
                        Full name
                    </label>

                    <input type="text" id="name" name="name" maxlength="100" autocomplete="name"
                        value="<?= profileEscape($profileName) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">
                        Email address
                    </label>

                    <input type="email" id="email" name="email" maxlength="150" autocomplete="email"
                        value="<?= profileEscape($profileEmail) ?>" required>
                </div>
            </div>

            <div class="form-actions">
                <button class="profile-button save-changes-button" type="submit">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <div id="security-panel" class="profile-panel <?= $activePanel === 'security'
        ? 'active'
        : '' ?>" role="tabpanel" data-profile-panel="security">
        <div class="panel-heading">
            <h2>Change Password</h2>

            <p>
                Confirm your current password before choosing
                a new password.
            </p>
        </div>

        <?php if (isset($_GET['password_updated'])): ?>
            <div class="profile-message success-message" role="status">
                Password
                <strong>updated</strong>
                successfully.
            </div>
        <?php endif; ?>

        <?php if ($passwordErrors): ?>
            <div class="profile-message error-message" role="alert">
                <strong>
                    Please correct the following:
                </strong>

                <ul>
                    <?php foreach ($passwordErrors as $error): ?>
                        <li>
                            <?= profileEscape($error) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="profile-form">
            <input type="hidden" name="action" value="change_password">

            <input type="hidden" name="csrf_token" value="<?= profileEscape(getAuthToken()) ?>">

            <div class="form-group full-width">
                <label for="current_password">
                    Current password
                </label>

                <div class="password-field">
                    <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                        required>

                    <button class="password-toggle" type="button" data-password-toggle="current_password"
                        aria-label="Show current password" aria-pressed="false">
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
            </div>

            <div class="form-grid password-grid">
                <div class="form-group">
                    <label for="new_password">
                        New password
                    </label>

                    <div class="password-field">
                        <input type="password" id="new_password" name="new_password" minlength="8"
                            autocomplete="new-password" required>

                        <button class="password-toggle" type="button" data-password-toggle="new_password"
                            aria-label="Show new password" aria-pressed="false">
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
                </div>

                <div class="form-group">
                    <label for="confirm_new_password">
                        Confirm new password
                    </label>

                    <div class="password-field">
                        <input type="password" id="confirm_new_password" name="confirm_new_password" minlength="8"
                            autocomplete="new-password" required>

                        <button class="password-toggle" type="button" data-password-toggle="confirm_new_password"
                            aria-label="Show confirm password" aria-pressed="false">
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
                </div>
            </div>

            <p class="password-help">
                Your new password must contain at least
                8 characters.
            </p>

            <div class="form-actions">
                <button class="profile-button" type="submit">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</section>

<script src="<?= $baseUrl ?>/assets/js/profile.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>