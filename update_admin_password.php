<?php
require_once 'config.php';

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@aspirian.pk'");
$result = $stmt->execute([$hash]);

if ($result && $stmt->rowCount() > 0) {
    echo "<h2 style='color:green'>✅ Admin password updated successfully!</h2>";
    echo "<p>Email: admin@aspirian.pk</p>";
    echo "<p>Password: admin123</p>";
    echo "<p><strong>⚠️ Delete this file from server now!</strong></p>";
} else {
    echo "<h2 style='color:red'>❌ Failed or admin user not found.</h2>";
    // Try insert if not exists
    $stmt2 = $pdo->prepare("SELECT id FROM users WHERE email = 'admin@aspirian.pk'");
    $stmt2->execute();
    if (!$stmt2->fetch()) {
        $stmt3 = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@aspirian.pk', ?, 'admin')");
        $stmt3->execute([$hash]);
        echo "<p style='color:green'>Admin user created! Password: admin123</p>";
    }
}
?>
