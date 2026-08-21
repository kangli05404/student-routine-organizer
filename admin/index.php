<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

function adminEscape($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function countRecords($pdo, $table)
{
    $allowedTables = [
        'exercise_records',
        'diary',
        'habits',
        'transactions'
    ];

    if (!in_array($table, $allowedTables, true)) {
        return 0;
    }

    return (int) $pdo
        ->query("SELECT COUNT(*) FROM {$table}")
        ->fetchColumn();
}

$studentCountStatement = $pdo->query(
    "SELECT COUNT(*)
     FROM users
     WHERE role = 'student'"
);

$totalStudents =
    (int) $studentCountStatement->fetchColumn();

$totalExercises =
    countRecords($pdo, 'exercise_records');

$totalDiaryEntries =
    countRecords($pdo, 'diary');

$totalHabits =
    countRecords($pdo, 'habits');

$totalTransactions =
    countRecords($pdo, 'transactions');

$usersStatement = $pdo->query(
    'SELECT
        id,
        name,
        email,
        role,
        created_at
     FROM users
     ORDER BY id ASC'
);

$users = $usersStatement->fetchAll();

$pageTitle = 'Admin Dashboard';
$activePage = 'admin';
$pageStylesheet = 'admin.css';

require_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-header">
    <div>
        <span class="admin-label">ADMINISTRATOR</span>

        <h1>Admin Dashboard</h1>

        <p>
            View registered users and basic system summaries.
        </p>
    </div>

    <div class="admin-badge">
        Admin
    </div>
</section>

<section class="admin-summary-grid">
    <article class="admin-summary-card">
        <span class="summary-icon">👥</span>

        <div>
            <strong>
                <?= $totalStudents ?>
            </strong>
            <span>Registered Students</span>
        </div>
    </article>

    <article class="admin-summary-card">
        <span class="summary-icon">🏋️</span>

        <div>
            <strong>
                <?= $totalExercises ?>
            </strong>
            <span>Exercise Records</span>
        </div>
    </article>

    <article class="admin-summary-card">
        <span class="summary-icon">📖</span>

        <div>
            <strong>
                <?= $totalDiaryEntries ?>
            </strong>
            <span>Diary Entries</span>
        </div>
    </article>

    <article class="admin-summary-card">
        <span class="summary-icon">✅</span>

        <div>
            <strong>
                <?= $totalHabits ?>
            </strong>
            <span>Habit Records</span>
        </div>
    </article>

    <article class="admin-summary-card">
        <span class="summary-icon">💰</span>

        <div>
            <strong>
                <?= $totalTransactions ?>
            </strong>
            <span>Transactions</span>
        </div>
    </article>
</section>

<section class="users-section">
    <div class="section-heading">
        <div>
            <h2>Registered Users</h2>

            <p>
                View all student and administrator accounts.
            </p>
        </div>

        <span class="user-count">
            <?= count($users) ?> users
        </span>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered Date</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <?= adminEscape($user['id']) ?>
                        </td>

                        <td>
                            <?= adminEscape($user['name']) ?>
                        </td>

                        <td>
                            <?= adminEscape($user['email']) ?>
                        </td>

                        <td>
                            <span class="role-badge role-<?= adminEscape(
                                $user['role']
                            ) ?>">
                                <?= adminEscape(
                                    ucfirst($user['role'])
                                ) ?>
                            </span>
                        </td>

                        <td>
                            <?= adminEscape(
                                date(
                                    'd M Y',
                                    strtotime($user['created_at'])
                                )
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>