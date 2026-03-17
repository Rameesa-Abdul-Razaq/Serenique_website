<?php
/**
 * One-time admin password fix script.
 * Run this once via browser, then DELETE this file immediately.
 * URL: http://cs2team57.cs2410-web01pvm.aston.ac.uk/fix_admin_password.php
 */
require_once __DIR__ . '/includes/config.php';

$newPassword = 'Admin123';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = 'admin@site.com' AND role = 'admin'");
$stmt->bind_param("s", $hash);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected > 0) {
    echo "<h2 style='color:green'>Admin password updated successfully.</h2>";
    echo "<p>Email: <strong>admin@site.com</strong><br>Password: <strong>Admin123</strong></p>";
    echo "<p style='color:red'><strong>IMPORTANT: Delete this file immediately after use!</strong></p>";
} else {
    echo "<h2 style='color:orange'>No rows updated.</h2>";
    echo "<p>The admin account may not exist or was already using the correct hash.</p>";
    
    // Show current users for debugging
    $result = $conn->query("SELECT id, username, email, role FROM users");
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['username']}</td><td>{$row['email']}</td><td>{$row['role']}</td></tr>";
    }
    echo "</table>";
}

