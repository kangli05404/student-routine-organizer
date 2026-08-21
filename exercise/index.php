<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Fixed Monthly Calorie Goal
|--------------------------------------------------------------------------
*/

$monthlyCalorieGoal = 10000;

$monthlyCaloriesStatement = $pdo->prepare(
    "SELECT
        COALESCE(
            SUM(calories_burned),
            0
        ) AS monthly_calories
     FROM exercise_records
     WHERE user_id = ?
       AND exercise_date >=
           DATE_FORMAT(CURDATE(), '%Y-%m-01')
       AND exercise_date <= CURDATE()"
);

$monthlyCaloriesStatement->execute([
    $userId
]);

$monthlyCaloriesResult =
    $monthlyCaloriesStatement->fetch();

$monthlyCalories =
    (int) $monthlyCaloriesResult['monthly_calories'];

$actualGoalPercentage = (int) round(
    ($monthlyCalories / $monthlyCalorieGoal) * 100
);

$goalPercentage =
    min($actualGoalPercentage, 100);

$goalBarWidth =
    $goalPercentage;

$exceededCalories = max(
    0,
    $monthlyCalories - $monthlyCalorieGoal
);

$remainingCalories = max(
    0,
    $monthlyCalorieGoal - $monthlyCalories
);

/*
|--------------------------------------------------------------------------
| Undo Delete
|--------------------------------------------------------------------------
*/

$undoAvailable = false;
$undoSecondsRemaining = 0;
$undoTimeLimit = 20;

if (
    !empty($_SESSION['last_deleted_exercise']) &&
    is_array($_SESSION['last_deleted_exercise']) &&
    !empty($_SESSION['undo_exercise_token'])
) {
    $deletedExercise =
        $_SESSION['last_deleted_exercise'];

    $deletedAt =
        (int) ($deletedExercise['deleted_at'] ?? 0);

    $deletedUserId =
        (int) ($deletedExercise['user_id'] ?? 0);

    $elapsed =
        time() - $deletedAt;

    if (
        $deletedAt > 0 &&
        $deletedUserId === $userId &&
        $elapsed <= $undoTimeLimit
    ) {
        $undoAvailable = true;

        $undoSecondsRemaining = max(
            0,
            $undoTimeLimit - $elapsed
        );
    } else {
        unset(
            $_SESSION['last_deleted_exercise'],
            $_SESSION['undo_exercise_token']
        );
    }
}

/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

$sort =
    $_GET['sort'] ?? 'newest';

$sortOptions = [
    'newest' =>
        'exercise_date DESC, exercise_id DESC',

    'longest' =>
        'duration_minutes DESC, exercise_date DESC',

    'highest_calories' =>
        'calories_burned DESC, exercise_date DESC'
];

if (!array_key_exists($sort, $sortOptions)) {
    $sort = 'newest';
}

$orderBy =
    $sortOptions[$sort];

/*
|--------------------------------------------------------------------------
| Exercise Summary
|--------------------------------------------------------------------------
*/

