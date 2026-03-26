<?php
/**
 * test.php
 * Aspirian.pk Online Test System
 * Test interface — displays randomised MCQs and handles submission
 */

require_once __DIR__ . '/functions.php';
requireStudent();

$topic = trim($_GET['topic'] ?? '');

// ── Validate topic ─────────────────────────────────────────
if (empty($topic) || !in_array($topic, TOPICS)) {
    flash('error', 'Invalid topic selected.');
    header('Location: dashboard.php');
    exit;
}

// ── Handle form POST (test submission) ────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    // Recover submitted answers: key = mcq_id, value = a/b/c/d
    $answers = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'answer_') === 0) {
            $mcqId = (int)substr($key, 7);
            $answers[$mcqId] = strtolower(trim($value));
        }
    }

    // Grade the test
    $grading = gradeTest($answers);
    $score   = $grading['score'];
    $total   = $grading['total'];

    // Save result to DB
    saveResult((int)$_SESSION['user_id'], $topic, $score, $total);

    // Store grading details in session for the result page
    $_SESSION['last_result'] = [
        'topic'   => $topic,
        'score'   => $score,
        'total'   => $total,
        'details' => $grading['details'],
    ];

    header('Location: result.php');
    exit;
}

// ── Load MCQs ──────────────────────────────────────────────
$mcqs = getMCQs($topic, MCQS_PER_TEST);

if (empty($mcqs)) {
    flash('error', "No MCQs are available for the topic \"$topic\" yet.");
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($topic) ?> Test — <?= SITE_NAME ?></title>
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

<div class="container" style="padding-top:28px; padding-bottom:40px;">

    <!-- Test Header with Timer -->
    <div class="test-header">
        <div>
            <h2>📝 <?= e($topic) ?> Test</h2>
            <p style="color:#64748b; margin-top:4px; font-size:.9rem;">
                <?= count($mcqs) ?> questions &bull; Answer all questions before submitting
            </p>
        </div>
        <div
            id="countdown-timer"
            class="timer-box ok"
            data-seconds="<?= TEST_TIME ?>"
        >
            <?php
                $m = floor(TEST_TIME / 60);
                $s = TEST_TIME % 60;
                printf('%02d:%02d', $m, $s);
            ?>
        </div>
    </div>

    <!-- MCQ Form -->
    <form id="test-form" method="POST" action="test.php?topic=<?= urlencode($topic) ?>">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div style="counter-reset: mcq;">
            <?php foreach ($mcqs as $mcq): ?>
                <div class="mcq-item">
                    <p class="mcq-question"><?= e($mcq['question']) ?></p>
                    <div class="options">
                        <?php foreach (['a' => $mcq['option_a'], 'b' => $mcq['option_b'], 'c' => $mcq['option_c'], 'd' => $mcq['option_d']] as $letter => $text): ?>
                            <label class="option-label">
                                <input
                                    type="radio"
                                    name="answer_<?= (int)$mcq['id'] ?>"
                                    value="<?= $letter ?>"
                                    required
                                >
                                <span><strong><?= strtoupper($letter) ?>.</strong> <?= e($text) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Submit button -->
        <div style="text-align:center; margin-top:30px;">
            <button
                type="submit"
                class="btn btn-primary"
                style="padding:14px 40px; font-size:1.05rem;"
                onclick="return confirm('Are you sure you want to submit the test?');"
            >
                ✅ Submit Test
            </button>
            <a href="dashboard.php" class="btn btn-light" style="margin-left:12px;" onclick="return confirm('Are you sure? Your progress will be lost.');">
                Cancel
            </a>
        </div>

    </form>
</div>

<!-- Footer -->
<footer class="footer">
    &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.
</footer>

<!-- Countdown Timer Script -->
<script src="assets/js/timer.js"></script>
</body>
</html>
