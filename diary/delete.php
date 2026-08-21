<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Please log in first.");
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM diary WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error deleting entry: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Entry - Diary Journal</title>
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

        <div class="form-card" style="text-align: center;">
            <h2 style="color: var(--danger);">Deletion Error</h2>
            <?php if (isset($error)): ?>
                <p style="color: var(--text-color); margin-bottom: 20px;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <a href="index.php" class="btn-cancel">Return to Diary List</a>
        </div>
    </main>
</body>
</html>