$summaryStatement = $pdo->prepare(
    'SELECT
        COUNT(*) AS total_workouts,

        COALESCE(
            SUM(duration_minutes),
            0
        ) AS total_minutes,

        COALESCE(
            SUM(calories_burned),
            0
        ) AS total_calories,

        COALESCE(
            SUM(
                CASE
                    WHEN exercise_date >=
                        DATE_SUB(
                            CURDATE(),
                            INTERVAL WEEKDAY(CURDATE()) DAY
                        )
                    AND exercise_date <= CURDATE()
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS workouts_this_week

     FROM exercise_records
     WHERE user_id = ?'
);

$summaryStatement->execute([
    $userId
]);

$summary =
    $summaryStatement->fetch();

/*
|--------------------------------------------------------------------------
| Exercise Records
|--------------------------------------------------------------------------
*/

$statement = $pdo->prepare(
    'SELECT
        exercise_id,
        activity_type,
        duration_minutes,
        calories_burned,
        exercise_date
     FROM exercise_records
     WHERE user_id = ?
     ORDER BY ' . $orderBy
);

$statement->execute([
    $userId
]);

$exercises =
    $statement->fetchAll();

/*
|--------------------------------------------------------------------------
| HTML Escape Helper
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle =
    'Exercise Tracker';

$activePage =
    'exercise';

$pageStylesheet =
    'exercise.css';

require_once __DIR__ . '/../includes/header.php';

?>

<section class="exercise-header">
    <img class="exercise-mascot" src="../assets/images/exercise-mascot.avif" alt="Exercise mascot">

    <div>
        <h1>
            My Exercise Records
        </h1>

        <p class="page-description">
            Keep track of your workouts, duration,
            and calories burned.
        </p>
    </div>

    <a class="button" href="create.php">
        + Add Exercise Record
    </a>
</section>

<section class="exercise-summary">
    <article class="summary-card workouts-card">
        <span class="summary-icon">
            🏋️
        </span>

        <div>
            <strong>
                <?= number_format(
                    (int) $summary['total_workouts']
                ) ?>
            </strong>

            <span>
                Total Workouts
            </span>
        </div>
    </article>

    <article class="summary-card minutes-card">
        <span class="summary-icon">
            ⏱️
        </span>

        <div>
            <strong>
                <?= number_format(
                    (int) $summary['total_minutes']
                ) ?>
            </strong>

            <span>
                Total Minutes
            </span>
        </div>
    </article>

    <article class="summary-card calories-card">
        <span class="summary-icon">
            🔥
        </span>

        <div>
            <strong>
                <?= number_format(
                    (int) $summary['total_calories']
                ) ?>
            </strong>

            <span>
                Calories Burned
            </span>
        </div>
    </article>

    <article class="summary-card weekly-card">
        <span class="summary-icon">
            📅
        </span>

        <div>
            <strong>
                <?= number_format(
                    (int) $summary['workouts_this_week']
                ) ?>
            </strong>

            <span>
                Workouts This Week
            </span>
        </div>
    </article>
</section>

<section class="monthly-goal-card">
    <div class="goal-header">
        <div>
            <span class="goal-label">
                Monthly Calorie Goal
            </span>

            <h2>
                <?= e(date('F Y')) ?>
            </h2>
        </div>

        <div class="fixed-goal">
            Goal:

            <strong>
                <?= number_format(
                    $monthlyCalorieGoal
                ) ?>
                kcal
            </strong>
        </div>
    </div>

    <div class="goal-progress-information">
        <span>
            <?= number_format($monthlyCalories) ?>

            of

            <?= number_format(
                $monthlyCalorieGoal
            ) ?>

            kcal
        </span>

        <strong>
            <?= number_format($goalPercentage) ?>%
        </strong>
    </div>

    <div class="goal-progress-track" role="progressbar" aria-label="Monthly calorie goal progress" aria-valuemin="0"
        aria-valuemax="100" aria-valuenow="<?= e($goalBarWidth) ?>">
        <div class="goal-progress-bar" style="width: <?= e($goalBarWidth) ?>%"></div>
    </div>

    <div class="goal-stats">
        <?php if (
            $monthlyCalories >= $monthlyCalorieGoal
        ): ?>
            <p class="goal-completed">
                Congratulations! You achieved your
                monthly calorie goal.
            </p>
        <?php else: ?>
            <p>
                You need another

                <strong>
                    <?= number_format(
                        $remainingCalories
                    ) ?>
                    kcal
                </strong>

                to achieve your goal.
            </p>
        <?php endif; ?>
    </div>
</section>

<?php if (isset($_GET['deleted'])): ?>
    <div class="success-message delete-success-message">
        <div class="delete-message-content">
            <span>
                Exercise record
                <strong>deleted</strong>
                successfully.
            </span>

            <?php if ($undoAvailable): ?>
                <form class="undo-form" method="post" action="undo.php">
                    <input type="hidden" name="csrf_token" value="<?= e(
                        $_SESSION['undo_exercise_token']
                    ) ?>">

                    <button class="undo-button" type="submit">
                        ↶ Undo
                    </button>
                </form>

                <span class="undo-time">
                    Available for
                    <?= e($undoSecondsRemaining) ?>
                    seconds
                </span>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($_GET['restored'])): ?>
    <p class="success-message">
        Exercise record
        <strong>restored</strong>
        successfully.
    </p>
<?php endif; ?>

<?php if (isset($_GET['undo_expired'])): ?>
    <p class="info-message">
        The <strong>Undo</strong> period has expired.
        The exercise record can no longer be restored.
    </p>
<?php endif; ?>

<?php if (isset($_GET['undo_error'])): ?>
    <p class="error-message">
        Unable to restore the exercise record.
        Please try again.
    </p>
<?php endif; ?>

<?php if (isset($_GET['updated'])): ?>
    <p class="success-message">
        Exercise record
        <strong>updated</strong>
        successfully.
    </p>
<?php endif; ?>

<?php if (isset($_GET['created'])): ?>
    <p class="success-message">
        Exercise record
        <strong>added</strong>
        successfully.
    </p>
<?php endif; ?>

<?php if (!$exercises): ?>
    <div class="empty-state">
        <h2>
            No exercise records yet
        </h2>

        <p>
            Add your first workout to begin tracking
            your exercise progress.
        </p>
    </div>
<?php else: ?>
    <div class="table-toolbar">
        <form class="sort-form" method="get">
            <label for="sort">
                Sort records by:
            </label>

            <select id="sort" name="sort" onchange="this.form.submit()">
                <option value="newest" <?= $sort === 'newest'
                    ? 'selected'
                    : '' ?>> Newest Date </option>

                <option value="longest" <?= $sort === 'longest'
                    ? 'selected'
                    : '' ?>> Longest Duration </option>

                <option value="highest_calories" <?= $sort === 'highest_calories'
                    ? 'selected'
                    : '' ?>> Highest Calories
                </option>
            </select>

            <noscript>
                <button class="sort-button" type="submit">
                    Apply
                </button>
            </noscript>
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Activity</th>
                    <th>Duration</th>
                    <th>Calories Burned</th>
                    <th>Exercise Date</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach (
                    $exercises as $exercise
                ): ?>
                    <tr>
                        <td>
                            <span class="activity-name">
                                <?= e(
                                    $exercise['activity_type']
                                ) ?>
                            </span>
                        </td>

                        <td>
                            <?= e(
                                $exercise['duration_minutes']
                            ) ?>
                            minutes
                        </td>

                        <td>
                            <?= e(
                                $exercise['calories_burned']
                            ) ?>
                            kcal
                        </td>

                        <td>
                            <?= e(
                                date(
                                    'd M Y',
                                    strtotime(
                                        $exercise['exercise_date']
                                    )
                                )
                            ) ?>
                        </td>

                        <td>
                            <div class="action-buttons">
                                <a class="action-link edit-link" href="edit.php?id=<?= e(
                                    $exercise['exercise_id']
                                ) ?>">
                                    Edit
                                </a>

                                <a class="action-link delete-link" href="delete.php?id=<?= e(
                                    $exercise['exercise_id']
                                ) ?>">
                                    Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php

require_once __DIR__ . '/../includes/footer.php';

?>