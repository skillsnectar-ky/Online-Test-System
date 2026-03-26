<?php
/**
 * admin/login.php
 * Aspirian.pk Online Test System
 * Admin login page
 */

require_once __DIR__ . '/../functions.php';

// ── Redirect if already logged in as admin ────────────────
if (isLoggedIn() && $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// ── Handle form submission ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $admin = fetchOne(
            "SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1",
            's', $email
        );

        if ($admin && verifyPassword($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['name']    = $admin['name'];
            $_SESSION['email']   = $admin['email'];
            $_SESSION['role']    = $admin['role'];

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid admin credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="../assets/images/logo.png" alt="<?= SITE_NAME ?>" onerror="this.style.display='none'">
        </a>
        <div class="navbar-nav">
            <a href="../index.php">Student Login</a>
        </div>
    </div>
</nav>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="logo">
            <img src="../assets/images/logo.png" alt="<?= SITE_NAME ?>" onerror="this.style.display='none'">
            <p>Secure Admin Login</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?= renderFlash() ?>

        <form method="POST" action="login.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="form-group">
                <label for="email">Admin Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="admin@aspirian.pk"
                    value="<?= e($_POST['email'] ?? '') ?>"
                    required
                    autocomplete="username"
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

            <button type="submit" class="btn btn-primary btn-block mt-2">
                Sign In as Admin
            </button>
        </form>

        <p class="text-center mt-2" style="font-size:.85rem;color:#94a3b8;">
            Default: admin@aspirian.pk / admin123
        </p>
    </div>
</div>

<footer class="footer">
    &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.
</footer>

</body>
</html>
