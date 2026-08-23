<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireStudent();

$userId = (int) $_SESSION['user_id'];

$title = '';
$mood = '';
$journalDate = date('Y-m-d');
$content = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $mood = trim($_POST['mood'] ?? '');
    $journalDate = trim($_POST['journal_date'] ?? '');
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

    if (
        $title === '' ||
        $mood === '' ||
        $journalDate === '' ||
        $content === ''
    ) {
        $error = 'Please complete all required fields.';
    } elseif (!$isValidDate) {
        $error =
            'Invalid date. Please enter a valid date with a 4-digit year.';
    } else {
        try {
            $statement = $pdo->prepare(
                'INSERT INTO diary
                    (user_id, title, content, mood, journal_date)
                 VALUES (?, ?, ?, ?, ?)'
            );

            $statement->execute([
                $userId,
                $title,
                $content,
                $mood,
                $journalDate
            ]);

            header('Location: index.php?created=1');
            exit;
        } catch (PDOException $exception) {
            $error =
                'The diary entry could not be saved. Please try again.';
        }
    }
}

$pageTitle = 'Add Entry - Diary Journal';
$activePage = 'diary';
$pageStylesheet = 'diary.css';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="diary-wrapper">
    <div class="form-card">
        <h2>New Diary Entry</h2>

        <?php if ($error !== ''): ?>
            <p class="form-error" role="alert">
                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>
        <?php endif; ?>

        <form method="post" action="create.php" class="diary-form">
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

                <input type="text" name="mood" id="moodInput" maxlength="30" placeholder="e.g. Happy, Calm, Stressed"
                    class="form-control" value="<?= htmlspecialchars(
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
                    Save Entry
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