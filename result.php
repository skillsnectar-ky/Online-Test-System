<?php
/**
 * result.php
 * Aspirian.pk Online Test System
 * Result display page — shows score after test submission
 */

require_once __DIR__ . '/functions.php';
requireStudent();

// ── Retrieve result from session ──────────────────────────
if (empty($_SESSION['last_result'])) {
    flash('error', 'No result data found. Please take a test first.');
    header('Location: dashboard.php');
    exit;
}

$result  = $_SESSION['last_result'];
unset($_SESSION['last_result']); // Consume so it can't be refreshed

$topic   = $result['topic'];
$score   = (int)$result['score'];
$total   = (int)$result['total'];
$details = $result['details'];
$pct     = $total > 0 ? round($score / $total * 100) : 0;

// Result message based on percentage
if ($pct >= 80) {
    $message = '🏆 Excellent! Outstanding performance!';
    $msgClass = 'alert-success';
} elseif ($pct >= 60) {
    $message = '👍 Good job! Keep it up!';
    $msgClass = 'alert-info';
} elseif ($pct >= 40) {
    $message = '📚 Average performance. Keep practising!';
    $msgClass = 'alert-warning';
} else {
    $message = '😟 Below average. Don\'t give up — try again!';
    $msgClass = 'alert-error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Result — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php"><?= SITE_NAME ?></a>
        <div class="navbar-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container" style="padding-top:32px; padding-bottom:48px;">

    <!-- Score Summary Card -->
    <div class="card mb-3">
        <div class="card-body result-summary">
            <div class="score-circle">
                <span class="score-num"><?= $score ?>/<?= $total ?></span>
                <span class="score-lbl">Score</span>
            </div>

            <h2 style="font-size:1.6rem; margin-bottom:6px;"><?= e($topic) ?> Test Result</h2>
            <p style="color:#64748b; margin-bottom:18px;">
                You scored <strong><?= $score ?></strong> out of <strong><?= $total ?></strong>
                &mdash; <strong><?= $pct ?>%</strong>
            </p>

            <div class="alert <?= $msgClass ?>" style="display:inline-block; max-width:500px;">
                <?= $message ?>
            </div>

            <div style="margin-top:24px; display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <a href="test.php?topic=<?= urlencode($topic) ?>" class="btn btn-primary">
                    🔁 Retake Test
                </a>
                <a href="dashboard.php" class="btn btn-light">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Detailed Review -->
    <?php if (!empty($details)): ?>
    <div class="card">
        <div class="card-header">📋 Detailed Answer Review</div>
        <div class="card-body">
            <div class="result-review" style="counter-reset: mcq;">
                <?php foreach ($details as $item): ?>
                    <div class="result-item <?= $item['is_correct'] ? '' : 'wrong' ?>">
                        <p class="result-q"><?= e($item['question']) ?></p>

                        <div style="display:flex; flex-direction:column; gap:6px; font-size:.9rem;">
                            <!-- Your answer -->
                            <div style="display:flex; align-items:flex-start; gap:8px;">
                                <span style="min-width:120px; font-weight:600; color:#64748b;">Your Answer:</span>
                                <span>
                                    <?php if ($item['submitted'] && isset($item['submitted_text'])): ?>
                                        <strong><?= strtoupper($item['submitted']) ?>.</strong>
                                        <?= e($item['submitted_text']) ?>
                                        <?php if ($item['is_correct']): ?>
                                            <span class="badge badge-success ml-1">✓ Correct</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger ml-1">✗ Wrong</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color:#94a3b8;">Not answered</span>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <!-- Correct answer (only shown if wrong) -->
                            <?php if (!$item['is_correct']): ?>
                            <div style="display:flex; align-items:flex-start; gap:8px;">
                                <span style="min-width:120px; font-weight:600; color:#16a34a;">Correct Answer:</span>
                                <span style="color:#16a34a; font-weight:600;">
                                    <?= strtoupper($item['correct']) ?>.
                                    <?= e($item['correct_text']) ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Footer -->
<footer class="footer">
    &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.
</footer>

</body>
</html>
