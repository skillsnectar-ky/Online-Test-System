<?php
/**
 * config.php
 * Aspirian.pk Online Test System
 * Database configuration and global constants
 */

// ── Database credentials ────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change to your MySQL username
define('DB_PASS', '');           // Change to your MySQL password
define('DB_NAME', 'aspirian_test_system');

// ── Site settings ────────────────────────────────────────────
define('SITE_NAME',  'Aspirian.pk');
define('SITE_URL',   'http://localhost/Online Test System'); // No trailing slash
define('TEST_TIME',  1800); // Seconds per test (30 minutes)
define('MCQS_PER_TEST', 10); // Number of MCQs shown per test

// ── Available test topics ────────────────────────────────────
define('TOPICS', [
    'MS Word',
    'MS Excel',
    'PowerPoint',
    'Internet',
    'Urdu InPage',
    'Introduction to Computer',
]);

// ── Create MySQLi connection ─────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;color:#c0392b;padding:30px;">
        <h2>Database Connection Failed</h2>
        <p>' . htmlspecialchars($conn->connect_error) . '</p>
        <p>Please check your database credentials in <strong>config.php</strong>.</p>
    </div>');
}

// Set charset to utf8mb4 (supports Urdu / Arabic characters)
$conn->set_charset('utf8mb4');

// ── Start session if not already started ─────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
