<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireStudent();

$userId = (int) $_SESSION['user_id'];

$statement = $pdo->prepare(
    'SELECT
        id,
        title,
        content,
        mood,
        journal_date
     FROM diary
     WHERE user_id = ?
     ORDER BY journal_date DESC, id DESC'
);

$statement->execute([$userId]);
$entries = $statement->fetchAll();

$totalEntries = count($entries);
$recentEntries = 0;

foreach ($entries as $entry) {
    if (
        strtotime($entry['journal_date']) >=
        strtotime('-7 days')
    ) {
        $recentEntries++;
    }
}

$rawMood =
    !empty($entries)
    ? $entries[0]['mood']
    : 'None';

$latestMood =
    mb_strlen($rawMood) > 12
    ? mb_substr($rawMood, 0, 10) . '...'
    : $rawMood;

$pageTitle =
    'Diary Journal - Student Routine Organizer';

$activePage = 'diary';
$pageStylesheet = 'diary.css';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="diary-wrapper">
    <section class="diary-hero-card">
        <div class="hero-main-content">
            <svg class="hero-mascot" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true">
                <circle cx="60" cy="60" r="50" fill="#e0e7ff" />

                <rect x="38" y="32" width="44" height="56" rx="8" fill="#6b46c1" />

                <rect x="42" y="36" width="36" height="48" rx="4" fill="#ffffff" />

                <path d="M48 46H72M48 54H72M48 62H64" stroke="#8b5cf6" stroke-width="3" stroke-linecap="round" />

                <circle cx="78" cy="74" r="12" fill="#ffb703" />

                <path d="M74 74H82M78 70V78" stroke="#1e1b4b" stroke-width="2.5" stroke-linecap="round" />
            </svg>

            <div class="hero-text-box">
                <h1>My Diary Journal</h1>

                <p>
                    Record personal thoughts, experiences,
                    moods, and reflections.
                </p>
            </div>
        </div>

        <div class="hero-cta">
            <a href="create.php" class="btn-add-gold">
                <span aria-hidden="true">+</span>
                Add Diary Entry
            </a>
        </div>

        <div class="diary-stats-grid">
            <div class="stat-box">
                <div class="
                        stat-icon-wrapper
                        stat-icon-purple
                    ">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path d="
                                M12 6.253v13
                                m0-13C10.832 5.477
                                9.246 5 7.5 5
                                S4.168 5.477 3 6.253v13
                                C4.168 18.477 5.754 18
                                7.5 18s3.332.477
                                4.5 1.253m0-13
                                C13.168 5.477 14.754 5
                                16.5 5c1.747 0
                                3.332.477 4.5 1.253v13
                                C19.832 18.477 18.247 18
                                16.5 18c-1.746 0
                                -3.332.477-4.5 1.253
                            " />
                    </svg>
                </div>

                <div class="stat-info">
                    <h2><?= $totalEntries ?></h2>
                    <p>Total Entries</p>
                </div>
            </div>

            <div class="stat-box">
                <div class="
                        stat-icon-wrapper
                        stat-icon-blue
                    ">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" />

                        <line x1="16" y1="2" x2="16" y2="6" />

                        <line x1="8" y1="2" x2="8" y2="6" />

                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                </div>

                <div class="stat-info">
                    <h2><?= $recentEntries ?></h2>
                    <p>Entries in Last 7 Days</p>
                </div>
            </div>

            <div class="stat-box">
                <div class="
                        stat-icon-wrapper
                        stat-icon-green
                    ">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path d="
                                M14 9V5a3 3 0 0 0-3-3
                                l-4 9v11h11.28a2 2 0 0 0
                                2-1.7l1.38-9a2 2 0 0 0
                                -2-2.3zM7 22H4a2 2 0 0 1
                                -2-2v-7a2 2 0 0 1
                                2-2h3
                            " />
                    </svg>
                </div>

                <div class="stat-info latest-mood">
                    <h2 title="<?= htmlspecialchars(
                        $rawMood,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>">
                        <?= htmlspecialchars(
                            $latestMood,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </h2>

                    <p>Latest Mood</p>
                </div>
            </div>
        </div>
    </section>

    <?php if (isset($_GET['created'])): ?>
        <div class="diary-success" role="status">
            Diary entry added successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="diary-success" role="status">
            Diary entry updated successfully.
        </div>
    <?php endif; ?>

    <?php if (
        isset($_GET['msg']) &&
        $_GET['msg'] === 'deleted'
    ): ?>
        <div class="diary-success" role="status">
            Diary entry deleted successfully.
        </div>
    <?php endif; ?>

    <div class="diary-feed-container">
        <section class="diary-feed">
            <?php if ($totalEntries > 0): ?>
                <?php foreach ($entries as $entry): ?>
                    <article class="diary-card">
                        <div class="diary-card-header">
                            <h3 class="diary-card-title">
                                <?= htmlspecialchars(
                                    $entry['title'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </h3>

                            <span class="mood-badge" title="Mood: <?= htmlspecialchars(
                                $entry['mood'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>">
                                Mood:
                                <?= htmlspecialchars(
                                    $entry['mood'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </span>
                        </div>

                        <small class="diary-date">
                            <?= htmlspecialchars(
                                date(
                                    'd/m/Y',
                                    strtotime(
                                        $entry['journal_date']
                                    )
                                ),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </small>

                        <p class="diary-content">
                            <?= nl2br(
                                htmlspecialchars(
                                    $entry['content'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                            ) ?>
                        </p>

                        <div class="diary-actions">
                            <a href="edit.php?id=<?= (int) $entry['id'] ?>" class="action-edit">
                                Edit
                            </a>

                            <a href="delete.php?id=<?= (int) $entry['id'] ?>" class="action-delete">
                                Delete
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="diary-empty">
                    <h3>No diary entries yet</h3>

                    <p>
                        Add your first daily reflection to
                        begin tracking your thoughts.
                    </p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>