<?php
/**
 * Diagnostic script — DELETE after use.
 * Visit: http://cs2team57.cs2410-web01pvm.aston.ac.uk/diag.php
 */
$DB_HOST = "localhost";
$DB_USER = "cs2team57";
$DB_PASS = "EruuMu42kZHszDadyUWhXXNkc";
$DB_NAME = "cs2team57_db";

echo "<style>body{font-family:monospace;padding:20px;} .ok{color:green;} .fail{color:red;} .warn{color:orange;} table{border-collapse:collapse;} td,th{border:1px solid #ccc;padding:6px 12px;}</style>";
echo "<h2>Serenique Diagnostic</h2>";

// 1. DB Connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    echo "<p class='fail'>❌ DB CONNECTION FAILED: " . htmlspecialchars($conn->connect_error) . "</p>";
    exit;
}
echo "<p class='ok'>✅ DB connected to <strong>{$DB_NAME}</strong> as <strong>{$DB_USER}</strong></p>";

// 2. Tables
$tables = [];
$r = $conn->query("SHOW TABLES");
while ($row = $r->fetch_row()) $tables[] = $row[0];
echo "<p class='ok'>✅ Tables found: " . implode(', ', $tables) . "</p>";

// 3. Users table
$result = $conn->query("SELECT id, username, email, role, LEFT(password_hash,30) AS hash_preview FROM users");
if (!$result) {
    echo "<p class='fail'>❌ Could not query users table: " . htmlspecialchars($conn->error) . "</p>";
    exit;
}
$users = $result->fetch_all(MYSQLI_ASSOC);
echo "<h3>Users in DB (" . count($users) . " total)</h3>";
echo "<table><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Hash (first 30 chars)</th><th>Password Test</th></tr>";
foreach ($users as $u) {
    // Fetch full hash for verify
    $s = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
    $s->bind_param("i", $u['id']);
    $s->execute();
    $fullHash = $s->get_result()->fetch_assoc()['password_hash'];
    $s->close();

    $testPass = ($u['role'] === 'admin') ? 'Admin123' : '';
    $verifyResult = $testPass ? (password_verify($testPass, $fullHash) ? "<span class='ok'>✅ 'Admin123' works</span>" : "<span class='fail'>❌ 'Admin123' WRONG</span>") : '—';

    echo "<tr><td>{$u['id']}</td><td>{$u['username']}</td><td>{$u['email']}</td><td>{$u['role']}</td><td>{$u['hash_preview']}...</td><td>{$verifyResult}</td></tr>";
}
echo "</table>";

// 4. Fix admin password right now
echo "<hr><h3>Auto-fix Admin Password</h3>";
$newHash = password_hash('Admin123', PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = 'admin@site.com' AND role = 'admin'");
$stmt->bind_param("s", $newHash);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected > 0) {
    echo "<p class='ok'>✅ Admin password has been set to <strong>Admin123</strong></p>";
} else {
    echo "<p class='warn'>⚠️ No admin row updated (either already correct or admin@site.com doesn't exist with role=admin)</p>";
}

// 5. Session test
session_start();
$_SESSION['diag_test'] = 'works';
echo "<p class='ok'>✅ Sessions working (session_id: " . session_id() . ")</p>";

// 6. PHP version
echo "<p class='ok'>✅ PHP version: " . phpversion() . "</p>";

echo "<hr><p style='color:red;font-weight:bold;'>DELETE THIS FILE (diag.php) FROM THE SERVER IMMEDIATELY AFTER READING THIS.</p>";
echo "<p><a href='/login.php'>→ Go to login page</a></p>";
$conn->close();
