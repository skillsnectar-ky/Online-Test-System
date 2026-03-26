<?php
// Aspirian.pk — Server Diagnostic (DELETE AFTER USE)
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PHP Version: " . phpversion() . "</h2>";

// Test DB connection
$conn = @mysqli_connect('localhost', 'u440219551_onlinetest', 'ArfatGift@2018', 'u440219551_online_test');
if ($conn) {
    echo "<p style='color:green'>✅ Database connected OK</p>";
    mysqli_close($conn);
} else {
    echo "<p style='color:red'>❌ DB Error: " . mysqli_connect_error() . "</p>";
}

// Test includes
echo "<p>Testing includes...</p>";
try {
    require_once __DIR__ . '/functions.php';
    echo "<p style='color:green'>✅ functions.php loaded OK</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>❌ " . $e->getMessage() . "</p>";
}
