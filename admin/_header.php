<?php
/**
 * admin/_header.php
 * Aspirian.pk Online Test System
 * Reusable admin layout header — included at top of every admin page
 *
 * Expects $pageTitle to be set before including this file.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Admin') ?> — <?= SITE_NAME ?> Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- Admin Top Navbar -->
<nav class="navbar">
    <div class="container" style="max-width:100%;">
        <a class="navbar-brand" href="dashboard.php">
            🛡️ <?= SITE_NAME ?> Admin
        </a>
        <div class="navbar-nav">
            <span style="color:rgba(255,255,255,.7); font-size:.9rem;">
                👤 <?= e($_SESSION['name'] ?? 'Admin') ?>
            </span>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<!-- Admin Layout Wrapper -->
<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">Admin Panel</div>
        <ul class="sidebar-nav">
            <li>
                <a href="dashboard.php" <?= (basename($_SERVER['PHP_SELF']) === 'dashboard.php') ? 'class="active"' : '' ?>>
                    <span class="nav-icon">🏠</span> Dashboard
                </a>
            </li>
            <li>
                <a href="add_mcq.php" <?= (basename($_SERVER['PHP_SELF']) === 'add_mcq.php') ? 'class="active"' : '' ?>>
                    <span class="nav-icon">➕</span> Add MCQ
                </a>
            </li>
            <li>
                <a href="mcqs.php" <?= (basename($_SERVER['PHP_SELF']) === 'mcqs.php') ? 'class="active"' : '' ?>>
                    <span class="nav-icon">📋</span> Manage MCQs
                </a>
            </li>
            <li>
                <a href="upload_csv.php" <?= (basename($_SERVER['PHP_SELF']) === 'upload_csv.php') ? 'class="active"' : '' ?>>
                    <span class="nav-icon">📤</span> Upload CSV
                </a>
            </li>
            <li>
                <a href="results.php" <?= (basename($_SERVER['PHP_SELF']) === 'results.php') ? 'class="active"' : '' ?>>
                    <span class="nav-icon">📊</span> Student Results
                </a>
            </li>
            <li>
                <a href="students.php" <?= (basename($_SERVER['PHP_SELF']) === 'students.php') ? 'class="active"' : '' ?>>
                    <span class="nav-icon">👥</span> Students
                </a>
            </li>
            <li>
                <a href="../index.php" target="_blank">
                    <span class="nav-icon">🌐</span> View Site
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <span class="nav-icon">🚪</span> Logout
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <main class="admin-content">
        <h2 style="font-size:1.35rem; font-weight:700; margin-bottom:20px; color:#1e293b;">
            <?= e($pageTitle ?? '') ?>
        </h2>
        <?= renderFlash() ?>
