<?php
/**
 * Shopping Cart Page
 * 3 for 2 Special Offer Applied
 */
require_once __DIR__ . '/session.php';

$u = SITE_URL;

$cartItems = [];
$cartTotal = 0;
$cartCount = 0;
$discount = 0;
$discountMessage = '';

if (isLoggedIn()) {
    $conn = getDB();
    $userId = getCurrentUserId();
    
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.image, p.category, p.stock
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $allItemPrices = [];
    foreach ($cartItems as $item) {
        $cartTotal += $item['price'] * $item['qty'];
        $cartCount += $item['qty'];
        for ($i = 0; $i < $item['qty']; $i++) {
            $allItemPrices[] = $item['price'];
        }
    }
    
    if ($cartCount >= 3) {
        sort($allItemPrices);
        $freeItems = floor($cartCount / 3);
        for ($i = 0; $i < $freeItems; $i++) {
            $discount += $allItemPrices[$i];
        }
        if ($discount > 0) {
            $discountMessage = "🎉 3 for 2 Deal: You get {$freeItems} item(s) FREE!";
        }
    }
    
    $finalTotal = $cartTotal - $discount;
}

$success = getFlash('success');
$error = getFlash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Serenique | Shopping Cart</title>
    <style>
        .cart-page { padding: 2rem 0; min-height: 60vh; }
        .cart-header { font-family: 'Playfair Display', serif; margin-bottom: 2rem; }
        .cart-table { background: var(--card-bg, #fff); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .cart-table th { background: #f8f5f2; padding: 1rem; font-weight: 600; border: none; }
        .cart-table td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #eee; }
        .cart-item-img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .cart-item-name { font-weight: 600; color: var(--text-color); text-decoration: none; }
        .cart-item-name:hover { color: #d27b5a; }
        .qty-control { display: flex; align-items: center; gap: 5px; }
        .qty-btn { width: 30px; height: 30px; border: 1px solid #ddd; background: #fff; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .qty-btn:hover { background: #f0f0f0; }
        .qty-value { width: 50px; text-align: center; border: 1px solid #ddd; border-radius: 4px; padding: 5px; }
        .remove-btn { color: #dc3545; background: none; border: none; cursor: pointer; font-size: 1.2rem; }
        .cart-summary { background: var(--card-bg, #fff); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position: sticky; top: 100px; }
        .summary-row { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #eee; }
        .summary-total { font-size: 1.5rem; font-weight: 700; color: #d27b5a; }
        .btn-checkout { background: #d27b5a; color: white; border: none; padding: 15px 30px; border-radius: 8px; font-size: 1.1rem; font-weight: 600; width: 100%; cursor: pointer; transition: all 0.3s; }
        .btn-checkout:hover { background: #b8654a; color: white; }
        .empty-cart { text-align: center; padding: 4rem 2rem; }
        .promo-banner { background: linear-gradient(135deg, #d27b5a 0%, #e8a87c 100%); color: white; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 15px; }
        .discount-row { color: #28a745; font-weight: 600; }
        .deal-badge { background: #28a745; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
        .original-price { text-decoration: line-through; color: #999; font-size: 0.9rem; }
        .items-needed { background: #fff3cd; color: #856404; padding: 1rem; border-radius: 8px; margin-top: 1rem; text-align: center; }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <main class="container cart-page">
        <div class="promo-banner">
            <div style="font-size: 2.5rem;">🎁</div>
            <div>
                <h4 style="margin: 0;">3 FOR 2 SPECIAL OFFER!</h4>
                <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Add 3 or more items and get the cheapest one FREE!</p>
            </div>
        </div>

        <h1 class="cart-header">Shopping Cart</h1>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo e($success); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?php echo e($error); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <?php if (!isLoggedIn()): ?>
            <div class="empty-cart">
                <h3>Please log in to view your cart</h3>
                <p class="text-muted mb-4">Sign in to access your shopping cart.</p>
                <a href="<?php echo $u; ?>/login.php" class="btn btn-primary btn-lg">Login</a>
            </div>
        <?php elseif (empty($cartItems)): ?>
            <div class="empty-cart">
                <h3>Your cart is empty</h3>
                <p class="text-muted mb-4">Add 3 items to get the cheapest one FREE!</p>
                <a href="<?php echo $u; ?>/products.php" class="btn btn-primary btn-lg">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="cart-table">
                        <table class="table table-borderless mb-0">
                            <thead>
                                <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th></th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
                                    <?php 
                                    $imagePath = $item['image'] ?? 'assets/images/products/default.svg';
                                    if (strpos($imagePath, 'http') !== 0) {
                                        $imagePath = BASE_URL . '/' . ltrim($imagePath, '/');
                                    }
                                    $itemTotal = $item['price'] * $item['qty'];
                                    $stock = (int)($item['stock'] ?? 0);
                                    $stockWarning = $stock < $item['qty'];
                                    ?>
                                    <tr class="<?php echo $stockWarning ? 'table-warning' : ''; ?>">
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?php echo e($imagePath); ?>" alt="" class="cart-item-img">
                                                <div>
                                                    <a href="<?php echo $u; ?>/product.php?id=<?php echo $item['product_id']; ?>" class="cart-item-name"><?php echo e($item['name']); ?></a>
                                                    <div class="text-muted small"><?php echo e($item['category']); ?></div>
                                                    <?php if ($stockWarning): ?>
                                                        <div class="text-warning small mt-1">⚠️ Only <?php echo $stock; ?> in stock — reduce qty or remove before checkout</div>
                                                    <?php elseif ($stock <= 0): ?>
                                                        <div class="text-danger small mt-1">Out of stock — remove before checkout</div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>£<?php echo number_format($item['price'], 2); ?></td>
                                        <td>
                                            <form action="<?php echo $u; ?>/cart_update.php" method="POST" class="qty-control">
                                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" name="action" value="decrease" class="qty-btn">−</button>
                                                <input type="text" class="qty-value" value="<?php echo $item['qty']; ?>" readonly>
                                                <button type="submit" name="action" value="increase" class="qty-btn">+</button>
                                            </form>
                                        </td>
                                        <td class="fw-bold">£<?php echo number_format($itemTotal, 2); ?></td>
                                        <td>
                                            <form action="<?php echo $u; ?>/cart_update.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" name="action" value="remove" class="remove-btn">🗑️</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="<?php echo $u; ?>/products.php" class="btn btn-outline-secondary">← Continue Shopping</a>
                        <form action="<?php echo $u; ?>/cart_update.php" method="POST">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Clear cart?');">🗑️ Clear Cart</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h4 class="mb-4">Order Summary</h4>
                        <div class="summary-row"><span>Items (<?php echo $cartCount; ?>)</span><span>£<?php echo number_format($cartTotal, 2); ?></span></div>
                        <?php if ($discount > 0): ?>
                            <div class="summary-row discount-row"><span><span class="deal-badge">3 FOR 2</span> Discount</span><span>-£<?php echo number_format($discount, 2); ?></span></div>
                        <?php endif; ?>
                        <div class="summary-row"><span>Shipping</span><span class="text-success">FREE</span></div>
                        <div class="summary-row" style="border: none;">
                            <span class="fw-bold">Total</span>
                            <div>
                                <?php if ($discount > 0): ?><span class="original-price">£<?php echo number_format($cartTotal, 2); ?></span><br><?php endif; ?>
                                <span class="summary-total">£<?php echo number_format($finalTotal, 2); ?></span>
                            </div>
                        </div>
                        <?php if ($discount > 0): ?><div class="alert alert-success py-2 px-3 mb-3"><?php echo $discountMessage; ?></div><?php endif; ?>
                        <?php if ($cartCount > 0 && $cartCount < 3): ?>
                            <div class="items-needed">🎁 Add <?php echo (3 - $cartCount); ?> more item(s) for 3 for 2 deal!</div>
                        <?php endif; ?>
                        <a href="<?php echo $u; ?>/checkout.php" class="btn-checkout d-block text-center text-decoration-none mt-4">Proceed to Checkout →</a>
                        <?php if ($discount > 0): ?><div class="text-center mt-3"><span class="text-success fw-bold">You're saving £<?php echo number_format($discount, 2); ?>!</span></div><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
