<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

requireStudent();

$userName = $_SESSION['user_name'] ?? 'Student';
$userRole = $_SESSION['user_role'] ?? 'student';
$userId = (int) $_SESSION['user_id'];

$currentDate = new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur'));

// ============================================================
// Get greeting based on time of day
// ============================================================
$hour = (int) $currentDate->format('H');
if ($hour < 12) {
    $greeting = 'Good Morning';
} elseif ($hour < 17) {
    $greeting = 'Good Afternoon';
} else {
    $greeting = 'Good Evening';
}

// ============================================================
// FETCH MODULE SUMMARIES
// ============================================================

// --- Exercise Summary ---
$exerciseStmt = $pdo->prepare(
    "SELECT 
        COUNT(*) AS total_workouts,
        COALESCE(SUM(calories_burned), 0) AS total_calories,
        COALESCE(SUM(duration_minutes), 0) AS total_minutes,
        COALESCE(ROUND(AVG(calories_burned), 0), 0) AS avg_calories,
        COUNT(CASE WHEN exercise_date >= CURDATE() - INTERVAL 7 DAY THEN 1 END) AS workouts_this_week,
        COUNT(CASE WHEN exercise_date >= CURDATE() - INTERVAL 30 DAY THEN 1 END) AS workouts_this_month,
        MAX(exercise_date) AS last_workout_date
     FROM exercise_records 
     WHERE user_id = ?"
);
$exerciseStmt->execute([$userId]);
$exerciseSummary = $exerciseStmt->fetch();

// --- Diary Summary ---
$diaryStmt = $pdo->prepare(
    "SELECT 
        COUNT(*) AS total_entries,
        COUNT(CASE WHEN journal_date >= CURDATE() - INTERVAL 7 DAY THEN 1 END) AS entries_this_week,
        COUNT(CASE WHEN journal_date >= CURDATE() - INTERVAL 30 DAY THEN 1 END) AS entries_this_month,
        (SELECT mood FROM diary WHERE user_id = ? ORDER BY journal_date DESC, id DESC LIMIT 1) AS latest_mood,
        (SELECT journal_date FROM diary WHERE user_id = ? ORDER BY journal_date DESC, id DESC LIMIT 1) AS last_entry_date
     FROM diary 
     WHERE user_id = ?"
);
$diaryStmt->execute([$userId, $userId, $userId]);
$diarySummary = $diaryStmt->fetch();

// --- Money Summary ---
$moneyStmt = $pdo->prepare(
    "SELECT 
        COALESCE(SUM(CASE WHEN transaction_type = 'Income' THEN amount END), 0) AS total_income,
        COALESCE(SUM(CASE WHEN transaction_type = 'Expense' THEN amount END), 0) AS total_expense,
        COUNT(*) AS total_transactions,
        COALESCE(SUM(CASE WHEN transaction_type = 'Income' AND transaction_date >= CURDATE() - INTERVAL 30 DAY THEN amount END), 0) AS income_this_month,
        COALESCE(SUM(CASE WHEN transaction_type = 'Expense' AND transaction_date >= CURDATE() - INTERVAL 30 DAY THEN amount END), 0) AS expense_this_month
     FROM transactions 
     WHERE user_id = ?"
);
$moneyStmt->execute([$userId]);
$moneySummary = $moneyStmt->fetch();
$balance = $moneySummary['total_income'] - $moneySummary['total_expense'];

// --- Habit Summary ---
$habitStmt = $pdo->prepare(
    "SELECT 
        COUNT(*) AS total_habits,
        COUNT(CASE WHEN completion_status = 'completed' THEN 1 END) AS completed_habits,
        COUNT(CASE WHEN completion_status = 'pending' THEN 1 END) AS pending_habits,
        COUNT(CASE WHEN habit_date >= CURDATE() - INTERVAL 7 DAY THEN 1 END) AS habits_this_week
     FROM habits 
     WHERE user_id = ?"
);
$habitStmt->execute([$userId]);
$habitSummary = $habitStmt->fetch();

$total = ($habitSummary['total_habits'] ?? 0);
$completed = ($habitSummary['completed_habits'] ?? 0);
$completionRate = $total > 0 ? round(($completed / $total) * 100) : 0;

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
$pageStylesheet = 'dashboard.css';

