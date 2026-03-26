<?php
/**
 * admin/mcqs.php
 * Aspirian.pk Online Test System
 * Admin: View and manage all MCQs with topic filtering and pagination
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$pageTitle = '📋 Manage MCQs';

// ── Filters & pagination ───────────────────────────────────
$filterTopic = trim($_GET['topic'] ?? '');
$page        = max(1, (int)($_GET['page'] ?? 1));
$perPage     = 15;
$offset      = ($page - 1) * $perPage;

// Count total rows for pagination
if ($filterTopic) {
    $totalRows = fetchOne('SELECT COUNT(*) AS c FROM mcqs WHERE topic = ?', 's', $filterTopic)['c'] ?? 0;
} else {
    $totalRows = fetchOne('SELECT COUNT(*) AS c FROM mcqs')['c'] ?? 0;
}

$totalPages = max(1, (int)ceil($totalRows / $perPage));

// Fetch MCQs
if ($filterTopic) {
    $mcqs = fetchAll(
        'SELECT * FROM mcqs WHERE topic = ? ORDER BY id DESC LIMIT ? OFFSET ?',
        'sii', $filterTopic, $perPage, $offset
    );
} else {
    $mcqs = fetchAll(
        'SELECT * FROM mcqs ORDER BY id DESC LIMIT ? OFFSET ?',
        'ii', $perPage, $offset
    );
}

include '_header.php';
?>

<!-- Toolbar -->
<div style="display:flex; align-items:center; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
    <form method="GET" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <select name="topic" class="form-control" style="width:220px;" onchange="this.form.submit()">
            <option value="">All Topics</option>
            <?php foreach (TOPICS as $t): ?>
                <option value="<?= e($t) ?>" <?= ($filterTopic === $t) ? 'selected' : '' ?>>
                    <?= e($t) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-light btn-sm">Filter</button>
        <?php if ($filterTopic): ?>
            <a href="mcqs.php" class="btn btn-light btn-sm">Clear</a>
        <?php endif; ?>
    </form>
    <a href="add_mcq.php" class="btn btn-primary btn-sm" style="margin-left:auto;">
        ➕ Add MCQ
    </a>
    <a href="upload_csv.php" class="btn btn-secondary btn-sm">📤 Upload CSV</a>
</div>

<!-- MCQ Table -->
<div class="card">
    <div class="card-header">
        <?= $filterTopic ? e($filterTopic) . ' MCQs' : 'All MCQs' ?>
        <span style="font-weight:400; color:#64748b; font-size:.9rem;">
            (<?= $totalRows ?> total)
        </span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Topic</th>
                        <th>Question</th>
                        <th>Correct</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mcqs)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; color:#94a3b8; padding:30px;">
                                No MCQs found.
                                <a href="add_mcq.php">Add one now</a>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($mcqs as $i => $mcq): ?>
                            <tr>
                                <td><?= $offset + $i + 1 ?></td>
                                <td>
                                    <span class="badge badge-success" style="font-size:.78rem;">
                                        <?= e($mcq['topic']) ?>
                                    </span>
                                </td>
                                <td style="max-width:380px;">
                                    <span title="<?= e($mcq['question']) ?>">
                                        <?= e(mb_strimwidth($mcq['question'], 0, 80, '…')) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="text-transform:uppercase;">
                                        <?= e($mcq['correct_option']) ?>
                                    </strong>
                                    — <?= e($mcq['option_' . $mcq['correct_option']]) ?>
                                </td>
                                <td style="white-space:nowrap;">
                                    <a href="edit_mcq.php?id=<?= (int)$mcq['id'] ?>"
                                       class="btn btn-warning btn-sm">Edit</a>
                                    <a href="delete_mcq.php?id=<?= (int)$mcq['id'] ?><?= $filterTopic ? '&topic='.urlencode($filterTopic) : '' ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Delete this MCQ?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div class="pagination mt-2">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <?php $url = 'mcqs.php?page=' . $p . ($filterTopic ? '&topic='.urlencode($filterTopic) : ''); ?>
        <?php if ($p === $page): ?>
            <span class="current"><?= $p ?></span>
        <?php else: ?>
            <a href="<?= $url ?>"><?= $p ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include '_footer.php'; ?>
