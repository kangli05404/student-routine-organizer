<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireStudent();

if (!isset($_GET['id'])) {
    die('No entry specified.');
}

$id = $_GET['id'];
$user_id = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM diary WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$entry = $stmt->fetch();

if (!$entry) {
    die("Entry not found or unauthorized.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $mood = $_POST['mood'];
    $journal_date = $_POST['journal_date'];

    try {
        $update_stmt = $pdo->prepare("UPDATE diary SET title=?, content=?, mood=?, journal_date=? WHERE id=? AND user_id=?");
        $update_stmt->execute([$title, $content, $mood, $journal_date, $id, $user_id]);

        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error updating entry: " . $e->getMessage();
    }
}

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/
$pageTitle = 'Edit Entry - Diary Journal';
$activePage = 'diary';
$pageStylesheet = 'diary.css';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/shared.css">
    <link rel="stylesheet" href="../assets/css/diary.css">
</head>

<body class="diary-page">

    <?php
    if (file_exists('../includes/navbar.php')) {
        include '../includes/navbar.php';
    } elseif (file_exists('../includes/header.php')) {
        include '../includes/header.php';
    }
    ?>

    <main class="diary-wrapper">

        <div class="form-card">
            <h2>Edit Diary Entry</h2>

            <?php if (isset($error)): ?>
                <p style="color: var(--danger);"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST" action="edit.php?id=<?php echo $id; ?>" class="diary-form">
                <div class="form-group">
                    <label>
                        Title 
                        <small class="char-count" id="titleCount">0/100</small>
                    </label>
                    <input type="text" name="title" id="titleInput" maxlength="100" value="<?php echo htmlspecialchars($entry['title']); ?>"
                        class="form-control" required>
                </div>

                <div class="form-group">
                    <label>
                        Mood 
                        <small class="char-count" id="moodCount">0/30</small>
                    </label>
                    <input type="text" name="mood" id="moodInput" maxlength="30" value="<?php echo htmlspecialchars($entry['mood']); ?>"
                        class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="journal_date" value="<?php echo $entry['journal_date']; ?>"
                        class="form-control" required>
                </div>

                <div class="form-group">
                    <label>
                        Content 
                        <small class="char-count" id="contentCount">0/2000</small>
                    </label>
                    <textarea name="content" id="contentInput" maxlength="2000" rows="6" class="form-control"
                        required><?php echo htmlspecialchars($entry['content']); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Update Entry</button>
                    <a href="index.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        function setupCharCounter(inputId, counterId, maxLength) {
            const input = document.getElementById(inputId);
            const counter = document.getElementById(counterId);

            if (!input || !counter) return;

            function updateCount() {
                const currentLength = input.value.length;
                counter.textContent = `${currentLength}/${maxLength}`;

                if (currentLength >= maxLength) {
                    counter.style.color = 'var(--danger)';
                    counter.style.fontWeight = 'bold';
                } else {
                    counter.style.color = '#64748b';
                    counter.style.fontWeight = 'normal';
                }
            }

            input.addEventListener('input', updateCount);
            updateCount(); // Calculates length for existing entry content on load
        }

        setupCharCounter('titleInput', 'titleCount', 100);
        setupCharCounter('moodInput', 'moodCount', 30);
        setupCharCounter('contentInput', 'contentCount', 2000);
    </script>
</body>

</html>