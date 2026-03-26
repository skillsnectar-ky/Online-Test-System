<?php
/**
 * admin/add_mcq.php
 * Aspirian.pk Online Test System
 * Admin: Add a new MCQ to the database
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$pageTitle = '➕ Add New MCQ';
$error     = '';

// ── Handle form POST ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    $topic   = trim($_POST['topic']          ?? '');
    $question = trim($_POST['question']       ?? '');
    $optA    = trim($_POST['option_a']        ?? '');
    $optB    = trim($_POST['option_b']        ?? '');
    $optC    = trim($_POST['option_c']        ?? '');
    $optD    = trim($_POST['option_d']        ?? '');
    $correct = strtolower(trim($_POST['correct_option'] ?? ''));

    // Validation
    if (empty($topic) || empty($question) || empty($optA) || empty($optB) || empty($optC) || empty($optD) || empty($correct)) {
        $error = 'All fields are required.';
    } elseif (!in_array($correct, ['a','b','c','d'], true)) {
        $error = 'Correct option must be a, b, c, or d.';
    } else {
        $result = execute(
            'INSERT INTO mcqs (topic, question, option_a, option_b, option_c, option_d, correct_option)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            'sssssss', $topic, $question, $optA, $optB, $optC, $optD, $correct
        );

        if ($result) {
            flash('success', 'MCQ added successfully!');
            header('Location: add_mcq.php');
            exit;
        } else {
            $error = 'Failed to add MCQ. Please try again.';
        }
    }
}

include '_header.php';
?>

<div class="card" style="max-width:720px;">
    <div class="card-header">
        ➕ Add New MCQ
        <a href="mcqs.php" class="btn btn-light btn-sm">← All MCQs</a>
    </div>
    <div class="card-body">

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="add_mcq.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <!-- Topic -->
            <div class="form-group">
                <label for="topic">Topic</label>
                <select id="topic" name="topic" class="form-control" required>
                    <option value="">— Select Topic —</option>
                    <?php foreach (TOPICS as $t): ?>
                        <option value="<?= e($t) ?>" <?= (($_POST['topic'] ?? '') === $t) ? 'selected' : '' ?>>
                            <?= e($t) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Question -->
            <div class="form-group">
                <label for="question">Question</label>
                <textarea
                    id="question"
                    name="question"
                    class="form-control"
                    rows="3"
                    placeholder="Enter the question..."
                    required
                    style="resize:vertical;"
                ><?= e($_POST['question'] ?? '') ?></textarea>
            </div>

            <!-- Options grid -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <?php foreach (['a','b','c','d'] as $letter): ?>
                    <div class="form-group">
                        <label for="option_<?= $letter ?>">Option <?= strtoupper($letter) ?></label>
                        <input
                            type="text"
                            id="option_<?= $letter ?>"
                            name="option_<?= $letter ?>"
                            class="form-control"
                            placeholder="Option <?= strtoupper($letter) ?>"
                            value="<?= e($_POST["option_$letter"] ?? '') ?>"
                            required
                        >
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Correct option -->
            <div class="form-group">
                <label for="correct_option">Correct Option</label>
                <select id="correct_option" name="correct_option" class="form-control" required>
                    <option value="">— Select Correct Answer —</option>
                    <?php foreach (['a'=>'A','b'=>'B','c'=>'C','d'=>'D'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= (($_POST['correct_option'] ?? '') === $val) ? 'selected' : '' ?>>
                            Option <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:flex; gap:12px; margin-top:8px;">
                <button type="submit" class="btn btn-primary">Save MCQ</button>
                <a href="mcqs.php" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '_footer.php'; ?>
