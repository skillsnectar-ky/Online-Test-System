<?php
/**
 * index.php
 * Aspirian.pk Online Test System
 * Student login page — entry point of the application
 */

require_once __DIR__ . '/functions.php';

// ── Redirect if already logged in ────────────────────────
if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

$error = '';

// ── Handle login form submission ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // Fetch user by email (students only on this page)
        $user = fetchOne(
            "SELECT * FROM users WHERE email = ? AND role = 'student' LIMIT 1",
            's', $email
        );

        if ($user && verifyPassword($password, $user['password'])) {
            // ✓ Valid — set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="assets/images/logo.png" alt="<?= SITE_NAME ?>" onerror="this.style.display='none'">
            <?= SITE_NAME ?>
        </a>
        <div class="navbar-nav">
            <a href="register.php">Register</a>
            <a href="admin/login.php">Admin Login</a>
        </div>
    </div>
</nav>

<!-- Login Form -->
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="logo">
            <img src="assets/images/logo.png" alt="<?= SITE_NAME ?>" onerror="this.style.display='none'">
            <p>Online Test System — Student Login</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?= renderFlash() ?>

        <form method="POST" action="index.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="you@example.com"
                    value="<?= e($_POST['email'] ?? '') ?>"
                    required
                    autocomplete="email"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn btn-primary btn-block mt-2">Sign In</button>
        </form>

        <p class="text-center mt-2" style="font-size:.9rem;color:#64748b;">
            Don't have an account?
            <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.
</footer>

</body>
</html>
