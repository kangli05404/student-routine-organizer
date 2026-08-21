<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireStudent();

$userId = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Only allow POST requests
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Check Undo CSRF token
|--------------------------------------------------------------------------
*/

$submittedToken =
    $_POST['csrf_token'] ?? '';

$sessionToken =
    $_SESSION['undo_exercise_token'] ?? '';

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    $submittedToken === '' ||
    $sessionToken === '' ||
    !hash_equals(
        $sessionToken,
        $submittedToken
    )
) {
    header(
        'Location: index.php?undo_error=1'
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| Check whether a deleted exercise exists
|--------------------------------------------------------------------------
*/

if (
    empty($_SESSION['last_deleted_exercise']) ||
    !is_array(
        $_SESSION['last_deleted_exercise']
    )
) {
    header(
        'Location: index.php?undo_expired=1'
    );

    exit;
}

$exercise =
    $_SESSION['last_deleted_exercise'];

/*
|--------------------------------------------------------------------------
| Make sure deleted record belongs to current user
|--------------------------------------------------------------------------
*/

if (
    (int) ($exercise['user_id'] ?? 0)
    !== $userId
) {
    unset(
        $_SESSION['last_deleted_exercise'],
        $_SESSION['undo_exercise_token']
    );

    header(
        'Location: index.php?undo_error=1'
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| Check 20-second Undo time limit
|--------------------------------------------------------------------------
*/

$deletedAt =
    (int) ($exercise['deleted_at'] ?? 0);

$undoTimeLimit = 20;

if (
    $deletedAt <= 0 ||
    (time() - $deletedAt) > $undoTimeLimit
) {
    unset(
        $_SESSION['last_deleted_exercise'],
        $_SESSION['undo_exercise_token']
    );

    header(
        'Location: index.php?undo_expired=1'
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| Restore deleted exercise
|--------------------------------------------------------------------------
*/

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
    $exercise['activity_type'],
    (int) $exercise['duration_minutes'],
    (int) $exercise['calories_burned'],
    $exercise['exercise_date']
]);

/*
|--------------------------------------------------------------------------
| Remove temporary deleted record
|--------------------------------------------------------------------------
|
| This prevents the user from clicking Undo multiple times
| and creating duplicate exercise records.
|
*/

unset(
    $_SESSION['last_deleted_exercise'],
    $_SESSION['undo_exercise_token']
);

/*
|--------------------------------------------------------------------------
| Return to Exercise Tracker
|--------------------------------------------------------------------------
*/

header(
    'Location: index.php?restored=1'
);

exit;