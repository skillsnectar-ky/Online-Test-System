<?php
/**
 * config.php
 * Aspirian.pk Online Test System
 * Database configuration and global constants
 */

// ── Database credentials ────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'u440219551_onlinetest');  // Hostinger DB username
define('DB_PASS', 'ArfatGift@2018');          // Hostinger DB password
define('DB_NAME', 'u440219551_online_test');  // Hostinger DB name

// ── Site settings ────────────────────────────────────────────
define('SITE_NAME',  'Aspirian.pk');
define('SITE_URL',   'https://aspirian.pk/test'); // No trailing slash
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
