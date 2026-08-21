<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Summary Data (New SQL)
|--------------------------------------------------------------------------
*/

//Total habit entries
$totalEntriesQuery = $pdo->prepare(
    'SELECT COUNT(*) FROM habits WHERE user_id = ?'
);
$totalEntriesQuery->execute([$userId]);
$totalEntries = $totalEntriesQuery->fetchColumn();

//Total completed entries
$totalCompletedQuery = $pdo->prepare(
    'SELECT COUNT(*) FROM habits WHERE user_id = ? AND completion_status = ?'
);
$totalCompletedQuery->execute([$userId, 'completed']);
$totalCompleted = $totalCompletedQuery->fetchColumn();

//Total unique habits
$totalTypesQuery = $pdo->prepare(
    'SELECT COUNT(DISTINCT habit_name) FROM habits WHERE user_id = ?'
);
$totalTypesQuery->execute([$userId]);
$totalTypes = $totalTypesQuery->fetchColumn();

//Habits this week, calculate the start of the week (Monday)
$weekStart = date('Y-m-d', strtotime('monday this week'));
$habitsThisWeekQuery = $pdo->prepare(
    'SELECT COUNT(*) FROM habits WHERE user_id = ? AND habit_date >= ?'
);
$habitsThisWeekQuery->execute([$userId, $weekStart]);
$habitsThisWeek = $habitsThisWeekQuery->fetchColumn();


/*
|--------------------------------------------------------------------------
| Filtering
|--------------------------------------------------------------------------
*/

$statusFilter = $_GET['status'] ?? 'all';
$validStatuses = ['all', 'pending', 'completed'];

if (!in_array($statusFilter, $validStatuses, true)) {
    $statusFilter = 'all';
}

/*
|--------------------------------------------------------------------------
| Sorting (Same as original)
|--------------------------------------------------------------------------
*/

$sort = $_GET['sort'] ?? 'newest';
$sortOptions = [
    'newest' => 'habit_date DESC, habit_id DESC',
    'oldest' => 'habit_date ASC, habit_id ASC',
    'name' => 'habit_name ASC'
];

if (!array_key_exists($sort, $sortOptions)) {
    $sort = 'newest';
}
$orderBy = $sortOptions[$sort];


/*
|--------------------------------------------------------------------------
| Habit records (Modified query for mapping)
|--------------------------------------------------------------------------
*/

$query =
    'SELECT
        habit_id,
        habit_name,
        target_frequency,
        completion_status,
        habit_date
     FROM habits
     WHERE user_id = ?';

$params = [$userId];

if ($statusFilter !== 'all') {
    $query .= ' AND completion_status = ?';
    $params[] = $statusFilter;
}

$query .= ' ORDER BY ' . $orderBy;

$statement = $pdo->prepare($query);
$statement->execute($params);
$habits = $statement->fetchAll();

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

$pageTitle = 'My Habit Tracker';
$activePage = 'habit';
$pageStylesheet = 'habit.css';

require_once __DIR__ . '/../includes/header.php';
?>

<section class="habit-header-centered">
    <div class="header-box">
        

        <h1>My Habit Tracker</h1>

        <p class="page-description">Keep track of your daily routines, frequencies, and completion progress.</p>
    </div>

    <a class="btn-add-golden" href="create.php">+ Add New Habit</a>
</section>

<section class="summary-cards">
    <!-- Card 1: Total Entries -->
    <article class="summary-card purple-card">
        <div class="card-icon-wrapper">
            <!-- Icon placeholder: Trophy -->
            <img src="../assets/images/total-entry.jpg" alt="Total entry Icon">
        </div>
        <div class="card-data">
            <span class="card-value"><?= e($totalEntries) ?></span>
            <span class="card-label">Total Entries</span>
        </div>
    </article>

    <!-- Card 2: Completed Entries -->
    <article class="summary-card blue-card">
        <div class="card-icon-wrapper">
            <!-- Icon placeholder: Clock/Timer -->
            <img src="../assets/images/complete.png" alt="Completions Icon">
        </div>
        <div class="card-data">
            <span class="card-value"><?= e($totalCompleted) ?></span>
            <span class="card-label">Completions</span>
        </div>
    </article>

    <!-- Card 3: Total Types -->
    <article class="summary-card orange-card">
        <div class="card-icon-wrapper">
            <!-- Icon placeholder: Open Book -->
            <img src="../assets/images/number.png" alt="Habit Types Icon">
        </div>
        <div class="card-data">
            <span class="card-value"><?= e($totalTypes) ?></span>
            <span class="card-label">Unique Habits</span>
        </div>
    </article>

    <!-- Card 4: Habits This Week -->
    <article class="summary-card green-card">
        <div class="card-icon-wrapper">
            <!-- Icon placeholder: Calendar/Checkmark -->
            <img src="../assets/images/week.jpg" alt="Week Icon">
        </div>
        <div class="card-data">
            <span class="card-value"><?= e($habitsThisWeek) ?></span>
            <span class="card-label">Entries This Week</span>
        </div>
    </article>
</section>


<?php if (!$habits): ?>
    <div class="empty-state card">
        <h2>No habits yet</h2>
        <p>
            Add your first habit to start tracking your daily progress.
        </p>
    </div>
<?php else: ?>

<div class="table-toolbar">
    <div class="sort-pills-container">
        <span class="sort-label">Sort by:</span>
        <div class="sort-pills">
            <a href="?status=<?= e($statusFilter) ?>&sort=newest" 
               class="sort-pill <?= $sort === 'newest' ? 'active' : '' ?>">
               ✨ Newest
            </a>
            <a href="?status=<?= e($statusFilter) ?>&sort=oldest" 
               class="sort-pill <?= $sort === 'oldest' ? 'active' : '' ?>">
               ⏳ Oldest
            </a>
            <a href="?status=<?= e($statusFilter) ?>&sort=name" 
               class="sort-pill <?= $sort === 'name' ? 'active' : '' ?>">
               🔤 Name
            </a>
        </div>
    </div>
</div>

<div class="table-wrapper card">
        <table>
            <thead>
                <tr>
                    <th style="width: 30%;">Habit Name</th>
                    <th style="width: 20%;">Frequency</th>
                    <th class="col-status text-center" style="width: 15%;">Status</th>
                    <th style="width: 15%;">Date</th>
                    <th class="col-actions text-center" style="width: 20%;">Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($habits as $habit): ?>
                    <tr>
                        <td class="col-habit-name" title="<?= e($habit['habit_name']) ?>">
                            <?= e($habit['habit_name']) ?>
                        </td>

                        <td class="col-frequency" title="<?= e($habit['target_frequency']) ?>">
                            <?= e($habit['target_frequency']) ?>
                        </td>

                        <td class="col-status text-center">
                            <span class="status-badge badge-<?= e($habit['completion_status']) ?>">
                                <?= e(ucfirst($habit['completion_status'])) ?>
                            </span>
                        </td>

                        <td class="col-date">
                            <?= e(
                                date(
                                    'd M Y',
                                    strtotime($habit['habit_date'])
                                )
                            ) ?>
                        </td>

                        <td class="col-actions text-center">
                            <div class="action-buttons">
                                <a class="action-link edit-link" href="edit.php?id=<?= e($habit['habit_id']) ?>">
                                    Edit
                                </a>

                                <a class="action-link delete-link" href="delete.php?id=<?= e($habit['habit_id']) ?>">
                                    Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>