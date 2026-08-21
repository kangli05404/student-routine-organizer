<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

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

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (Your original edit.php handler logic remains exactly the same) ...
    $habitName =
        trim($_POST['habit_name'] ?? '');

    $targetFrequency =
        trim($_POST['target_frequency'] ?? '');

    $completionStatus =
        trim($_POST['completion_status'] ?? 'pending');

    $habitDate =
        trim($_POST['habit_date'] ?? '');

    if ($habitName === '') {
        $errors[] = 'Habit name is required.';
    } elseif (strlen($habitName) > 100) {
        $errors[] =
            'Habit name cannot exceed 100 characters.';
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
        $hasChanges =
            $habitName !== $habit['habit_name']
            || $targetFrequency !== $habit['target_frequency']
            || $completionStatus !== $habit['completion_status']
            || $habitDate !== $habit['habit_date'];

        if (!$hasChanges) {
            $errors[] =
                'No changes detected. Please update at least one field.';
        }
    }

    if (!$errors) {
        $update = $pdo->prepare(
            'UPDATE habits
             SET habit_name = ?,
                 target_frequency = ?,
                 completion_status = ?,
                 habit_date = ?
             WHERE habit_id = ?
               AND user_id = ?'
        );

        $update->execute([
            $habitName,
            $targetFrequency,
            $completionStatus,
            $habitDate,
            $habitId,
            $userId
        ]);

        header('Location: index.php?updated=1');
        exit;
    }

    $habit['habit_name'] = $habitName;
    $habit['target_frequency'] = $targetFrequency;
    $habit['completion_status'] = $completionStatus;
    $habit['habit_date'] = $habitDate;
}

$pageTitle = 'Edit Habit';
$activePage = 'habit';
$pageStylesheet = 'habit.css';

require_once __DIR__ . '/../includes/header.php';
?>

<section class="form-page-header">
    <h1>Edit Habit</h1>
    <p>
        Update the details of your selected habit.
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

                <input id="habit_name" type="text" name="habit_name" maxlength="100" value="<?= htmlspecialchars(
                    $habit['habit_name'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>" required>
            </div>

            <div class="form-group">
                <label for="target_frequency">
                    Target Frequency
                </label>

                <input id="target_frequency" type="text" name="target_frequency" maxlength="50" value="<?= htmlspecialchars(
                    $habit['target_frequency'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>" required>
            </div>

            <div class="form-group">
                <label for="completion_status">
                    Completion Status
                </label>

                <select id="completion_status" name="completion_status">
                    <option value="pending" <?= $habit['completion_status'] === 'pending' ? 'selected' : '' ?>>
                        Pending
                    </option>

                    <option value="completed" <?= $habit['completion_status'] === 'completed' ? 'selected' : '' ?>>
                        Completed
                    </option>
                </select>
            </div>

            <div class="form-group form-group-full">
                <label for="habit_date">
                    Date
                </label>

                <input id="habit_date" type="date" name="habit_date" value="<?= htmlspecialchars(
                    $habit['habit_date'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <button class="save-button" type="submit">
                Update Habit
            </button>

            <a class="cancel-button" href="index.php">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>