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

/*
|--------------------------------------------------------------------------
| Get selected exercise record
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
     WHERE exercise_id = ?
       AND user_id = ?'
);

$statement->execute([
    $exerciseId,
    $userId
]);

$exercise = $statement->fetch();

if (!$exercise) {
    http_response_code(404);
    exit('Exercise record not found.');
}

$error = '';

/*
|--------------------------------------------------------------------------
| Create delete CSRF token
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['delete_exercise_token'])) {
    $_SESSION['delete_exercise_token'] =
        bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| Delete exercise
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $submittedToken =
        $_POST['csrf_token'] ?? '';

    if (
        !is_string($submittedToken) ||
        !hash_equals(
            $_SESSION['delete_exercise_token'],
            $submittedToken
        )
    ) {
        $error =
            'Invalid request. Please try again.';
    } else {

        /*
        |--------------------------------------------------------------------------
        | Save record temporarily for Undo
        |--------------------------------------------------------------------------
        |
        | Before deleting the record from the database,
        | we temporarily save its information inside
        | the user's PHP session.
        |
        */

        $_SESSION['last_deleted_exercise'] = [
            'user_id' =>
                $userId,

            'activity_type' =>
                $exercise['activity_type'],

            'duration_minutes' =>
                (int) $exercise['duration_minutes'],

            'calories_burned' =>
                (int) $exercise['calories_burned'],

            'exercise_date' =>
                $exercise['exercise_date'],

            'deleted_at' =>
                time()
        ];

        /*
        |--------------------------------------------------------------------------
        | Create token for Undo action
        |--------------------------------------------------------------------------
        */

        $_SESSION['undo_exercise_token'] =
            bin2hex(random_bytes(32));

        /*
        |--------------------------------------------------------------------------
        | Delete record from database
        |--------------------------------------------------------------------------
        */

        $delete = $pdo->prepare(
            'DELETE FROM exercise_records
             WHERE exercise_id = ?
               AND user_id = ?'
        );

        $delete->execute([
            $exerciseId,
            $userId
        ]);

        /*
        |--------------------------------------------------------------------------
        | Remove old delete token
        |--------------------------------------------------------------------------
        */

        unset(
            $_SESSION['delete_exercise_token']
        );

        /*
        |--------------------------------------------------------------------------
        | Redirect back to Exercise Tracker
        |--------------------------------------------------------------------------
        */

        header(
            'Location: index.php?deleted=1'
        );

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Page settings
|--------------------------------------------------------------------------
*/

$pageTitle = 'Delete Exercise Record';
$activePage = 'exercise';
$pageStylesheet = 'exercise.css';

require_once __DIR__ . '/../includes/header.php';

?>

<section class="delete-page-header">

    <h1>
        Delete Exercise Record
    </h1>

    <p>
        Please confirm before deleting this workout.
    </p>

</section>


<?php if ($error): ?>

    <div class="error-message" role="alert">
        <?= htmlspecialchars(
            $error,
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </div>

<?php endif; ?>


<div class="delete-card">

    <div class="warning-icon" aria-hidden="true">
        !
    </div>

    <h2>
        Are you sure?
    </h2>

    <p class="delete-warning">
        This exercise record will be deleted.
        You will have 20 seconds to undo this action.
    </p>


    <dl class="record-summary">

        <div>

            <dt>
                Activity
            </dt>

            <dd>
                <?= htmlspecialchars(
                    $exercise['activity_type'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </dd>

        </div>


        <div>

            <dt>
                Duration
            </dt>

            <dd>
                <?= htmlspecialchars(
                    $exercise['duration_minutes'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

                minutes
            </dd>

        </div>


        <div>

            <dt>
                Calories Burned
            </dt>

            <dd>
                <?= htmlspecialchars(
                    $exercise['calories_burned'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

                kcal
            </dd>

        </div>


        <div>

            <dt>
                Exercise Date
            </dt>

            <dd>
                <?= htmlspecialchars(
                    date(
                        'd M Y',
                        strtotime(
                            $exercise['exercise_date']
                        )
                    ),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </dd>

        </div>

    </dl>


    <form method="post">

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
            $_SESSION['delete_exercise_token'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>">


        <div class="delete-actions">

            <button class="danger-button" type="submit">
                Yes, Delete Record
            </button>


            <a class="cancel-button" href="index.php">
                Cancel
            </a>

        </div>

    </form>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>