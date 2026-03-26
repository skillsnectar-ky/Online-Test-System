<?php
/**
 * admin/edit_mcq.php
 * Aspirian.pk Online Test System
 * Admin: Edit an existing MCQ
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$pageTitle = '✏️ Edit MCQ';
$error     = '';

// ── Load MCQ by ID ─────────────────────────────────────────
$id  = (int)($_GET['id'] ?? 0);
$mcq = fetchOne('SELECT * FROM mcqs WHERE id = ? LIMIT 1', 'i', $id);

if (!$mcq) {
    flash('error', 'MCQ not found.');
    header('Location: mcqs.php');
    exit;
}

// ── Handle form POST ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    $topic    = trim($_POST['topic']          ?? '');
    $question = trim($_POST['question']       ?? '');
    $optA     = trim($_POST['option_a']       ?? '');
    $optB     = trim($_POST['option_b']       ?? '');
    $optC     = trim($_POST['option_c']       ?? '');
    $optD     = trim($_POST['option_d']       ?? '');
    $correct  = strtolower(trim($_POST['correct_option'] ?? ''));

    if (empty($topic) || empty($question) || empty($optA) || empty($optB) || empty($optC) || empty($optD) || empty($correct)) {
        $error = 'All fields are required.';
    } elseif (!in_array($correct, ['a','b','c','d'], true)) {
        $error = 'Correct option must be a, b, c, or d.';
    } else {
        $result = execute(
            'UPDATE mcqs SET topic=?, question=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_option=?
             WHERE id=?',
            'sssssssi', $topic, $question, $optA, $optB, $optC, $optD, $correct, $id
        );

        if ($result !== false) {
            flash('success', 'MCQ updated successfully!');
            header('Location: mcqs.php?topic=' . urlencode($topic));
            exit;
        } else {
            $error = 'Failed to update MCQ. Please try again.';
        }
    }

    // Reload MCQ data with posted values on error
    $mcq = array_merge($mcq, $_POST);
}

include '_header.php';
?>

<div class="card" style="max-width:720px;">
    <div class="card-header">
        ✏️ Edit MCQ #<?= $id ?>
        <a href="mcqs.php" class="btn btn-light btn-sm">← All MCQs</a>
    </div>
    <div class="card-body">

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="edit_mcq.php?id=<?= $id ?>" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <!-- Topic -->
            <div class="form-group">
                <label for="topic">Topic</label>
                <select id="topic" name="topic" class="form-control" required>
                    <option value="">— Select Topic —</option>
                    <?php foreach (TOPICS as $t): ?>
                        <option value="<?= e($t) ?>" <?= ($mcq['topic'] === $t) ? 'selected' : '' ?>>
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
                    required
                    style="resize:vertical;"
                ><?= e($mcq['question']) ?></textarea>
            </div>

            <!-- Options -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <?php foreach (['a','b','c','d'] as $letter): ?>
                    <div class="form-group">
                        <label for="option_<?= $letter ?>">Option <?= strtoupper($letter) ?></label>
                        <input
                            type="text"
                            id="option_<?= $letter ?>"
                            name="option_<?= $letter ?>"
                            class="form-control"
                            value="<?= e($mcq['option_' . $letter]) ?>"
                            required
                        >
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Correct option -->
            <div class="form-group">
                <label for="correct_option">Correct Option</label>
                <select id="correct_option" name="correct_option" class="form-control" required>
                    <?php foreach (['a'=>'A','b'=>'B','c'=>'C','d'=>'D'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($mcq['correct_option'] === $val) ? 'selected' : '' ?>>
                            Option <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:flex; gap:12px; margin-top:8px;">
                <button type="submit" class="btn btn-primary">Update MCQ</button>
                <a href="mcqs.php" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '_footer.php'; ?>
