<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']))
    die("Please log in first.");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $mood = $_POST['mood'];
    $journal_date = $_POST['journal_date'];

    try {
        $stmt = $pdo->prepare("INSERT INTO diary (user_id, title, content, mood, journal_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $content, $mood, $journal_date]);

        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error saving entry: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Entry - Diary Journal</title>
    <link rel="stylesheet" href="../assets/css/shared.css">
    <link rel="stylesheet" href="../assets/css/diary.css">
    <script src="../assets/js/navbar.js" defer></script>
</head>

<body class="diary-page">

    <div id="navbar"></div>
    <?php
    if (file_exists('../includes/navbar.php')) {
        include '../includes/navbar.php';
    } elseif (file_exists('../includes/header.php')) {
        include '../includes/header.php';
    }
    ?>

    <main class="diary-wrapper">

        <div class="form-card">
            <h2>New Diary Entry</h2>

            <?php if (isset($error)): ?>
                <p style="color: var(--danger);"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST" action="create.php" class="diary-form">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Mood</label>
                    <input type="text" name="mood" placeholder="e.g., Happy, Calm, Stressed" class="form-control"
                        required>
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="journal_date" value="<?php echo date('Y-m-d'); ?>" class="form-control"
                        required>
                </div>

                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" rows="6" class="form-control" required></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Save Entry</button>
                    <a href="index.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>

</html>