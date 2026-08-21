<?php

require_once __DIR__ . '/includes/auth.php';

requireStudent();

$userName =
    $_SESSION['user_name'] ?? 'Student';

$userRole =
    $_SESSION['user_role'] ?? 'student';

$currentDate = new DateTime(
    'now',
    new DateTimeZone('Asia/Kuala_Lumpur')
);

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
$pageStylesheet = 'dashboard.css';

require_once __DIR__ . '/includes/header.php';
?>

<section class="dashboard-hero">
    <div class="welcome-content">
        <span class="welcome-label">
            STUDENT ROUTINE ORGANIZER
        </span>

        <h1>
            Welcome back,
            <?= htmlspecialchars(
                $userName,
                ENT_QUOTES,
                'UTF-8'
            ) ?>!
        </h1>

        <p>
            Manage your health, reflections, finances,
            and habits from one place.
        </p>

        <div class="user-details">
            <span>
                <?= htmlspecialchars(
                    $currentDate->format('l, d F Y'),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </span>

            <span class="role-badge">
                <?= htmlspecialchars(
                    ucfirst($userRole),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </span>
        </div>
    </div>

    <div class="hero-decoration" aria-hidden="true">
        ✦
    </div>
</section>

<section class="module-section">
    <div class="section-heading">
        <div>
            <span class="section-label">
                YOUR ROUTINE
            </span>

            <h2>Choose a Module</h2>
        </div>

        <p>
            Select an area you would like to manage today.
        </p>
    </div>

    <div class="module-grid">
        <a class="module-card exercise-module" href="<?= $baseUrl ?>/exercise/index.php">
            <div class="module-icon" aria-hidden="true">
                🏋️
            </div>

            <div class="module-content">
                <span class="module-number">01</span>

                <h3>Exercise Tracker</h3>

                <p>
                    Record workouts, duration,
                    calories burned, and exercise dates.
                </p>

                <span class="module-link">
                    Open Exercise Tracker
                    <span aria-hidden="true">→</span>
                </span>
            </div>
        </a>

        <a class="module-card diary-module" href="<?= $baseUrl ?>/diary/index.php">
            <div class="module-icon" aria-hidden="true">
                ✍️
            </div>

            <div class="module-content">
                <span class="module-number">02</span>

                <h3>Diary Journal</h3>

                <p>
                    Record personal thoughts,
                    experiences, moods, and reflections.
                </p>

                <span class="module-link">
                    Open Diary Journal
                    <span aria-hidden="true">→</span>
                </span>
            </div>
        </a>

        <a class="module-card money-module" href="<?= $baseUrl ?>/money/index.php">
            <div class="module-icon" aria-hidden="true">
                💰
            </div>

            <div class="module-content">
                <span class="module-number">03</span>

                <h3>Money Tracker</h3>

                <p>
                    Monitor income, expenses,
                    categories, and financial activity.
                </p>

                <span class="module-link">
                    Open Money Tracker
                    <span aria-hidden="true">→</span>
                </span>
            </div>
        </a>

        <a class="module-card habit-module" href="<?= $baseUrl ?>/habit/index.php">
            <div class="module-icon" aria-hidden="true">
                ✅
            </div>

            <div class="module-content">
                <span class="module-number">04</span>

                <h3>Habit Tracker</h3>

                <p>
                    Create positive habits,
                    monitor completion, and track progress.
                </p>

                <span class="module-link">
                    Open Habit Tracker
                    <span aria-hidden="true">→</span>
                </span>
            </div>
        </a>
    </div>
</section>

<?php if ($userRole === 'admin'): ?>
    <section class="admin-panel">
        <div>
            <span class="section-label">ADMIN</span>
            <h2>Administration</h2>

            <p>
                View registered users and basic
                system summaries.
            </p>
        </div>

        <a class="admin-button" href="<?= $baseUrl ?>/admin/index.php">
            Open Admin Panel
        </a>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>