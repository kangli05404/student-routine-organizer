<?php

$pageTitle = $pageTitle ?? 'Student Routine Organizer';
$activePage = $activePage ?? '';
$pageStylesheet = $pageStylesheet ?? null;

$baseUrl = '/student_routine_organizer';

$sharedStylesheetPath =
    __DIR__ . '/../assets/css/shared.css';

$sharedStylesheetVersion =
    file_exists($sharedStylesheetPath)
    ? filemtime($sharedStylesheetPath)
    : time();

function navClass($page, $activePage)
{
    return $page === $activePage ? 'active' : '';
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

    <!-- Shared layout and navigation CSS -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/shared.css?v=<?= $sharedStylesheetVersion ?>">

    <!-- Current module CSS -->
    <?php if ($pageStylesheet): ?>
        <?php
        $safeStylesheet = basename($pageStylesheet);

        $pageStylesheetPath =
            __DIR__ . '/../assets/css/' . $safeStylesheet;

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
            <a class="brand" href="<?= $baseUrl ?>/index.php">
                Student Routine Organizer
            </a>

            <nav class="nav-links" aria-label="Main navigation">
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

                <a class="logout-link" href="<?= $baseUrl ?>/logout.php">
                    Logout
                </a>
            </nav>
        </div>
    </header>

    <main class="page-container">