<?php
/**
 * dashboard.php
 * Aspirian.pk Online Test System
 * Student dashboard — shows all available test topics
 */

require_once __DIR__ . '/functions.php';
requireStudent();

// Icons mapped to each topic (emoji for simplicity, no extra libs needed)
$topicIcons = [
    'MS Word'                    => '📝',
    'MS Excel'                   => '📊',
    'PowerPoint'                 => '📽️',
    'Internet'                   => '🌐',
    'Urdu InPage'                => '✒️',
    'Introduction to Computer'   => '💻',
];

// Get the topics with at least one MCQ in the DB
$availableTopics = getTopicsWithMCQs();

// Student's recent results (last 5)
$recentResults = fetchAll(
    'SELECT r.*, u.name FROM results r
     JOIN users u ON u.id = r.user_id
     WHERE r.user_id = ?
     ORDER BY r.date DESC
     LIMIT 5',
    'i', $_SESSION['user_id']
);

// Stats: total tests taken and average score
$stats = fetchOne(
    'SELECT COUNT(*) AS total_tests,
            IFNULL(AVG(score / total * 100), 0) AS avg_pct
     FROM results
     WHERE user_id = ?',
    'i', $_SESSION['user_id']
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php"><?= SITE_NAME ?></a>
        <div class="navbar-nav">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h2>Welcome, <?= e($_SESSION['name']) ?>!</h2>
        <p>Select a topic below to start your test.</p>
    </div>
</div>

<!-- Main Content -->
<div class="container">

    <?= renderFlash() ?>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-num"><?= (int)($stats['total_tests'] ?? 0) ?></div>
            <div class="stat-lbl">Tests Taken</div>
        </div>
        <div class="stat-card">
            <div class="stat-num"><?= number_format($stats['avg_pct'] ?? 0, 1) ?>%</div>
            <div class="stat-lbl">Average Score</div>
        </div>
        <div class="stat-card">
            <div class="stat-num"><?= count($availableTopics) ?></div>
            <div class="stat-lbl">Topics Available</div>
        </div>
        <div class="stat-card">
            <div class="stat-num"><?= MCQS_PER_TEST ?></div>
            <div class="stat-lbl">MCQs Per Test</div>
        </div>
    </div>

    <!-- Available Topics -->
    <div class="card mb-3">
        <div class="card-header">📚 Available Test Topics</div>
        <div class="card-body">
            <?php if (empty($availableTopics)): ?>
                <div class="alert alert-info">
                    No topics are available right now. Please check back later.
                </div>
            <?php else: ?>
                <div class="topic-grid">
                    <?php foreach ($availableTopics as $row): ?>
                        <?php
                            $t    = $row['topic'];
                            $icon = $topicIcons[$t] ?? '📋';
                        ?>
                        <a class="topic-card" href="test.php?topic=<?= urlencode($t) ?>">
                            <div class="icon"><?= $icon ?></div>
                            <h3><?= e($t) ?></h3>
                            <p>Start <?= MCQS_PER_TEST ?> MCQ test &rarr;</p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Results -->
    <?php if (!empty($recentResults)): ?>
    <div class="card mb-3">
        <div class="card-header">📈 Your Recent Results</div>
        <div class="card-body">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Topic</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentResults as $i => $r): ?>
                            <?php
                                $pct    = $r['total'] > 0 ? round($r['score'] / $r['total'] * 100) : 0;
                                $badge  = $pct >= 50 ? 'badge-success' : 'badge-danger';
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= e($r['topic']) ?></td>
                                <td><?= (int)$r['score'] ?> / <?= (int)$r['total'] ?></td>
                                <td>
                                    <span class="badge <?= $badge ?>"><?= $pct ?>%</span>
                                </td>
                                <td><?= date('d M Y, h:i A', strtotime($r['date'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /.container -->

<!-- Footer -->
<footer class="footer">
    &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.
</footer>

</body>
</html>
