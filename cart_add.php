<?php
/**
 * Add to Cart Handler
 */
require_once __DIR__ . '/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('products.php');
}

$productId = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));
$buyNow = isset($_POST['buy_now']);
$userId = getCurrentUserId();

if (!$productId) {
    setFlash('error', 'Invalid product.');
    redirect('products.php');
}

$conn = getDB();

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    setFlash('error', 'Product not found.');
    redirect('products.php');
}

$stock = (int)($product['stock'] ?? 0);
if ($stock <= 0) {
    setFlash('error', '"' . $product['name'] . '" is out of stock.');
    redirect('product.php?id=' . $productId);
}

$qty = min($qty, $stock);

$cartStmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
$cartStmt->bind_param("ii", $userId, $productId);
$cartStmt->execute();
$existingItem = $cartStmt->get_result()->fetch_assoc();
$cartStmt->close();

if ($existingItem) {
    $newQty = min($existingItem['qty'] + $qty, $stock);
    $updateStmt = $conn->prepare("UPDATE cart SET qty = ? WHERE id = ?");
    $updateStmt->bind_param("ii", $newQty, $existingItem['id']);
    $updateStmt->execute();
    $updateStmt->close();
} else {
    $insertStmt = $conn->prepare("INSERT INTO cart (user_id, product_id, qty, created_at) VALUES (?, ?, ?, NOW())");
    $insertStmt->bind_param("iii", $userId, $productId, $qty);
    $insertStmt->execute();
    $insertStmt->close();
}

setFlash('success', '"' . $product['name'] . '" added to cart!');

if ($buyNow) {
    redirect('checkout.php');
} else {
    redirect('cart.php');
}
?>
