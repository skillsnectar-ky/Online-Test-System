<?php
/**
 * dashboard.php
 * Aspirian.pk Online Test System
 * Student dashboard — shows all available test topics
 */

require_once __DIR__ . '/functions.php';
requireStudent();

// ── Handle delete result ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_result_id'])) {
    csrfVerify();
    $rid = (int)$_POST['delete_result_id'];
    if (deleteResult($rid, (int)$_SESSION['user_id'])) {
        flash('success', 'Result deleted successfully.');
    } else {
        flash('error', 'Could not delete result.');
    }
    header('Location: dashboard.php');
    exit;
}

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
        <a class="navbar-brand" href="dashboard.php">
            <img src="assets/images/logo.png" alt="<?= SITE_NAME ?>" onerror="this.style.display='none'">
        </a>
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
                            <th>Action</th>
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
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm"
                                        onclick="confirmDelete(<?= (int)$r['id'] ?>, '<?= e($r['topic']) ?>')"
                                    >🗑 Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /.container -->

<!-- ── Delete Confirm Modal ──────────────────────────────── -->
<div id="delete-modal" style="
    display:none; position:fixed; inset:0; z-index:9999;
    background:rgba(10,31,53,.80); backdrop-filter:blur(4px);
    align-items:center; justify-content:center;
">
    <div style="
        background:#0a1f35; border-radius:14px; padding:38px 34px;
        width:100%; max-width:400px; margin:0 20px;
        border-top:4px solid #c0392b;
        box-shadow:0 20px 70px rgba(0,0,0,.7);
        text-align:center;
    ">
        <div style="font-size:3rem; margin-bottom:14px;">🗑️</div>
        <h3 style="color:#fff; font-size:1.2rem; margin-bottom:10px; font-weight:700;">Delete Result?</h3>
        <p id="delete-modal-msg" style="color:rgba(255,255,255,.68); font-size:.93rem; margin-bottom:28px; line-height:1.65;"></p>
        <form method="POST" action="dashboard.php" id="delete-form">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="delete_result_id" id="delete-result-id" value="">
            <div style="display:flex; gap:12px; justify-content:center;">
                <button type="button" onclick="closeDeleteModal()" style="
                    padding:11px 30px; border-radius:8px;
                    border:1.5px solid rgba(255,255,255,.25);
                    background:transparent; color:rgba(255,255,255,.8);
                    font-size:.95rem; font-weight:600; cursor:pointer;
                ">Cancel</button>
                <button type="submit" style="
                    padding:11px 30px; border-radius:8px; border:none;
                    background:#c0392b; color:#fff;
                    font-size:.95rem; font-weight:700; cursor:pointer;
                ">Yes, Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmDelete(id, topic) {
    document.getElementById('delete-result-id').value = id;
    document.getElementById('delete-modal-msg').textContent =
        'Are you sure you want to delete your "' + topic + '" result? This cannot be undone.';
    document.getElementById('delete-modal').style.display = 'flex';
}
function closeDeleteModal() {
    document.getElementById('delete-modal').style.display = 'none';
}
document.getElementById('delete-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>

<!-- Footer -->
<footer class="footer">
    &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.
</footer>

</body>
</html>
