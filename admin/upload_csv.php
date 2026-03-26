<?php
/**
 * admin/upload_csv.php
 * Aspirian.pk Online Test System
 * Admin: Bulk-upload MCQs from a CSV file
 *
 * Expected CSV format (first row = header):
 * topic, question, option_a, option_b, option_c, option_d, correct_option
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$pageTitle = '📤 Upload MCQs via CSV';
$report    = null;

// ── Handle CSV upload ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        flash('error', 'Please select a valid CSV file to upload.');
        header('Location: upload_csv.php');
        exit;
    }

    $file     = $_FILES['csv_file'];
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($ext !== 'csv') {
        flash('error', 'Only .csv files are accepted.');
        header('Location: upload_csv.php');
        exit;
    }

    // Use the temp path directly (no need to move for just reading)
    $report = importMCQsFromCSV($file['tmp_name']);
}

include '_header.php';
?>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:22px;">

    <!-- Upload Form -->
    <div class="card">
        <div class="card-header">📤 Upload CSV File</div>
        <div class="card-body">

            <?php if ($report): ?>
                <div class="alert <?= empty($report['errors']) ? 'alert-success' : 'alert-warning' ?>">
                    <?= (int)$report['inserted'] ?> MCQ(s) imported successfully.
                    <?php if (!empty($report['errors'])): ?>
                        <br><strong><?= count($report['errors']) ?> error(s) occurred:</strong>
                        <ul style="margin:8px 0 0 16px;">
                            <?php foreach ($report['errors'] as $err): ?>
                                <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="upload_csv.php" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <div class="form-group">
                    <label for="csv_file">Select CSV File</label>
                    <input
                        type="file"
                        id="csv_file"
                        name="csv_file"
                        class="form-control"
                        accept=".csv"
                        required
                    >
                    <p style="color:#64748b; font-size:.82rem; margin-top:6px;">
                        Max size: <?= ini_get('upload_max_filesize') ?>. Only .csv files.
                    </p>
                </div>

                <button type="submit" class="btn btn-primary">
                    📤 Upload & Import
                </button>
                <a href="mcqs.php" class="btn btn-light" style="margin-left:10px;">
                    View MCQs
                </a>
            </form>
        </div>
    </div>

    <!-- CSV Format Guide -->
    <div class="card">
        <div class="card-header">📋 CSV Format Guide</div>
        <div class="card-body">
            <p style="margin-bottom:12px; color:#374151;">
                Your CSV file must have the following columns
                <strong>in this exact order</strong> (first row = header, which will be skipped):
            </p>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Column</th>
                            <th>Description</th>
                            <th>Example</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>topic</td><td>One of the defined topics</td><td>MS Word</td></tr>
                        <tr><td>question</td><td>The full question text</td><td>What is Ctrl+B?</td></tr>
                        <tr><td>option_a</td><td>Option A text</td><td>Italic</td></tr>
                        <tr><td>option_b</td><td>Option B text</td><td>Bold</td></tr>
                        <tr><td>option_c</td><td>Option C text</td><td>Underline</td></tr>
                        <tr><td>option_d</td><td>Option D text</td><td>Cut</td></tr>
                        <tr><td>correct_option</td><td>a / b / c / d</td><td>b</td></tr>
                    </tbody>
                </table>
            </div>

            <p style="margin-top:14px; font-size:.88rem; color:#64748b;">
                <strong>Available Topics:</strong>
                <?= implode(', ', array_map(fn($t) => e($t), TOPICS)) ?>
            </p>

            <!-- Sample CSV preview -->
            <div style="margin-top:16px;">
                <strong style="font-size:.88rem;">Sample CSV Content:</strong>
                <pre style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px;
                            padding:12px; margin-top:8px; font-size:.8rem; overflow-x:auto;
                            white-space:pre-wrap; color:#374151;">topic,question,option_a,option_b,option_c,option_d,correct_option
MS Word,Shortcut to bold text?,Ctrl+I,Ctrl+B,Ctrl+U,Ctrl+S,b
MS Excel,Symbol to start a formula?,#,@,=,$,c
Internet,WWW stands for?,World Wide Web,Wide Web World,Web World Wide,Wide World Web,a</pre>
            </div>

            <a
                href="data:text/csv;charset=utf-8,topic%2Cquestion%2Coption_a%2Coption_b%2Coption_c%2Coption_d%2Ccorrect_option%0AMS+Word%2CSample+question%3F%2COption+A%2COption+B%2COption+C%2COption+D%2Ca"
                download="sample_mcqs.csv"
                class="btn btn-light btn-sm mt-2"
            >
                ⬇️ Download Sample CSV
            </a>
        </div>
    </div>

</div>

<?php include '_footer.php'; ?>
