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
        <a class="navbar-brand" href="dashboard.php">
            <img src="assets/images/logo.png" alt="<?= SITE_NAME ?>" onerror="this.style.display='none'">
        </a>
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
                type="button"
                class="btn btn-primary"
                style="padding:14px 40px; font-size:1.05rem;"
                onclick="showSubmitModal()"
            >
                ✅ Submit Test
            </button>
            <button type="button" class="btn btn-light" style="margin-left:12px;" onclick="showCancelModal()">
                Cancel
            </button>
        </div>

    </form>
</div>

<!-- ── Custom Confirm Modal ─────────────────────────────── -->
<div id="confirm-modal" style="
    display:none; position:fixed; inset:0; z-index:9999;
    background:rgba(10,31,53,.80); backdrop-filter:blur(4px);
    align-items:center; justify-content:center;
">
    <div style="
        background:#0a1f35; border-radius:14px; padding:38px 34px;
        width:100%; max-width:420px; margin:0 20px;
        border-top:4px solid #0cc0c0;
        box-shadow:0 20px 70px rgba(0,0,0,.7);
        text-align:center;
    ">
        <div id="modal-icon" style="font-size:3rem; margin-bottom:14px;"></div>
        <h3 id="modal-title" style="color:#fff; font-size:1.25rem; margin-bottom:10px; font-weight:700;"></h3>
        <p id="modal-msg" style="color:rgba(255,255,255,.68); font-size:.93rem; margin-bottom:28px; line-height:1.65;"></p>
        <div style="display:flex; gap:12px; justify-content:center;">
            <button onclick="closeModal()" style="
                padding:11px 30px; border-radius:8px;
                border:1.5px solid rgba(255,255,255,.25);
                background:transparent; color:rgba(255,255,255,.8);
                font-size:.95rem; font-weight:600; cursor:pointer;
            ">Cancel</button>
            <button id="modal-ok-btn" style="
                padding:11px 30px; border-radius:8px; border:none;
                background:#0cc0c0; color:#fff;
                font-size:.95rem; font-weight:700; cursor:pointer;
            "></button>
        </div>
    </div>
</div>

<script>
var _pendingAction = null;

function showModal(icon, title, msg, okText, action) {
    document.getElementById('modal-icon').textContent  = icon;
    document.getElementById('modal-title').textContent = title;
    document.getElementById('modal-msg').textContent   = msg;
    document.getElementById('modal-ok-btn').textContent = okText;
    _pendingAction = action;
    var m = document.getElementById('confirm-modal');
    m.style.display = 'flex';
}

function closeModal() {
    document.getElementById('confirm-modal').style.display = 'none';
    _pendingAction = null;
}

document.getElementById('modal-ok-btn').addEventListener('click', function () {
    closeModal();
    if (_pendingAction) _pendingAction();
});

// Close modal on backdrop click
document.getElementById('confirm-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

function showSubmitModal() {
    showModal(
        '📝',
        'Submit Test?',
        'Are you sure you want to submit? You cannot change your answers after submission.',
        'Submit Now',
        function () {
            window._testSubmitting = true;
            document.getElementById('test-form').submit();
        }
    );
}

function showCancelModal() {
    showModal(
        '⚠️',
        'Cancel Test?',
        'Your progress will not be saved. Are you sure you want to go back to the dashboard?',
        'Yes, Cancel',
        function () {
            window._testSubmitting = true;
            window.location.href = 'dashboard.php';
        }
    );
}
</script>

<!-- Footer -->
<footer class="footer">
    &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.
</footer>

<!-- Countdown Timer Script -->
<script src="assets/js/timer.js"></script>
</body>
</html>
