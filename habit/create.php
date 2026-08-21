<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireStudent();

$userId = (int) $_SESSION['user_id'];
$errors = [];

$habitName = '';
$targetFrequency = '';
$completionStatus = 'pending';
$habitDate = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (Your original POST handler logic remains exactly the same) ...
    $habitName = trim($_POST['habit_name'] ?? '');
    $targetFrequency = trim($_POST['target_frequency'] ?? '');
    $completionStatus = trim($_POST['completion_status'] ?? 'pending');
    $habitDate = trim($_POST['habit_date'] ?? '');

    if ($habitName === '') {
        $errors[] = 'Habit name is required.';
    } elseif (strlen($habitName) > 100) {
        $errors[] = 'Habit name cannot exceed 100 characters.';
    }

    if ($targetFrequency === '') {
        $errors[] = 'Target frequency is required.';
    } elseif (strlen($targetFrequency) > 50) {
        $errors[] =
            'Target frequency cannot exceed 50 characters.';
    }

    if (!in_array($completionStatus, ['pending', 'completed'], true)) {
        $errors[] = 'Invalid completion status.';
    }

    $validDate = DateTime::createFromFormat(
        'Y-m-d',
        $habitDate
    );

    if (
        !$validDate ||
        $validDate->format('Y-m-d') !== $habitDate
    ) {
        $errors[] = 'Please enter a valid date.';
    }

    if (!$errors) {
        $statement = $pdo->prepare(
            'INSERT INTO habits (
                user_id,
                habit_name,
                target_frequency,
                completion_status,
                habit_date
            )
            VALUES (?, ?, ?, ?, ?)'
        );

        $statement->execute([
            $userId,
            $habitName,
            $targetFrequency,
            $completionStatus,
            $habitDate
        ]);

        header('Location: index.php?created=1');
        exit;
    }
}

$pageTitle = 'Add Habit';
$activePage = 'habit';
$pageStylesheet = 'habit.css';

require_once __DIR__ . '/../includes/header.php';
?>

<section class="form-page-header">
    <h1>Add Habit</h1>
    <p>
        Set up a new habit you want to build.
    </p>
</section>

<?php if ($errors): ?>
    <div class="error-message" role="alert">
        <strong>Please correct the following:</strong>
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

<!-- Wrapped in a styled card -->
<div class="form-card card">
    <form method="post">
        <div class="form-grid">
            <div class="form-group form-group-full">
                <label for="habit_name">
                    Habit Name
                </label>

                <input id="habit_name" type="text" name="habit_name" list="habit_suggestions" maxlength="100"
                    placeholder="For example, Drink 2L Water" value="<?= htmlspecialchars(
                        $habitName,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>" required>

                <datalist id="habit_suggestions">
                    <option value="Drink 2L Water">
                    <option value="Read 20 Pages">
                    <option value="Sleep Before 11pm">
                    <option value="Meditate">
                    <option value="No Junk Food">
                </datalist>
            </div>

            <div class="form-group">
                <label for="target_frequency">
                    Target Frequency
                </label>

                <input id="target_frequency" type="text" name="target_frequency" maxlength="50"
                    placeholder="For example, Daily or 3x/week" value="<?= htmlspecialchars(
                        $targetFrequency,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>" required>
            </div>

            <div class="form-group">
                <label for="completion_status">
                    Completion Status
                </label>

                <select id="completion_status" name="completion_status">
                    <option value="pending" <?= $completionStatus === 'pending' ? 'selected' : '' ?>>
                        Pending
                    </option>

                    <option value="completed" <?= $completionStatus === 'completed' ? 'selected' : '' ?>>
                        Completed
                    </option>
                </select>
            </div>

            <div class="form-group form-group-full">
                <label for="habit_date">
                    Date
                </label>

                <input id="habit_date" type="date" name="habit_date" value="<?= htmlspecialchars(
                    $habitDate,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <button class="save-button" type="submit">
                Save Habit
            </button>

            <a class="cancel-button" href="index.php">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>