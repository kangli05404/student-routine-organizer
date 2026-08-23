<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireStudent();

$userId = (int) $_SESSION['user_id'];

$entryId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$entryId) {
    header('Location: index.php');
    exit;
}

$statement = $pdo->prepare(
    'SELECT
        id,
        title,
        content,
        mood,
        journal_date
     FROM diary
     WHERE id = ?
       AND user_id = ?
     LIMIT 1'
);

$statement->execute([
    $entryId,
    $userId
]);

$entry = $statement->fetch();

if (!$entry) {
    header('Location: index.php');
    exit;
}

$title = $entry['title'];
$mood = $entry['mood'];
$journalDate = $entry['journal_date'];
$content = $entry['content'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $mood = trim($_POST['mood'] ?? '');
    $journalDate =
        trim($_POST['journal_date'] ?? '');
    $content = trim($_POST['content'] ?? '');

    $date = DateTime::createFromFormat(
        'Y-m-d',
        $journalDate
    );

    $dateParts = explode('-', $journalDate);

    $isValidDate =
        $date &&
        $date->format('Y-m-d') === $journalDate &&
        isset($dateParts[0]) &&
        strlen($dateParts[0]) === 4;

    $hasChanges =
        $title !== $entry['title'] ||
        $mood !== $entry['mood'] ||
        $journalDate !== $entry['journal_date'] ||
        $content !== $entry['content'];

    if (
        $title === '' ||
        $mood === '' ||
        $journalDate === '' ||
        $content === ''
    ) {
        $error =
            'Please complete all required fields.';
    } elseif (!$isValidDate) {
        $error =
            'Invalid date. Please enter a valid date with a 4-digit year.';
    } elseif (!$hasChanges) {
        $error =
            'No changes detected. Please update at least one field.';
    } else {
        try {
            $updateStatement = $pdo->prepare(
                'UPDATE diary
                 SET
                    title = ?,
                    content = ?,
                    mood = ?,
                    journal_date = ?
                 WHERE id = ?
                   AND user_id = ?'
            );

            $updateStatement->execute([
                $title,
                $content,
                $mood,
                $journalDate,
                $entryId,
                $userId
            ]);

            header('Location: index.php?updated=1');
            exit;
        } catch (PDOException $exception) {
            $error =
                'The diary entry could not be updated. Please try again.';
        }
    }
}

$pageTitle = 'Edit Entry - Diary Journal';
$activePage = 'diary';
$pageStylesheet = 'diary.css';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="diary-wrapper">
    <div class="form-card">
        <h2>Edit Diary Entry</h2>

        <?php if ($error !== ''): ?>
            <p class="form-error" role="alert">
                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>
        <?php endif; ?>

        <form method="post" action="edit.php?id=<?= $entryId ?>" class="diary-form">
            <div class="form-group">
                <label for="titleInput">
                    Title

                    <small class="char-count" id="titleCount">
                        0/100
                    </small>
                </label>

                <input type="text" name="title" id="titleInput" maxlength="100" class="form-control" value="<?= htmlspecialchars(
                    $title,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>" required>
            </div>

            <div class="form-group">
                <label for="moodInput">
                    Mood

                    <small class="char-count" id="moodCount">
                        0/30
                    </small>
                </label>

                <input type="text" name="mood" id="moodInput" maxlength="30" class="form-control" value="<?= htmlspecialchars(
                    $mood,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>" required>
            </div>

            <div class="form-group">
                <label for="journalDate">
                    Date
                </label>

                <input type="date" name="journal_date" id="journalDate" class="form-control" value="<?= htmlspecialchars(
                    $journalDate,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>" required>
            </div>

            <div class="form-group">
                <label for="contentInput">
                    Content

                    <small class="char-count" id="contentCount">
                        0/2000
                    </small>
                </label>

                <textarea name="content" id="contentInput" maxlength="2000" rows="6" class="form-control" required><?= htmlspecialchars(
                    $content,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    Update Entry
                </button>

                <a href="index.php" class="btn-cancel">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function setupCharCounter(
        inputId,
        counterId,
        maxLength
    ) {
        const input =
            document.getElementById(inputId);

        const counter =
            document.getElementById(counterId);

        if (!input || !counter) {
            return;
        }

        function updateCount() {
            const currentLength =
                input.value.length;

            counter.textContent =
                `${currentLength}/${maxLength}`;

            if (currentLength >= maxLength) {
                counter.style.color =
                    'var(--danger)';

                counter.style.fontWeight =
                    'bold';
            } else {
                counter.style.color =
                    '#64748b';

                counter.style.fontWeight =
                    'normal';
            }
        }

        input.addEventListener(
            'input',
            updateCount
        );

        updateCount();
    }

    setupCharCounter(
        'titleInput',
        'titleCount',
        100
    );

    setupCharCounter(
        'moodInput',
        'moodCount',
        30
    );

    setupCharCounter(
        'contentInput',
        'contentCount',
        2000
    );
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>