<?php
/**
 * ============================================================
 * PRESENTATION TIER
 * Add a new habit record (CREATE)
 * ============================================================
 */
require_once '../includes/auth.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();
$habitManager = new HabitManager($db);

$errors = [];
$habit_name = $target_frequency = $completion_status = $habit_date = $notes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $habit_name        = trim($_POST['habit_name'] ?? '');
    $target_frequency  = trim($_POST['target_frequency'] ?? '');
    $completion_status = trim($_POST['completion_status'] ?? 'Pending');
    $habit_date         = trim($_POST['habit_date'] ?? '');
    $notes              = trim($_POST['notes'] ?? '');

    $result = $habitManager->addHabit(
        $_SESSION['user_id'],
        $habit_name,
        $target_frequency,
        $completion_status,
        $habit_date,
        $notes
    );

    if ($result['success']) {
        $_SESSION['flash_success'] = "Habit added successfully.";
        header("Location: index.php");
        exit();
    } else {
        $errors = $result['errors'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Habit</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="navbar">
    <div><strong>Student Routine Organizer</strong></div>
    <div>
        <a href="../dashboard.php">Dashboard</a>
        <a href="index.php">Habit Tracker</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <div class="card">
        <h2>Add New Habit</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin:0; padding-left:18px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="add.php">
            <div class="field">
                <label>Habit Name</label>
                <input type="text" name="habit_name" maxlength="100" value="<?= htmlspecialchars($habit_name) ?>" required>
            </div>
            <div class="field">
                <label>Target Frequency</label>
                <input type="text" name="target_frequency" placeholder="e.g. Daily, 3x/week" value="<?= htmlspecialchars($target_frequency) ?>" required>
            </div>
            <div class="field">
                <label>Completion Status</label>
                <select name="completion_status">
                    <option value="Pending" <?= $completion_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Completed" <?= $completion_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="field">
                <label>Date</label>
                <input type="date" name="habit_date" value="<?= htmlspecialchars($habit_date) ?>" required>
            </div>
            <div class="field">
                <label>Notes (optional)</label>
                <textarea name="notes" rows="3" maxlength="255"><?= htmlspecialchars($notes) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Habit</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

</body>
</html>
