<?php
/**
 * Order History Page
 */
require_once __DIR__ . '/session.php';

requireLogin();

$u = SITE_URL;
$conn = getDB();
$userId = getCurrentUserId();

$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($orders as &$order) {
    $itemsStmt = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $itemsStmt->bind_param("i", $order['id']);
    $itemsStmt->execute();
    $order['items'] = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $itemsStmt->close();
}
unset($order);

$success = getFlash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Serenique | My Orders</title>
    <style>
        .orders-page { padding: 2rem 0; min-height: 70vh; }
        .order-card { background: var(--card-bg, #fff); border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 1.5rem; overflow: hidden; }
        .order-card-header { background: #f8f5f2; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .order-number { font-family: monospace; font-weight: 600; font-size: 1.1rem; }
        .order-status { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; background: #d4edda; color: #155724; }
        .order-card-body { padding: 1.5rem; }
        .order-item { display: flex; align-items: center; gap: 15px; padding: 1rem 0; border-bottom: 1px solid #eee; }
        .order-item:last-child { border: none; }
        .order-item img { width: 70px; height: 70px; object-fit: cover; border-radius: 8px; }
        .order-summary { display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 2px solid #eee; margin-top: 1rem; }
        .order-total span { color: #d27b5a; font-weight: 700; font-size: 1.2rem; }
        .empty-orders { text-align: center; padding: 4rem 2rem; }
        .btn-review { background: #ffc107; color: #333; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; text-decoration: none; }
        .discount-badge { background: #28a745; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <main class="container orders-page">
        <h1 style="font-family: 'Playfair Display', serif; margin-bottom: 2rem;">My Orders</h1>

        <?php if ($success): ?><div class="alert alert-success"><?php echo e($success); ?></div><?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <h3>No orders yet</h3>
                <p class="text-muted mb-4">When you place an order, it will appear here.</p>
                <a href="<?php echo $u; ?>/products.php" class="btn btn-primary btn-lg">Start Shopping</a>
            </div>
        <?php else: ?>
            <p class="text-muted mb-4"><?php echo count($orders); ?> order(s) found</p>

            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-card-header">
                        <div>
                            <span class="order-number"><?php echo e($order['order_number']); ?></span>
                            <span class="text-muted"> • <?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <?php if ($order['discount'] > 0): ?><span class="discount-badge">3 FOR 2 APPLIED</span><?php endif; ?>
                            <span class="order-status"><?php echo ucfirst($order['status']); ?></span>
                        </div>
                    </div>
                    <div class="order-card-body">
                        <?php foreach ($order['items'] as $item): ?>
                            <?php $imagePath = $item['image'] ?? 'assets/images/products/default.svg'; if (strpos($imagePath, 'http') !== 0) { $imagePath = BASE_URL . '/' . ltrim($imagePath, '/'); } ?>
                            <div class="order-item">
                                <img src="<?php echo e($imagePath); ?>" alt="">
                                <div style="flex-grow: 1;">
                                    <?php if ($item['product_id']): ?>
                                        <a href="<?php echo $u; ?>/product.php?id=<?php echo $item['product_id']; ?>" style="font-weight: 600; color: var(--text-color); text-decoration: none;"><?php echo e($item['name'] ?? $item['product_name']); ?></a>
                                    <?php else: ?>
                                        <span style="font-weight: 600;"><?php echo e($item['product_name']); ?></span>
                                    <?php endif; ?>
                                    <div class="text-muted small">Qty: <?php echo $item['qty']; ?> × £<?php echo number_format($item['price'], 2); ?></div>
                                </div>
                                <div class="text-end">
                                    <div style="font-weight: 600; color: #d27b5a;">£<?php echo number_format($item['price'] * $item['qty'], 2); ?></div>
                                    <?php if ($item['product_id']): ?>
                                        <a href="<?php echo $u; ?>/add_review.php?product_id=<?php echo $item['product_id']; ?>" class="btn-review mt-2 d-inline-block">⭐ Review</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="order-summary">
                            <div>
                                <?php if ($order['discount'] > 0): ?><small class="text-success">Saved £<?php echo number_format($order['discount'], 2); ?></small><br><?php endif; ?>
                                <small class="text-muted"><?php echo array_sum(array_column($order['items'], 'qty')); ?> item(s)</small>
                            </div>
                            <div class="order-total">Total: <span>£<?php echo number_format($order['total'], 2); ?></span></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
