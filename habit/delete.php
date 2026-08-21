<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireStudent();

$userId = (int) $_SESSION['user_id'];

$habitId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$habitId) {
    http_response_code(400);
    exit('Invalid habit record.');
}

$statement = $pdo->prepare(
    'SELECT
        habit_id,
        habit_name,
        target_frequency,
        completion_status,
        habit_date
     FROM habits
     WHERE habit_id = ?
       AND user_id = ?'
);

$statement->execute([$habitId, $userId]);
$habit = $statement->fetch();

if (!$habit) {
    http_response_code(404);
    exit('Habit record not found.');
}

$error = '';

if (empty($_SESSION['delete_habit_token'])) {
    $_SESSION['delete_habit_token'] =
        bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (Your original delete.php handler logic remains exactly the same) ...
    $submittedToken =
        $_POST['csrf_token'] ?? '';

    if (
        !hash_equals(
            $_SESSION['delete_habit_token'],
            $submittedToken
        )
    ) {
        $error = 'Invalid request. Please try again.';
    } else {
        $delete = $pdo->prepare(
            'DELETE FROM habits
             WHERE habit_id = ?
               AND user_id = ?'
        );

        $delete->execute([$habitId, $userId]);

        unset($_SESSION['delete_habit_token']);

        header('Location: index.php?deleted=1');
        exit;
    }
}

$pageTitle = 'Delete Habit';
$activePage = 'habit';
$pageStylesheet = 'habit.css';

require_once __DIR__ . '/../includes/header.php';
?>

<section class="delete-page-header">
    <div class="header-box">
        <h1>Delete Habit</h1>
        <p>Please confirm before permanently deleting this habit.</p>
    </div>
</section>

<?php if ($error): ?>
    <div class="error-message" role="alert">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<div class="delete-card">
    <div class="warning-icon-wrapper" aria-hidden="true">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
            <line x1="12" y1="9" x2="12" y2="13" />
            <line x1="12" y1="17" x2="12.01" y2="17" />
        </svg>
    </div>

    <h2>Are you sure?</h2>
    <p class="delete-warning">
        This habit record will be permanently deleted. This action cannot be undone.
    </p>

    <!-- Clean, Structured Record Card -->
    <div class="record-summary-box">
        <div class="summary-row">
            <span class="summary-label">Habit Name</span>
            <span class="summary-value"><?= htmlspecialchars($habit['habit_name'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <div class="summary-row">
            <span class="summary-label">Frequency</span>
            <span class="summary-value"><?= htmlspecialchars($habit['target_frequency'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <div class="summary-row">
            <span class="summary-label">Status</span>
            <span class="summary-value">
                <span
                    class="status-badge badge-<?= htmlspecialchars($habit['completion_status'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars(ucfirst($habit['completion_status']), ENT_QUOTES, 'UTF-8') ?>
                </span>
            </span>
        </div>

        <div class="summary-row">
            <span class="summary-label">Date Created</span>
            <span
                class="summary-value"><?= htmlspecialchars(date('d M Y', strtotime($habit['habit_date'])), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>

    <form method="post">
        <input type="hidden" name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['delete_habit_token'], ENT_QUOTES, 'UTF-8') ?>">

        <div class="delete-actions">
            <button class="danger-button" type="submit">
                Yes, Delete Habit
            </button>

            <a class="cancel-button" href="index.php">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>