<?php
/**
 * admin/dashboard.php
 * Aspirian.pk Online Test System
 * Admin dashboard — overview stats and quick actions
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$pageTitle = '🏠 Admin Dashboard';

// ── Aggregate statistics ───────────────────────────────────
$totalStudents = fetchOne("SELECT COUNT(*) AS c FROM users WHERE role='student'")['c'] ?? 0;
$totalMCQs     = fetchOne("SELECT COUNT(*) AS c FROM mcqs")['c'] ?? 0;
$totalResults  = fetchOne("SELECT COUNT(*) AS c FROM results")['c'] ?? 0;
$avgScore      = fetchOne(
    "SELECT IFNULL(AVG(score/total*100),0) AS avg FROM results WHERE total > 0"
)['avg'] ?? 0;

// MCQ counts per topic
$topicCounts = fetchAll(
    "SELECT topic, COUNT(*) AS cnt FROM mcqs GROUP BY topic ORDER BY topic ASC"
);

// Latest 10 results
$latestResults = fetchAll(
    "SELECT r.*, u.name AS student_name
     FROM results r
     JOIN users u ON u.id = r.user_id
     ORDER BY r.date DESC
     LIMIT 10"
);

include '_header.php';
?>

<!-- Stats -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-num"><?= (int)$totalStudents ?></div>
        <div class="stat-lbl">Total Students</div>
    </div>
    <div class="stat-card">
        <div class="stat-num"><?= (int)$totalMCQs ?></div>
        <div class="stat-lbl">Total MCQs</div>
    </div>
    <div class="stat-card">
        <div class="stat-num"><?= (int)$totalResults ?></div>
        <div class="stat-lbl">Tests Taken</div>
    </div>
    <div class="stat-card">
        <div class="stat-num"><?= number_format($avgScore, 1) ?>%</div>
        <div class="stat-lbl">Average Score</div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:22px; flex-wrap:wrap;" class="mb-3">

    <!-- MCQs per topic -->
    <div class="card">
        <div class="card-header">
            📚 MCQs by Topic
            <a href="add_mcq.php" class="btn btn-primary btn-sm">+ Add MCQ</a>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Topic</th>
                            <th>MCQ Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topicCounts as $tc): ?>
                            <tr>
                                <td><?= e($tc['topic']) ?></td>
                                <td><?= (int)$tc['cnt'] ?></td>
                                <td>
                                    <a href="mcqs.php?topic=<?= urlencode($tc['topic']) ?>"
                                       class="btn btn-light btn-sm">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($topicCounts)): ?>
                            <tr><td colspan="3" style="color:#94a3b8; text-align:center;">No MCQs yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">⚡ Quick Actions</div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:12px;">
            <a href="add_mcq.php" class="btn btn-primary">➕ Add New MCQ</a>
            <a href="upload_csv.php" class="btn btn-secondary">📤 Upload MCQs via CSV</a>
            <a href="mcqs.php" class="btn btn-warning">📋 Manage All MCQs</a>
            <a href="results.php" class="btn btn-success">📊 View Student Results</a>
            <a href="students.php" class="btn btn-light">👥 Manage Students</a>
        </div>
    </div>
</div>

<!-- Latest Results -->
<div class="card">
    <div class="card-header">
        🕐 Latest Test Results
        <a href="results.php" class="btn btn-light btn-sm">View All</a>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Topic</th>
                        <th>Score</th>
                        <th>%</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($latestResults as $r): ?>
                        <?php $pct = $r['total'] > 0 ? round($r['score']/$r['total']*100) : 0; ?>
                        <tr>
                            <td><?= e($r['student_name']) ?></td>
                            <td><?= e($r['topic']) ?></td>
                            <td><?= (int)$r['score'] ?>/<?= (int)$r['total'] ?></td>
                            <td>
                                <span class="badge <?= $pct >= 50 ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $pct ?>%
                                </span>
                            </td>
                            <td><?= date('d M Y, h:i A', strtotime($r['date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($latestResults)): ?>
                        <tr><td colspan="5" style="text-align:center;color:#94a3b8;">No results yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '_footer.php'; ?>
