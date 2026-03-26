<?php
/**
 * register.php
 * Aspirian.pk Online Test System
 * Student registration page
 */

require_once __DIR__ . '/functions.php';

// ── Redirect if already logged in ────────────────────────
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error   = '';
$success = '';

// ── Handle registration form ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $existing = fetchOne('SELECT id FROM users WHERE email = ? LIMIT 1', 's', $email);

        if ($existing) {
            $error = 'This email address is already registered.';
        } else {
            // Insert new student
            $hashed = hashPassword($password);
            $result = execute(
                "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')",
                'sss', $name, $email, $hashed
            );

            if ($result) {
                flash('success', 'Registration successful! Please log in.');
                header('Location: index.php');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration — <?= SITE_NAME ?></title>
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
            <a href="index.php">Login</a>
            <a href="admin/login.php">Admin Login</a>
        </div>
    </div>
</nav>

<!-- Registration Form -->
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="logo">
            <img src="assets/images/logo.png" alt="<?= SITE_NAME ?>" onerror="this.style.display='none'">
            <h1><?= SITE_NAME ?></h1>
            <p>Create your student account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?= renderFlash() ?>

        <form method="POST" action="register.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="form-group">
                <label for="name">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    placeholder="Your full name"
                    value="<?= e($_POST['name'] ?? '') ?>"
                    required
                >
            </div>

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
                >
            </div>

            <div class="form-group">
                <label for="password">Password <small style="color:#94a3b8;">(min. 6 characters)</small></label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="••••••••"
                    required
                >
            </div>

            <div class="form-group">
                <label for="confirm">Confirm Password</label>
                <input
                    type="password"
                    id="confirm"
                    name="confirm"
                    class="form-control"
                    placeholder="••••••••"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary btn-block mt-2">Create Account</button>
        </form>

        <p class="text-center mt-2" style="font-size:.9rem;color:#64748b;">
            Already have an account?
            <a href="index.php">Sign in here</a>
        </p>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.
</footer>

</body>
</html>
