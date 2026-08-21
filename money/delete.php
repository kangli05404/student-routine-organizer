<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];

$transactionId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$transactionId) {
    http_response_code(400);
    exit('Invalid transaction record.');
}

$statement = $pdo->prepare(
    'SELECT
        transaction_id,
        transaction_type,
        amount,
        category,
        description,
        transaction_date
     FROM transactions
     WHERE transaction_id = ?
       AND user_id = ?'
);

$statement->execute([
    $transactionId,
    $userId
]);

$transaction = $statement->fetch();

if (!$transaction) {
    http_response_code(404);
    exit('Transaction record not found.');
}

$error = '';

if (empty($_SESSION['delete_transaction_token'])) {
    $_SESSION['delete_transaction_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $submittedToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['delete_transaction_token'], $submittedToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $delete = $pdo->prepare(
            'DELETE FROM transactions
             WHERE transaction_id = ?
               AND user_id = ?'
        );

        $delete->execute([
            $transactionId,
            $userId
        ]);

        unset($_SESSION['delete_transaction_token']);

        header('Location: index.php?deleted=1');
        exit;
    }
}

$pageTitle = 'Delete Transaction';
$activePage = 'money';
$pageStylesheet = 'money.css';

require_once __DIR__ . '/../includes/header.php';

?>

<section class="money-delete-page">

    <div class="money-form-header">
        <span class="money-label">Money Tracker</span>
        <h1>Delete Transaction</h1>
        <p>Please confirm before permanently deleting this transaction.</p>
    </div>

    <?php if ($error !== ''): ?>
        <div class="money-error" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="money-delete-card">

        <div class="money-warning-icon">!</div>

        <h2>Are you sure?</h2>

        <p class="money-delete-warning">
            This transaction will be permanently deleted.
            This action cannot be undone.
        </p>

        <dl class="money-record-summary">
            <div>
                <dt>Type</dt>
                <dd>
                    <?php if ($transaction['transaction_type'] === 'Income'): ?>
                        <span class="type-badge income"><span class="dot"></span> Income</span>
                    <?php else: ?>
                        <span class="type-badge expense"><span class="dot"></span> Expense</span>
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt>Category</dt>
                <dd><?= htmlspecialchars($transaction['category'], ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
            <div>
                <dt>Amount</dt>
                <dd>
                    <?php if ($transaction['transaction_type'] === 'Income'): ?>
                        <span class="amount-income">+ RM <?= number_format((float) $transaction['amount'], 2) ?></span>
                    <?php else: ?>
                        <span class="amount-expense">− RM <?= number_format((float) $transaction['amount'], 2) ?></span>
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt>Date</dt>
                <dd><?= htmlspecialchars(date('d M Y', strtotime($transaction['transaction_date'])), ENT_QUOTES, 'UTF-8') ?>
                </dd>
            </div>
            <?php if (!empty($transaction['description'])): ?>
                <div>
                    <dt>Description</dt>
                    <dd><?= htmlspecialchars($transaction['description'], ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
            <?php endif; ?>
        </dl>

        <form method="post">
            <input type="hidden" name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['delete_transaction_token'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="money-delete-actions">
                <button type="submit" class="money-danger-button">🗑 Yes, Delete</button>
                <a href="index.php" class="money-cancel-button">Cancel</a>
            </div>
        </form>

    </div>

</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>