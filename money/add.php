<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];

$error = '';

$transactionType = '';
$amount = '';
$category = '';
$description = '';
$transactionDate = date('Y-m-d');

// Income categories
$incomeCategories = [
    'Salary',
    'Allowance',
    'Others'
];

// Expense categories
$expenseCategories = [
    'Food',
    'Transport',
    'Education',
    'Shopping',
    'Entertainment',
    'Bills',
    'Others'
];

// All categories combined
$allCategories = array_merge($incomeCategories, $expenseCategories);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $transactionType = trim($_POST['transaction_type'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $transactionDate = trim($_POST['transaction_date'] ?? '');

    if (
        $transactionType === '' ||
        $amount === '' ||
        $category === '' ||
        $transactionDate === ''
    ) {
        $error = 'Please fill in all required fields.';
    } elseif (
        !in_array($transactionType, ['Income', 'Expense'], true)
    ) {
        $error = 'Please select a valid transaction type.';
    } elseif (
        !is_numeric($amount) ||
        (float) $amount <= 0
    ) {
        $error = 'Please enter a valid amount.';
    } elseif (
        !in_array($category, $allCategories, true)
    ) {
        $error = 'Please select a valid category.';
    } else {
        $validDate = DateTime::createFromFormat('Y-m-d', $transactionDate);

        if (
            !$validDate ||
            $validDate->format('Y-m-d') !== $transactionDate
        ) {
            $error = 'Please enter a valid transaction date.';
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO transactions (
                    user_id,
                    transaction_type,
                    amount,
                    category,
                    description,
                    transaction_date
                )
                VALUES (?, ?, ?, ?, ?, ?)'
            );

            $statement->execute([
                $userId,
                $transactionType,
                (float) $amount,
                $category,
                $description,
                $transactionDate
            ]);

            header('Location: index.php?created=1');
            exit;
        }
    }
}

$pageTitle = 'Add Transaction';
$activePage = 'money';
$pageStylesheet = 'money.css';

require_once __DIR__ . '/../includes/header.php';

?>

<section class="money-form-page">

    <div class="money-form-header">
        <span class="money-label">Money Tracker</span>
        <h1>Add Transaction</h1>
        <p>Record your income or expense.</p>
    </div>

    <?php if ($error !== ''): ?>
        <div class="money-error" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="money-form-card">
        <form method="post">

            <div class="money-form-group">
                <label for="transaction_type">
                    Transaction Type <span class="required">*</span>
                </label>
                <select id="transaction_type" name="transaction_type" required onchange="updateCategories()">
                    <option value="">Select Type</option>
                    <option value="Income" <?= $transactionType === 'Income' ? 'selected' : '' ?>>Income</option>
                    <option value="Expense" <?= $transactionType === 'Expense' ? 'selected' : '' ?>>Expense</option>
                </select>
            </div>

            <div class="money-form-group">
                <label for="amount">
                    Amount (RM) <span class="required">*</span>
                </label>
                <input id="amount" type="number" name="amount" step="0.01" min="0.01"
                    value="<?= htmlspecialchars($amount, ENT_QUOTES, 'UTF-8') ?>" placeholder="0.00" required>
            </div>

            <div class="money-form-group">
                <label for="category">
                    Category <span class="required">*</span>
                </label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <?php foreach ($allCategories as $item): ?>
                        <option value="<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>"
                            data-type="<?= in_array($item, $incomeCategories) ? 'Income' : 'Expense' ?>"
                            <?= $category === $item ? 'selected' : '' ?>> <?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                </select>
            </div>

            <div class="money-form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"
                    placeholder="Enter description (optional)"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="money-form-group">
                <label for="transaction_date">
                    Transaction Date <span class="required">*</span>
                </label>
                <input id="transaction_date" type="date" name="transaction_date"
                    value="<?= htmlspecialchars($transactionDate, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="money-form-actions">
                <button type="submit" class="money-save-button">💾 Save Transaction</button>
                <a href="index.php" class="money-cancel-button">Cancel</a>
            </div>

        </form>
    </div>

</section>

<script>
    const incomeCategories = <?= json_encode($incomeCategories) ?>;
    const expenseCategories = <?= json_encode($expenseCategories) ?>;

    function updateCategories() {
        const typeSelect = document.getElementById('transaction_type');
        const categorySelect = document.getElementById('category');
        const selectedType = typeSelect.value;

        while (categorySelect.options.length > 0) {
            categorySelect.remove(0);
        }

        const emptyOption = document.createElement('option');
        emptyOption.value = '';
        emptyOption.textContent = 'Select Category';
        categorySelect.appendChild(emptyOption);

        let categoriesToShow = [];
        if (selectedType === 'Income') {
            categoriesToShow = incomeCategories;
        } else if (selectedType === 'Expense') {
            categoriesToShow = expenseCategories;
        } else {
            categoriesToShow = [...incomeCategories, ...expenseCategories];
        }

        categoriesToShow.forEach(category => {
            const option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            <?php if ($category !== ''): ?>
                if (category === '<?= addslashes($category) ?>') {
                    option.selected = true;
                }
            <?php endif; ?>
            categorySelect.appendChild(option);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateCategories();
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>