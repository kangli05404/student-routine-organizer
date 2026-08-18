<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];
$errors = [];

$activityType = '';
$durationMinutes = '';
$caloriesBurned = '';
$exerciseDate = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activityType = trim($_POST['activity_type'] ?? '');
    $durationMinutes = trim($_POST['duration_minutes'] ?? '');
    $caloriesBurned = trim($_POST['calories_burned'] ?? '');
    $exerciseDate = trim($_POST['exercise_date'] ?? '');

    if ($activityType === '') {
        $errors[] = 'Activity type is required.';
    } elseif (strlen($activityType) > 100) {
        $errors[] = 'Activity type cannot exceed 100 characters.';
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
        $errors[] = 'Please enter a valid exercise date.';
    }

    if (!$errors) {
        $statement = $pdo->prepare(
            'INSERT INTO exercise_records (
                user_id,
                activity_type,
                duration_minutes,
                calories_burned,
                exercise_date
            )
            VALUES (?, ?, ?, ?, ?)'
        );

        $statement->execute([
            $userId,
            $activityType,
            (int) $durationMinutes,
            (int) $caloriesBurned,
            $exerciseDate
        ]);

        header('Location: index.php?created=1');
        exit;
    }
}

$pageTitle = 'Add Exercise Record';
$activePage = 'exercise';
$pageStylesheet = 'exercise.css';

require_once __DIR__ . '/../includes/header.php';
?>

<section class="form-page-header">
    <h1>Add Exercise Record</h1>

    <p>
        Enter the details of your latest workout.
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
                    placeholder="For example, Jogging" value="<?= htmlspecialchars(
                        $activityType,
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
                    $durationMinutes,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>" required>
            </div>

            <div class="form-group">
                <label for="calories_burned">
                    Calories Burned
                </label>

                <input id="calories_burned" type="number" name="calories_burned" min="0" value="<?= htmlspecialchars(
                    $caloriesBurned,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>" required>
            </div>

            <div class="form-group form-group-full">
                <label for="exercise_date">
                    Exercise Date
                </label>

                <input id="exercise_date" type="date" name="exercise_date" value="<?= htmlspecialchars(
                    $exerciseDate,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <button class="button" type="submit">
                Save Exercise
            </button>

            <a class="cancel-button" href="index.php">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>