<?php
/**
 * admin/results.php
 * Aspirian.pk Online Test System
 * Admin: View all student test results with topic and student filtering
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$pageTitle = '📊 Student Results';

// ── Filters ────────────────────────────────────────────────
$filterTopic   = trim($_GET['topic']   ?? '');
$filterStudent = trim($_GET['student'] ?? '');
$page          = max(1, (int)($_GET['page'] ?? 1));
$perPage       = 20;
$offset        = ($page - 1) * $perPage;

// Build dynamic WHERE clause
$where  = [];
$types  = '';
$params = [];

if ($filterTopic) {
    $where[]  = 'r.topic = ?';
    $types   .= 's';
    $params[] = $filterTopic;
}
if ($filterStudent) {
    $where[]  = 'u.name LIKE ?';
    $types   .= 's';
    $params[] = '%' . $filterStudent . '%';
}

$whereSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Count
$countSQL  = "SELECT COUNT(*) AS c FROM results r JOIN users u ON u.id = r.user_id $whereSQL";
$totalRows = ($types ? fetchOne($countSQL, $types, ...$params) : fetchOne($countSQL))['c'] ?? 0;
$totalPages = max(1, (int)ceil($totalRows / $perPage));

// Fetch results
$sql  = "SELECT r.*, u.name AS student_name, u.email AS student_email
         FROM results r
         JOIN users u ON u.id = r.user_id
         $whereSQL
         ORDER BY r.date DESC
         LIMIT ? OFFSET ?";

$fetchTypes  = $types . 'ii';
$fetchParams = array_merge($params, [$perPage, $offset]);
$results     = fetchAll($sql, $fetchTypes, ...$fetchParams);

// Topic-specific aggregate (if filter active)
$topicStats = null;
if ($filterTopic) {
    $topicStats = fetchOne(
        "SELECT COUNT(*) AS cnt,
                IFNULL(AVG(score/total*100),0) AS avg_pct,
                MAX(score/total*100) AS max_pct,
                MIN(score/total*100) AS min_pct
         FROM results WHERE topic = ?",
        's', $filterTopic
    );
}

include '_header.php';
?>

<!-- Filter Bar -->
<div class="card mb-3">
    <div class="card-body" style="padding:16px;">
        <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
            <div class="form-group" style="margin:0; flex:1; min-width:180px;">
                <label style="font-size:.85rem;">Filter by Topic</label>
                <select name="topic" class="form-control">
                    <option value="">All Topics</option>
                    <?php foreach (TOPICS as $t): ?>
                        <option value="<?= e($t) ?>" <?= ($filterTopic === $t) ? 'selected' : '' ?>>
                            <?= e($t) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0; flex:1; min-width:180px;">
                <label style="font-size:.85rem;">Search Student</label>
                <input type="text" name="student" class="form-control"
                       placeholder="Student name..."
                       value="<?= e($filterStudent) ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            <?php if ($filterTopic || $filterStudent): ?>
                <a href="results.php" class="btn btn-light btn-sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Topic aggregate stats (when filtered) -->
<?php if ($topicStats && $filterTopic): ?>
<div class="stats-row mb-3">
    <div class="stat-card">
        <div class="stat-num"><?= (int)$topicStats['cnt'] ?></div>
        <div class="stat-lbl">Tests for <?= e($filterTopic) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-num"><?= number_format($topicStats['avg_pct'], 1) ?>%</div>
        <div class="stat-lbl">Average Score</div>
    </div>
    <div class="stat-card">
        <div class="stat-num"><?= number_format($topicStats['max_pct'], 1) ?>%</div>
        <div class="stat-lbl">Highest Score</div>
    </div>
    <div class="stat-card">
        <div class="stat-num"><?= number_format($topicStats['min_pct'], 1) ?>%</div>
        <div class="stat-lbl">Lowest Score</div>
    </div>
</div>
<?php endif; ?>

<!-- Results Table -->
<div class="card">
    <div class="card-header">
        Results
        <span style="font-weight:400; color:#64748b; font-size:.9rem;">
            (<?= $totalRows ?> records)
        </span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Topic</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; color:#94a3b8; padding:30px;">
                                No results found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($results as $i => $r): ?>
                            <?php $pct = $r['total'] > 0 ? round($r['score']/$r['total']*100) : 0; ?>
                            <tr>
                                <td><?= $offset + $i + 1 ?></td>
                                <td><?= e($r['student_name']) ?></td>
                                <td style="color:#64748b; font-size:.88rem;"><?= e($r['student_email']) ?></td>
                                <td><?= e($r['topic']) ?></td>
                                <td><?= (int)$r['score'] ?>/<?= (int)$r['total'] ?></td>
                                <td>
                                    <span class="badge <?= $pct >= 50 ? 'badge-success' : 'badge-danger' ?>">
                                        <?= $pct ?>%
                                    </span>
                                </td>
                                <td style="white-space:nowrap;">
                                    <?= date('d M Y, h:i A', strtotime($r['date'])) ?>
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
        <?php
            $url = 'results.php?page=' . $p
                 . ($filterTopic    ? '&topic='.urlencode($filterTopic)     : '')
                 . ($filterStudent  ? '&student='.urlencode($filterStudent) : '');
        ?>
        <?php if ($p === $page): ?>
            <span class="current"><?= $p ?></span>
        <?php else: ?>
            <a href="<?= $url ?>"><?= $p ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include '_footer.php'; ?>
