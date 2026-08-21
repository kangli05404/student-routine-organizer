<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireStudent();

$userId = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

$type = $_GET['type'] ?? '';
$category = $_GET['category'] ?? '';

/*
|--------------------------------------------------------------------------
| Transaction Records
|--------------------------------------------------------------------------
*/

$sql = '
    SELECT
        transaction_id,
        transaction_type,
        amount,
        category,
        description,
        transaction_date
    FROM transactions
    WHERE user_id = ?
';

$params = [$userId];
$types = 'i';

if ($type !== '') {
    $sql .= ' AND transaction_type = ?';
    $params[] = $type;
    $types .= 's';
}

if ($category !== '') {
    $sql .= ' AND category = ?';
    $params[] = $category;
    $types .= 's';
}

$sql .= '
    ORDER BY
        transaction_date DESC,
        transaction_id DESC
';

$statement = $pdo->prepare($sql);
$statement->execute($params);
$transactions = $statement->fetchAll();

/*
|--------------------------------------------------------------------------
| Total Income
|--------------------------------------------------------------------------
*/

$incomeStatement = $pdo->prepare(
    'SELECT COALESCE(SUM(amount), 0)
     FROM transactions
     WHERE user_id = ?
       AND transaction_type = "Income"'
);
$incomeStatement->execute([$userId]);
$totalIncome = (float) $incomeStatement->fetchColumn();

/*
|--------------------------------------------------------------------------
| Total Expense
|--------------------------------------------------------------------------
*/

$expenseStatement = $pdo->prepare(
    'SELECT COALESCE(SUM(amount), 0)
     FROM transactions
     WHERE user_id = ?
       AND transaction_type = "Expense"'
);
$expenseStatement->execute([$userId]);
$totalExpense = (float) $expenseStatement->fetchColumn();

/*
|--------------------------------------------------------------------------
| Balance
|--------------------------------------------------------------------------
*/

$balance = $totalIncome - $totalExpense;

/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

$pageTitle = 'Money Tracker';
$activePage = 'money';
$pageStylesheet = 'money.css';

require_once __DIR__ . '/../includes/header.php';

?>

<!-- Header -->
<div class="money-header-frame">
    <header class="money-header">
        <span class="money-label">Money Tracker</span>
        <h1>My Money Records</h1>
        <p>Keep track of your income, expenses, and balance.</p>
        <a class="money-add-button" href="add.php">Add Transaction</a>
    </header>

    <!-- Summary -->
    <section class="money-summary">

        <article class="money-summary-card income-card">
            <div class="money-summary-icon">💰</div>
            <div class="money-summary-info">
                <span>Total Income</span>
                <strong>RM <?= number_format($totalIncome, 2) ?></strong>
            </div>
        </article>

        <article class="money-summary-card expense-card">
            <div class="money-summary-icon">💸</div>
            <div class="money-summary-info">
                <span>Total Expense</span>
                <strong>RM <?= number_format($totalExpense, 2) ?></strong>
            </div>
        </article>

        <article class="money-summary-card balance-card">
            <div class="money-summary-icon">💵</div>
            <div class="money-summary-info">
                <span>Balance</span>
                <strong>RM <?= number_format($balance, 2) ?></strong>
                <?php if ($balance > 0): ?>
                    <span class="money-summary-change change-positive">▲ Positive</span>
                <?php elseif ($balance < 0): ?>
                    <span class="money-summary-change change-negative">▼ Negative</span>
                <?php endif; ?>
            </div>
        </article>

    </section>

</div>

<!-- Filter -->
<section class="money-filter-card">
    <span class="money-filter-title">🔍 Filter</span>
    <form method="get" class="money-filter-form">
        <select name="type" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="Income" <?= $type === 'Income' ? 'selected' : '' ?>>Income</option>
            <option value="Expense" <?= $type === 'Expense' ? 'selected' : '' ?>>Expense</option>
        </select>
        <a href="index.php" class="money-reset-button">Reset</a>
    </form>
</section>

<!-- Records -->
<section class="money-records-card">

    <div class="money-records-header">
        <span class="money-records-title">📋 Transaction Records</span>
        <span class="money-records-count"><?= count($transactions) ?> records</span>
    </div>

    <?php if (!$transactions): ?>

        <div class="money-empty-state">
            <span class="money-empty-icon">📭</span>
            <h2>No transaction records found</h2>
            <p>Add your first income or expense to start tracking your money.</p>
        </div>

    <?php else: ?>

        <div class="money-table-wrapper">
            <table class="money-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td>
                                <?php if ($transaction['transaction_type'] === 'Income'): ?>
                                    <span class="type-badge income">
                                        <span class="dot"></span> Income
                                    </span>
                                <?php else: ?>
                                    <span class="type-badge expense">
                                        <span class="dot"></span> Expense
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td><?= e($transaction['category']) ?></td>

                            <td class="description-cell">
                                <?= e($transaction['description'] ?: '—') ?>
                            </td>

                            <td>
                                <?php if ($transaction['transaction_type'] === 'Income'): ?>
                                    <span class="amount-income">+ RM <?= number_format((float) $transaction['amount'], 2) ?></span>
                                <?php else: ?>
                                    <span class="amount-expense">− RM <?= number_format((float) $transaction['amount'], 2) ?></span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= e(date('d M Y', strtotime($transaction['transaction_date']))) ?>
                            </td>

                            <td>
                                <div class="money-action-buttons">
                                    <a href="edit.php?id=<?= e($transaction['transaction_id']) ?>"
                                        class="money-edit-button">Edit</a>
                                    <a href="delete.php?id=<?= e($transaction['transaction_id']) ?>"
                                        class="money-delete-button">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

    <?php endif; ?>

</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>