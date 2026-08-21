<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) die("Please log in first.");
if (!isset($_GET['id'])) die("No entry specified.");

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM diary WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$entry = $stmt->fetch();

if (!$entry) die("Entry not found or unauthorized.");

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Entry - Diary Journal</title>
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
        <a href="index.php" class="btn-back">
            <span>←</span> Back to Diary List
        </a>

        <div class="form-card">
            <h2>Edit Diary Entry</h2>
            
            <?php if (isset($error)): ?>
                <p style="color: var(--danger);"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST" action="edit.php?id=<?php echo $id; ?>" class="diary-form">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($entry['title']); ?>" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Mood</label>
                    <input type="text" name="mood" value="<?php echo htmlspecialchars($entry['mood']); ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="journal_date" value="<?php echo $entry['journal_date']; ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" rows="6" class="form-control" required><?php echo htmlspecialchars($entry['content']); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Update Entry</button>
                    <a href="index.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>