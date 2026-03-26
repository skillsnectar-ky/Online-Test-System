<?php
/**
 * admin/delete_mcq.php
 * Aspirian.pk Online Test System
 * Admin: Delete a single MCQ by ID (GET request with confirmation via JS)
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$id    = (int)($_GET['id']    ?? 0);
$topic = trim($_GET['topic'] ?? '');

if ($id < 1) {
    flash('error', 'Invalid MCQ ID.');
    header('Location: mcqs.php');
    exit;
}

// Verify the MCQ exists
$mcq = fetchOne('SELECT id FROM mcqs WHERE id = ? LIMIT 1', 'i', $id);

if (!$mcq) {
    flash('error', 'MCQ not found.');
    header('Location: mcqs.php');
    exit;
}

// Delete the MCQ
$result = execute('DELETE FROM mcqs WHERE id = ?', 'i', $id);

if ($result !== false) {
    flash('success', 'MCQ deleted successfully.');
} else {
    flash('error', 'Failed to delete MCQ.');
}

// Redirect back, preserving topic filter if provided
$redirect = 'mcqs.php' . ($topic ? '?topic=' . urlencode($topic) : '');
header('Location: ' . $redirect);
exit;