require_once __DIR__ . '/includes/header.php';
?>

<!-- ============================================================
     WELCOME SECTION
     ============================================================ -->
<section class="welcome-section">
    <div class="welcome-left">
        <span class="welcome-greeting"><?= htmlspecialchars($greeting, ENT_QUOTES, 'UTF-8') ?> 👋</span>
        <h1 class="welcome-title">
            <span class="welcome-name"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
        </h1>
        <p class="welcome-desc">Here's a complete overview of your student routine.</p>
        <div class="welcome-meta">
            <span class="wm-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <?= htmlspecialchars($currentDate->format('l, d F Y'), ENT_QUOTES, 'UTF-8') ?>
            </span>
            <span class="wm-item role-pill"><?= htmlspecialchars(ucfirst($userRole), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>
    <div class="welcome-right" aria-hidden="true">
        <div class="welcome-ring">
            <span class="ring-emoji">🌟</span>
        </div>
    </div>
</section>

<!-- ============================================================
     QUICK LINKS + ADMIN
     ============================================================ -->
<div class="quick-row">
    <div class="quick-links">
        <span class="ql-label">🚀 Quick Access</span>
        <div class="ql-group">
            <a href="<?= $baseUrl ?>/exercise/index.php" class="ql-link ql-exercise">🏋️ Exercise</a>
            <a href="<?= $baseUrl ?>/diary/index.php" class="ql-link ql-diary">✍️ Diary</a>
            <a href="<?= $baseUrl ?>/money/index.php" class="ql-link ql-money">💰 Money</a>
            <a href="<?= $baseUrl ?>/habit/index.php" class="ql-link ql-habit">✅ Habits</a>
        </div>
    </div>

    <?php if ($userRole === 'admin'): ?>
        <div class="admin-box">
            <span class="admin-icon">⚙️</span>
            <div>
                <span class="admin-tag">ADMIN</span>
                <span class="admin-desc">Manage users &amp; system</span>
            </div>
            <a href="<?= $baseUrl ?>/admin/index.php" class="admin-btn">Open →</a>
        </div>
    <?php endif; ?>
</div>

<!-- ============================================================
     MODULE DEFINITION GRID (CLICKABLE CARDS)
     ============================================================ -->
<div class="module-grid">

    <!-- 1. EXERCISE CARD -->
    <div class="mod-card mod-exercise clickable-card" onclick="openModal('modal-exercise')">
        <div class="mod-card-top">
            <div class="mod-icon-title">
                <span class="mod-icon">🏋️</span>
                <span class="mod-name">Exercise Tracker</span>
            </div>
            <span class="mod-badge">Module 01</span>
        </div>
        <p class="mod-description">
            Track your physical activities, logged workouts, estimated calorie expenditure, and overall fitness consistency.
        </p>
        <div class="mod-click-hint">
            <span>Click to view statistics</span>
            <span class="arrow">🔍</span>
        </div>
    </div>

    <!-- 2. DIARY CARD -->
    <div class="mod-card mod-diary clickable-card" onclick="openModal('modal-diary')">
        <div class="mod-card-top">
            <div class="mod-icon-title">
                <span class="mod-icon">✍️</span>
                <span class="mod-name">Diary Journal</span>
            </div>
            <span class="mod-badge">Module 02</span>
        </div>
        <p class="mod-description">
            Maintain personal daily entries, record your daily feelings and mood, and reflect on your campus experiences.
        </p>
        <div class="mod-click-hint">
            <span>Click to view statistics</span>
            <span class="arrow">🔍</span>
        </div>
    </div>

    <!-- 3. MONEY CARD -->
    <div class="mod-card mod-money clickable-card" onclick="openModal('modal-money')">
        <div class="mod-card-top">
            <div class="mod-icon-title">
                <span class="mod-icon">💰</span>
                <span class="mod-name">Money Tracker</span>
            </div>
            <span class="mod-badge">Module 03</span>
        </div>
        <p class="mod-description">
            Monitor student expenses, record incoming funds or allowances, and maintain an active financial overview.
        </p>
        <div class="mod-click-hint">
            <span>Click to view statistics</span>
            <span class="arrow">🔍</span>
        </div>
    </div>

    <!-- 4. HABITS CARD -->
    <div class="mod-card mod-habit clickable-card" onclick="openModal('modal-habit')">
        <div class="mod-card-top">
            <div class="mod-icon-title">
                <span class="mod-icon">✅</span>
                <span class="mod-name">Habit Tracker</span>
            </div>
            <span class="mod-badge">Module 04</span>
        </div>
        <p class="mod-description">
            Form positive habits, monitor completion status, and keep up with your weekly goals and routines.
        </p>
        <div class="mod-click-hint">
            <span>Click to view statistics</span>
            <span class="arrow">🔍</span>
        </div>
    </div>

</div>

<!-- ============================================================
     POPUP MODALS (ZOOM-IN STATS OVERLAYS)
     ============================================================ -->

<!-- EXERCISE MODAL -->
<div id="modal-exercise" class="dashboard-modal-overlay" onclick="closeModalOnBg(event, 'modal-exercise')">
    <div class="dashboard-modal-card mod-exercise">
        <button class="modal-close-btn" onclick="closeModal('modal-exercise')">&times;</button>
        <div class="mod-card-top">
            <div class="mod-icon-title">
                <span class="mod-icon">🏋️</span>
                <span class="mod-name">Exercise Summary</span>
            </div>
            <span class="mod-badge"><?= number_format($exerciseSummary['total_workouts'] ?? 0) ?> total</span>
        </div>

        <div class="mod-number"><?= number_format($exerciseSummary['workouts_this_week'] ?? 0) ?></div>
        <div class="mod-number-label">Workouts This Week</div>

        <div class="mod-stats-row">
            <div class="mod-stat">
                <span class="ms-number"><?= number_format($exerciseSummary['total_calories'] ?? 0) ?></span>
                <span class="ms-label">🔥 Calories</span>
            </div>
            <div class="mod-stat">
                <span class="ms-number"><?= number_format($exerciseSummary['total_minutes'] ?? 0) ?></span>
                <span class="ms-label">⏱️ Minutes</span>
            </div>
            <div class="mod-stat">
                <span class="ms-number"><?= number_format($exerciseSummary['workouts_this_month'] ?? 0) ?></span>
                <span class="ms-label">📆 This Month</span>
            </div>
        </div>

        <div class="mod-actions">
            <a href="<?= $baseUrl ?>/exercise/index.php" class="mod-link">View Exercise Page →</a>
            <a href="<?= $baseUrl ?>/exercise/create.php" class="mod-btn mod-btn-blue">+ New Workout</a>
        </div>
    </div>
</div>

<!-- DIARY MODAL -->
<div id="modal-diary" class="dashboard-modal-overlay" onclick="closeModalOnBg(event, 'modal-diary')">
    <div class="dashboard-modal-card mod-diary">
        <button class="modal-close-btn" onclick="closeModal('modal-diary')">&times;</button>
        <div class="mod-card-top">
            <div class="mod-icon-title">
                <span class="mod-icon">✍️</span>
                <span class="mod-name">Diary Summary</span>
            </div>
            <span class="mod-badge"><?= number_format($diarySummary['total_entries'] ?? 0) ?> total</span>
        </div>

        <div class="mod-number"><?= number_format($diarySummary['entries_this_week'] ?? 0) ?></div>
        <div class="mod-number-label">Entries This Week</div>

        <div class="mod-stats-row">
            <div class="mod-stat">
                <span class="ms-number"><?= number_format($diarySummary['entries_this_month'] ?? 0) ?></span>
                <span class="ms-label">📆 This Month</span>
            </div>
            <div class="mod-stat">
                <span class="ms-number mood-text"><?= htmlspecialchars($diarySummary['latest_mood'] ?? '—', ENT_QUOTES, 'UTF-8') ?></span>
                <span class="ms-label">😊 Latest Mood</span>
            </div>
            <div class="mod-stat">
                <span class="ms-number">
                    <?= !empty($diarySummary['last_entry_date']) 
                        ? htmlspecialchars(date('d M', strtotime($diarySummary['last_entry_date'])), ENT_QUOTES, 'UTF-8')
                        : '—' ?>
                </span>
                <span class="ms-label">📝 Last Entry</span>
            </div>
        </div>

        <div class="mod-actions">
            <a href="<?= $baseUrl ?>/diary/index.php" class="mod-link">View Diary Page →</a>
            <a href="<?= $baseUrl ?>/diary/create.php" class="mod-btn mod-btn-purple">+ New Entry</a>
        </div>
    </div>
</div>

<!-- MONEY MODAL -->
<div id="modal-money" class="dashboard-modal-overlay" onclick="closeModalOnBg(event, 'modal-money')">
    <div class="dashboard-modal-card mod-money">
        <button class="modal-close-btn" onclick="closeModal('modal-money')">&times;</button>
        <div class="mod-card-top">
            <div class="mod-icon-title">
                <span class="mod-icon">💰</span>
                <span class="mod-name">Money Summary</span>
            </div>
            <span class="mod-badge"><?= number_format($moneySummary['total_transactions'] ?? 0) ?> tx</span>
        </div>

        <div class="mod-number <?= $balance >= 0 ? 'text-green' : 'text-red' ?>">
            RM <?= number_format($balance, 2) ?>
        </div>
        <div class="mod-number-label">Current Balance</div>

        <div class="mod-stats-row">
            <div class="mod-stat">
                <span class="ms-number text-green">+RM <?= number_format($moneySummary['total_income'] ?? 0, 2) ?></span>
                <span class="ms-label">💰 Income</span>
            </div>
            <div class="mod-stat">
                <span class="ms-number text-red">-RM <?= number_format($moneySummary['total_expense'] ?? 0, 2) ?></span>
                <span class="ms-label">💸 Expense</span>
            </div>
            <div class="mod-stat">
                <span class="ms-number"><?= number_format($moneySummary['total_transactions'] ?? 0) ?></span>
                <span class="ms-label">📊 Transactions</span>
            </div>
        </div>

        <div class="mod-actions">
            <a href="<?= $baseUrl ?>/money/index.php" class="mod-link">View Money Page →</a>
            <a href="<?= $baseUrl ?>/money/add.php" class="mod-btn mod-btn-pink">+ New Record</a>
        </div>
    </div>
</div>

<!-- HABIT MODAL -->
<div id="modal-habit" class="dashboard-modal-overlay" onclick="closeModalOnBg(event, 'modal-habit')">
    <div class="dashboard-modal-card mod-habit">
        <button class="modal-close-btn" onclick="closeModal('modal-habit')">&times;</button>
        <div class="mod-card-top">
            <div class="mod-icon-title">
                <span class="mod-icon">✅</span>
                <span class="mod-name">Habits Summary</span>
            </div>
            <span class="mod-badge"><?= $completionRate ?>% done</span>
        </div>

        <div class="mod-number"><?= number_format($habitSummary['habits_this_week'] ?? 0) ?></div>
        <div class="mod-number-label">Habits This Week</div>

        <div class="habit-progress" style="margin: 15px 0;">
            <div class="habit-track">
                <div class="habit-fill" style="width: <?= $completionRate ?>%;"></div>
            </div>
            <span class="habit-text"><?= number_format($completed) ?> / <?= number_format($total) ?> completed</span>
        </div>

        <div class="mod-stats-row">
            <div class="mod-stat">
                <span class="ms-number"><?= number_format($habitSummary['total_habits'] ?? 0) ?></span>
                <span class="ms-label">📊 Total</span>
            </div>
            <div class="mod-stat">
                <span class="ms-number text-green"><?= number_format($habitSummary['completed_habits'] ?? 0) ?></span>
                <span class="ms-label">✅ Done</span>
            </div>
            <div class="mod-stat">
                <span class="ms-number text-orange"><?= number_format($habitSummary['pending_habits'] ?? 0) ?></span>
                <span class="ms-label">⏳ Pending</span>
            </div>
        </div>

        <div class="mod-actions">
            <a href="<?= $baseUrl ?>/habit/index.php" class="mod-link">View Habit Page →</a>
            <a href="<?= $baseUrl ?>/habit/create.php" class="mod-btn mod-btn-orange">+ New Habit</a>
        </div>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = '';
}

function closeModalOnBg(event, id) {
    if (event.target === document.getElementById(id)) {
        closeModal(id);
    }
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.dashboard-modal-overlay.active').forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>