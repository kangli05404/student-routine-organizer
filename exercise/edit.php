<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];

$exerciseId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$exerciseId) {
    http_response_code(400);
    exit('Invalid exercise record.');
}

$statement = $pdo->prepare(
    'SELECT
        exercise_id,
        activity_type,
        duration_minutes,
        calories_burned,
        exercise_date
     FROM exercise_records
     WHERE exercise_id = ?
       AND user_id = ?'
);

$statement->execute([$exerciseId, $userId]);
$exercise = $statement->fetch();

if (!$exercise) {
    http_response_code(404);
    exit('Exercise record not found.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activityType =
        trim($_POST['activity_type'] ?? '');

    $durationMinutes =
        trim($_POST['duration_minutes'] ?? '');

    $caloriesBurned =
        trim($_POST['calories_burned'] ?? '');

    $exerciseDate =
        trim($_POST['exercise_date'] ?? '');

    if ($activityType === '') {
        $errors[] = 'Activity type is required.';
    } elseif (strlen($activityType) > 100) {
        $errors[] =
            'Activity type cannot exceed 100 characters.';
    }

    if (
        !ctype_digit($durationMinutes) ||
        (int) $durationMinutes <= 0
    ) {
        $errors[] =
            'Duration must be a positive whole number.';
    }

    if (
        !ctype_digit($caloriesBurned) ||
        (int) $caloriesBurned < 0
    ) {
        $errors[] =
            'Calories must be zero or more.';
    }

    $validDate = DateTime::createFromFormat(
        'Y-m-d',
        $exerciseDate
    );

    if (
        !$validDate ||
        $validDate->format('Y-m-d') !== $exerciseDate
    ) {
        $errors[] =
            'Please enter a valid exercise date.';
    }

    if (!$errors) {
        $hasChanges =
            $activityType !== $exercise['activity_type']
            || (int) $durationMinutes
            !== (int) $exercise['duration_minutes']
            || (int) $caloriesBurned
            !== (int) $exercise['calories_burned']
            || $exerciseDate !== $exercise['exercise_date'];

        if (!$hasChanges) {
            $errors[] =
                'No changes detected. Please update at least one field.';
        }
    }

    if (!$errors) {
        $update = $pdo->prepare(
            'UPDATE exercise_records
             SET activity_type = ?,
                 duration_minutes = ?,
                 calories_burned = ?,
                 exercise_date = ?
             WHERE exercise_id = ?
               AND user_id = ?'
        );

        $update->execute([
            $activityType,
            (int) $durationMinutes,
            (int) $caloriesBurned,
            $exerciseDate,
            $exerciseId,
            $userId
        ]);

        header('Location: index.php?updated=1');
        exit;
    }

    $exercise['activity_type'] = $activityType;
    $exercise['duration_minutes'] = $durationMinutes;
    $exercise['calories_burned'] = $caloriesBurned;
    $exercise['exercise_date'] = $exerciseDate;
}

$pageTitle = 'Edit Exercise Record';
$activePage = 'exercise';
$pageStylesheet = 'exercise.css';

require_once __DIR__ . '/../includes/header.php';
?>

<section class="form-page-header">
    <h1>Edit Exercise Record</h1>

    <p>
        Update the details of your selected workout.
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

<div class="form-card">
    <form method="post">
        <div class="form-grid">
            <div class="form-group form-group-full">
                <label for="activity_type">
                    Activity Type
                </label>

                <input id="activity_type" type="text" name="activity_type" list="activity_suggestions" maxlength="100"
                    value="<?= htmlspecialchars(
                        $exercise['activity_type'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>" required>

                <datalist id="activity_suggestions">
                    <option value="Jogging">
                    <option value="Cycling">
                    <option value="Gym Session">
                    <option value="Swimming">
                    <option value="Walking">
                    <option value="Badminton">
                </datalist>
            </div>

            <div class="form-group">
                <label for="duration_minutes">
                    Duration (minutes)
                </label>

                <input id="duration_minutes" type="number" name="duration_minutes" min="1" value="<?= htmlspecialchars(
                    $exercise['duration_minutes'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>" required>
            </div>

            <div class="form-group">
                <label for="calories_burned">
                    Calories Burned
                </label>

                <input id="calories_burned" type="number" name="calories_burned" min="0" value="<?= htmlspecialchars(
                    $exercise['calories_burned'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>" required>
            </div>

            <div class="form-group form-group-full">
                <label for="exercise_date">
                    Exercise Date
                </label>

                <input id="exercise_date" type="date" name="exercise_date" value="<?= htmlspecialchars(
                    $exercise['exercise_date'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <button class="button" type="submit">
                Update Exercise
            </button>

            <a class="cancel-button" href="index.php">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>