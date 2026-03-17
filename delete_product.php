<?php
/**
 * Delete Product
 * Only product owner or admin can delete
 */
require_once __DIR__ . '/session.php';

requireLogin();

$productId = (int)($_GET['id'] ?? 0);

if (!$productId) {
    header('Location: products.php');
    exit;
}

$conn = getDB();

// Get product to check ownership
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    setFlash('error', 'Product not found.');
    header('Location: products.php');
    exit;
}

// Check permission: must be owner OR admin
if ($product['posted_by'] != getCurrentUserId() && !isAdmin()) {
    setFlash('error', 'You do not have permission to delete this product.');
    header('Location: products.php');
    exit;
}

// Delete the product
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);

if ($stmt->execute()) {
    setFlash('success', 'Product "' . $product['name'] . '" deleted successfully.');
} else {
    setFlash('error', 'Failed to delete product.');
}
$stmt->close();

header('Location: products.php');
exit;
?>
