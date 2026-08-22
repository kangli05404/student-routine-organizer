<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireStudent();

$user_id = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM diary WHERE user_id = ? ORDER BY journal_date DESC");
$stmt->execute([$user_id]);
$entries = $stmt->fetchAll();

$total_entries = count($entries);
$recent_entries = 0;

foreach ($entries as $entry) {
    if (strtotime($entry['journal_date']) >= strtotime('-7 days')) {
        $recent_entries++;
    }
}

// Full mood string for hover title
$raw_mood = !empty($entries) ? $entries[0]['mood'] : 'None';

// Truncate mood if it exceeds 12 characters to prevent stat card overflow
$latest_mood = (mb_strlen($raw_mood) > 12) ? mb_substr($raw_mood, 0, 10) . '...' : $raw_mood;

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/
$pageTitle = 'Diary Journal - Student Routine Organizer';
$activePage = 'diary';
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

        <!-- Hero Card Section -->
        <section class="diary-hero-card">
            <div class="hero-main-content">
                <svg class="hero-mascot" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="60" cy="60" r="50" fill="#e0e7ff" />
                    <rect x="38" y="32" width="44" height="56" rx="8" fill="#6b46c1" />
                    <rect x="42" y="36" width="36" height="48" rx="4" fill="#ffffff" />
                    <path d="M48 46H72M48 54H72M48 62H64" stroke="#8b5cf6" stroke-width="3" stroke-linecap="round" />
                    <circle cx="78" cy="74" r="12" fill="#ffb703" />
                    <path d="M74 74L82 74M78 70L78 78" stroke="#1e1b4b" stroke-width="2.5" stroke-linecap="round" />
                </svg>

                <div class="hero-text-box">
                    <h1>My Diary Journal</h1>
                    <p>Record personal thoughts, experiences, moods, and reflections.</p>
                </div>
            </div>

            <div class="hero-cta">
                <a href="create.php" class="btn-add-gold">
                    <span>+</span> Add Diary Entry
                </a>
            </div>

            <!-- 3-Column Stat Cards -->
            <div class="diary-stats-grid">
                <div class="stat-box">
                    <div class="stat-icon-wrapper stat-icon-purple">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo $total_entries; ?></h2>
                        <p>Total Entries</p>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon-wrapper stat-icon-blue">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo $recent_entries; ?></h2>
                        <p>Entries This Week</p>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon-wrapper stat-icon-green">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path
                                d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3">
                            </path>
                        </svg>
                    </div>
                    <div class="stat-info" style="min-width: 0;">
                        <h2 title="<?php echo htmlspecialchars($raw_mood); ?>" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?php echo htmlspecialchars($latest_mood); ?>
                        </h2>
                        <p>Latest Mood</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feed Section -->
        <div class="diary-feed-container">
            <section class="diary-feed">
                <?php if ($total_entries > 0): ?>
                    <?php foreach ($entries as $row): ?>
                        <article class="diary-card" style="word-wrap: break-word; overflow-wrap: break-word;">
                            <div class="diary-card-header" style="flex-wrap: wrap; gap: 8px;">
                                <h3 class="diary-card-title" style="word-break: break-word;"><?php echo htmlspecialchars($row['title']); ?></h3>
                                <span class="mood-badge" style="max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="Mood: <?php echo htmlspecialchars($row['mood']); ?>">
                                    Mood: <?php echo htmlspecialchars($row['mood']); ?>
                                </span>
                            </div>
                            <small class="diary-date"><?php echo $row['journal_date']; ?></small>
                            <p class="diary-content" style="word-break: break-word;"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>

                            <div class="diary-actions">
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="action-edit">Edit</a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="action-delete">Delete</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="diary-empty">
                        <h3>No diary entries yet</h3>
                        <p>Add your first daily reflection to begin tracking your thoughts.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>

</html>