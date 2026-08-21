<?php

require_once __DIR__ . '/auth.php';

startSecureSession();

$pageTitle =
    $pageTitle ?? 'Student Routine Organizer';

$activePage =
    $activePage ?? '';

$pageStylesheet =
    $pageStylesheet ?? null;

$baseUrl =
    '/student-routine-organizer';

$homeUrl = isAdmin()
    ? $baseUrl . '/admin/index.php'
    : $baseUrl . '/index.php';

$sharedStylesheetPath =
    __DIR__ . '/../assets/css/shared.css';

$sharedStylesheetVersion =
    file_exists($sharedStylesheetPath)
    ? filemtime($sharedStylesheetPath)
    : time();

function navClass($page, $activePage)
{
    return $page === $activePage
        ? 'active'
        : '';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars(
            $pageTitle,
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </title>

    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/shared.css?v=<?= $sharedStylesheetVersion ?>">

    <?php if ($pageStylesheet): ?>
        <?php

        $safeStylesheet =
            basename($pageStylesheet);

        $pageStylesheetPath =
            __DIR__ . '/../assets/css/' .
            $safeStylesheet;

        $pageStylesheetVersion =
            file_exists($pageStylesheetPath)
            ? filemtime($pageStylesheetPath)
            : time();

        ?>

        <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/<?= htmlspecialchars(
              $safeStylesheet,
              ENT_QUOTES,
              'UTF-8'
          ) ?>?v=<?= $pageStylesheetVersion ?>">
    <?php endif; ?>
</head>

<body>
    <header class="site-header">
        <div class="navbar">
            <a class="brand" href="<?= $homeUrl ?>">
                Student Routine Organizer
            </a>

            <nav class="nav-links" aria-label="Main navigation">
                <?php if (isAdmin()): ?>
                    <a class="<?= navClass(
                        'admin',
                        $activePage
                    ) ?>" href="<?= $baseUrl ?>/admin/index.php">
                        Admin Dashboard
                    </a>

                    <a class="<?= navClass(
                        'profile',
                        $activePage
                    ) ?>" href="<?= $baseUrl ?>/profile.php">
                        Profile
                    </a>
                <?php else: ?>
                    <a class="<?= navClass(
                        'dashboard',
                        $activePage
                    ) ?>" href="<?= $baseUrl ?>/index.php">
                        Dashboard
                    </a>

                    <a class="<?= navClass(
                        'exercise',
                        $activePage
                    ) ?>" href="<?= $baseUrl ?>/exercise/index.php">
                        Exercise
                    </a>

                    <a class="<?= navClass(
                        'diary',
                        $activePage
                    ) ?>" href="<?= $baseUrl ?>/diary/index.php">
                        Diary
                    </a>

                    <a class="<?= navClass(
                        'money',
                        $activePage
                    ) ?>" href="<?= $baseUrl ?>/money/index.php">
                        Money
                    </a>

                    <a class="<?= navClass(
                        'habit',
                        $activePage
                    ) ?>" href="<?= $baseUrl ?>/habit/index.php">
                        Habits
                    </a>

                    <a class="<?= navClass(
                        'profile',
                        $activePage
                    ) ?>" href="<?= $baseUrl ?>/profile.php">
                        Profile
                    </a>
                <?php endif; ?>

                <a class="logout-link" href="<?= $baseUrl ?>/logout.php">
                    Logout
                </a>
            </nav>
        </div>
    </header>

    <main class="page-container">