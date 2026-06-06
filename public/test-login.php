<?php
/**
 * Test Connection & Login
 * Jalankan di browser: http://localhost/SofwanLand-project/public/test-login.php
 */

require_once __DIR__ . '/../config/database.php';

echo "<h2>🧪 Test Login & Database</h2>";

try {
    $db = getDB();
    echo "✅ Database connected<br>";

    // Check users table
    $result = $db->query("SELECT COUNT(*) as count FROM users;");
    $row = $result->fetch();
    echo "✅ Users table: " . $row['count'] . " user(s)<br><br>";

    // Test setiap user
    $testUsers = [
        ['email' => 'admin@sofwan.com', 'password' => 'admin123', 'role' => 'admin'],
        ['email' => 'manager@sofwan.com', 'password' => 'manager123', 'role' => 'manager'],
        ['email' => 'user@sofwan.com', 'password' => 'user123', 'role' => 'user'],
    ];

    foreach ($testUsers as $test) {
        $stmt = $db->prepare("SELECT id, nama, email, password, role FROM users WHERE email = ? AND status = 'aktif'");
        $stmt->execute([$test['email']]);
        $user = $stmt->fetch();

        if (!$user) {
            echo "❌ User tidak ditemukan: {$test['email']}<br>";
        } elseif ($user['password'] === $test['password']) {
            echo "✅ Login OK: {$test['email']} (Role: {$test['role']})<br>";
        } else {
            echo "❌ Password salah: {$test['email']}<br>";
            echo "   Database password: " . $user['password'] . "<br>";
            echo "   Expected: " . $test['password'] . "<br>";
        }
    }

    echo "<br><h3>Langkah selanjutnya:</h3>";
    echo "<ol>";
    echo "<li>Jika semua ✅, coba login di <a href='/?page=login'>Login Page</a></li>";
    echo "<li>Jika ada ❌, jalankan <a href='/SofwanLand-project/public/setup-db.php'>Setup Database</a> terlebih dahulu</li>";
    echo "</ol>";

} catch (Exception $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "<br>";
}
?>
