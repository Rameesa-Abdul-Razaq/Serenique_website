<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/includes/config.php';

$db = getDB();
$res = $db->query("SELECT COUNT(*) AS c FROM products");
$row = $res->fetch_assoc();
echo "DB OK ✅ products=" . $row["c"];
