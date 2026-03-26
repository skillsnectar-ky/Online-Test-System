<?php
/**
 * admin/students.php
 * Aspirian.pk Online Test System
 * Admin: View and manage registered students
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$pageTitle = '👥 Manage Students';

// ── Handle soft delete (remove student) ───────────────────
if (isset($_GET['delete']) && (int)$_GET['delete'] > 0) {
    $delId = (int)$_GET['delete'];
    // Safety: do not allow deleting admin accounts via this page
    execute("DELETE FROM users WHERE id = ? AND role = 'student'", 'i', $delId);
    flash('success', 'Student removed successfully.');
    header('Location: students.php');
    exit;
}

// ── Pagination ─────────────────────────────────────────────
$search     = trim($_GET['search'] ?? '');
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 20;
$offset     = ($page - 1) * $perPage;

if ($search) {
    $totalRows = fetchOne(
        "SELECT COUNT(*) AS c FROM users WHERE role='student' AND (name LIKE ? OR email LIKE ?)",
        'ss', "%$search%", "%$search%"
    )['c'] ?? 0;
    $students = fetchAll(
        "SELECT u.*,
                (SELECT COUNT(*) FROM results r WHERE r.user_id = u.id) AS tests_taken,
                (SELECT IFNULL(AVG(r.score/r.total*100),0) FROM results r WHERE r.user_id = u.id AND r.total > 0) AS avg_pct
         FROM users u
         WHERE u.role = 'student' AND (u.name LIKE ? OR u.email LIKE ?)
         ORDER BY u.created_at DESC LIMIT ? OFFSET ?",
        'ssii', "%$search%", "%$search%", $perPage, $offset
    );
} else {
    $totalRows = fetchOne("SELECT COUNT(*) AS c FROM users WHERE role='student'")['c'] ?? 0;
    $students  = fetchAll(
        "SELECT u.*,
                (SELECT COUNT(*) FROM results r WHERE r.user_id = u.id) AS tests_taken,
                (SELECT IFNULL(AVG(r.score/r.total*100),0) FROM results r WHERE r.user_id = u.id AND r.total > 0) AS avg_pct
         FROM users u
         WHERE u.role = 'student'
         ORDER BY u.created_at DESC LIMIT ? OFFSET ?",
        'ii', $perPage, $offset
    );
}

$totalPages = max(1, (int)ceil($totalRows / $perPage));

include '_header.php';
?>

<!-- Search bar -->
<div class="card mb-3">
    <div class="card-body" style="padding:14px;">
        <form method="GET" style="display:flex; gap:10px; align-items:center;">
            <input
                type="text"
                name="search"
                class="form-control"
                style="max-width:300px;"
                placeholder="Search by name or email..."
                value="<?= e($search) ?>"
            >
            <button class="btn btn-primary btn-sm">Search</button>
            <?php if ($search): ?>
                <a href="students.php" class="btn btn-light btn-sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Students Table -->
<div class="card">
    <div class="card-header">
        Students
        <span style="font-weight:400; color:#64748b; font-size:.9rem;">(<?= $totalRows ?> total)</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Tests Taken</th>
                        <th>Avg Score</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center;color:#94a3b8;padding:30px;">
                                No students found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $i => $s): ?>
                            <tr>
                                <td><?= $offset + $i + 1 ?></td>
                                <td><?= e($s['name']) ?></td>
                                <td style="color:#64748b; font-size:.88rem;"><?= e($s['email']) ?></td>
                                <td><?= (int)$s['tests_taken'] ?></td>
                                <td>
                                    <?php $avg = round($s['avg_pct']); ?>
                                    <span class="badge <?= $avg >= 50 ? 'badge-success' : 'badge-danger' ?>">
                                        <?= $avg ?>%
                                    </span>
                                </td>
                                <td style="white-space:nowrap;">
                                    <?= date('d M Y', strtotime($s['created_at'])) ?>
                                </td>
                                <td>
                                    <a
                                        href="results.php?student=<?= urlencode($s['name']) ?>"
                                        class="btn btn-light btn-sm"
                                    >Results</a>
                                    <a
                                        href="students.php?delete=<?= (int)$s['id'] ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Remove student <?= e(addslashes($s['name'])) ?>?');"
                                    >Delete</a>
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
        <?php $url = 'students.php?page=' . $p . ($search ? '&search='.urlencode($search) : ''); ?>
        <?php if ($p === $page): ?>
            <span class="current"><?= $p ?></span>
        <?php else: ?>
            <a href="<?= $url ?>"><?= $p ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include '_footer.php'; ?>
