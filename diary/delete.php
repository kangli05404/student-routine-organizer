<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireStudent();

$user_id = (int) $_SESSION['user_id'];

$entry_id = $_GET['id'] ?? null;

if (!$entry_id) {
    header("Location: index.php");
    exit();
}

// Fetch entry using the simple "?" method
$stmt = $pdo->prepare("SELECT * FROM diary WHERE id = ? AND user_id = ?");
$stmt->execute([$entry_id, $user_id]);
$entry = $stmt->fetch();

if (!$entry) {
    header("Location: index.php");
    exit();
}

// Handle deletion when user submits "Yes, Delete Record"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $delete_stmt = $pdo->prepare("DELETE FROM diary WHERE id = ? AND user_id = ?");
    $delete_stmt->execute([$entry_id, $user_id]);

    header("Location: index.php?msg=deleted");
    exit();
}

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/
$pageTitle = 'Delete Diary Entry - Student Routine Organizer';
$activePage = 'diary'; // Activates the yellow navigation highlight
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
    <style>
        /* A custom red button specifically for this delete page based on your CSS style */
        .btn-danger-solid {
            background: linear-gradient(135deg, var(--danger) 0%, #c53030 100%);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.95rem;
            box-shadow: 0 4px 12px rgba(229, 62, 62, 0.3);
            transition: all 0.2s ease;
        }

        .btn-danger-solid:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(229, 62, 62, 0.4);
        }

        .warning-text {
            color: var(--danger);
            font-weight: bold;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
    </style>
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
        <!-- Using your beautiful purple gradient form-card -->
        <div class="form-card">
            <h2>Delete Diary Record</h2>
            <p class="warning-text">⚠️⚠️ Are you sure? This diary entry will be permanently deleted. ⚠️⚠️</p>

            <!-- Reusing your exact diary-card design to show a perfect preview -->
            <article class="diary-card" style="margin-bottom: 30px; pointer-events: none;">
                <div class="diary-card-header">
                    <h3 class="diary-card-title"><?= htmlspecialchars($entry['title']) ?></h3>
                    <span class="mood-badge">Mood: <?= htmlspecialchars($entry['mood']) ?></span>
                </div>
                <small class="diary-date"><?= htmlspecialchars($entry['journal_date']) ?></small>
                <p class="diary-content"><?= nl2br(htmlspecialchars(substr($entry['content'], 0, 100))) ?></p>
            </article>

            <!-- Using your form-actions and btn-cancel classes -->
            <form method="POST" class="form-actions">
                <button type="submit" name="confirm_delete" class="btn-danger-solid">Yes, Delete Record</button>
                <a href="index.php" class="btn-cancel">Cancel</a>
            </form>
        </div>
    </main>

</body>

</html>