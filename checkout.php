<?php
/**
 * Checkout Page with 3 for 2 Discount
 */
require_once __DIR__ . '/session.php';

requireLogin();

$u = SITE_URL;
$conn = getDB();
$userId = getCurrentUserId();

$stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($cartItems)) {
    setFlash('error', 'Your cart is empty.');
    redirect('cart.php');
}

$subtotal = 0;
$itemCount = 0;
$allItemPrices = [];

foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['qty'];
    $itemCount += $item['qty'];
    for ($i = 0; $i < $item['qty']; $i++) {
        $allItemPrices[] = $item['price'];
    }
}

$discount = 0;
$freeItems = 0;
if ($itemCount >= 3) {
    sort($allItemPrices);
    $freeItems = floor($itemCount / 3);
    for ($i = 0; $i < $freeItems; $i++) {
        $discount += $allItemPrices[$i];
    }
}

$shipping = 0;
$total = $subtotal - $discount + $shipping;

$error = '';
$orderPlaced = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postcode = trim($_POST['postcode'] ?? '');
    $cardName = trim($_POST['card_name'] ?? '');
    $cardNumber = trim($_POST['card_number'] ?? '');
    $expiry = trim($_POST['expiry'] ?? '');
    $cvv = trim($_POST['cvv'] ?? '');
    
    if (empty($fullName) || empty($email) || empty($address) || empty($city) || empty($postcode)) {
        $error = 'Please fill in all required shipping fields.';
    } elseif (empty($cardName) || empty($cardNumber) || empty($expiry) || empty($cvv)) {
        $error = 'Please fill in all payment fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Verify stock before placing order
        $stockOk = true;
        $stockMsg = '';
        foreach ($cartItems as $item) {
            $chk = $conn->prepare("SELECT stock, name FROM products WHERE id = ?");
            $chk->bind_param("i", $item['product_id']);
            $chk->execute();
            $p = $chk->get_result()->fetch_assoc();
            $chk->close();
            if ($p && (int)$p['stock'] < (int)$item['qty']) {
                $stockOk = false;
                $stockMsg = '"' . $p['name'] . '" only has ' . (int)$p['stock'] . ' left. Please update your cart.';
                break;
            }
        }
        if (!$stockOk) {
            $error = $stockMsg;
        } else {
        $orderNumber = 'ORD-' . strtoupper(substr(md5(time() . $userId), 0, 8));
        $shippingAddress = $fullName . "\n" . $address . "\n" . $city . ", " . $postcode;
        if ($phone) $shippingAddress .= "\nPhone: " . $phone;
        
        $orderStmt = $conn->prepare("INSERT INTO orders (user_id, order_number, subtotal, discount, total, status, shipping_address, email, created_at) VALUES (?, ?, ?, ?, ?, 'completed', ?, ?, NOW())");
        $orderStmt->bind_param("isdddss", $userId, $orderNumber, $subtotal, $discount, $total, $shippingAddress, $email);
        $orderStmt->execute();
        $orderId = $conn->insert_id;
        $orderStmt->close();
        
        $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, qty) VALUES (?, ?, ?, ?, ?)");
        foreach ($cartItems as $item) {
            $itemStmt->bind_param("iisdi", $orderId, $item['product_id'], $item['name'], $item['price'], $item['qty']);
            $itemStmt->execute();
            // Deduct stock
            $deductStmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $deductStmt->bind_param("ii", $item['qty'], $item['product_id']);
            $deductStmt->execute();
            $deductStmt->close();
        }
        $itemStmt->close();
        
        $_SESSION['products_to_review'] = [];
        foreach ($cartItems as $item) {
            $_SESSION['products_to_review'][] = ['id' => $item['product_id'], 'name' => $item['name'], 'image' => $item['image']];
        }
        
        $clearStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clearStmt->bind_param("i", $userId);
        $clearStmt->execute();
        $clearStmt->close();
        
        $orderPlaced = true;
        $savedAmount = $discount;
        $purchasedProducts = $_SESSION['products_to_review'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Serenique | Checkout</title>
    <style>
        .checkout-page { padding: 2rem 0; }
        .checkout-section { background: var(--card-bg, #fff); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .section-title { font-size: 1.2rem; font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px; }
        .section-number { background: #d27b5a; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; }
        .order-item { display: flex; gap: 15px; padding: 1rem 0; border-bottom: 1px solid #eee; }
        .order-item:last-child { border: none; }
        .order-item img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .order-summary { background: var(--card-bg, #fff); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position: sticky; top: 100px; }
        .summary-row { display: flex; justify-content: space-between; padding: 0.75rem 0; }
        .summary-total { font-size: 1.4rem; font-weight: 700; color: #d27b5a; border-top: 2px solid #eee; padding-top: 1rem; margin-top: 0.5rem; }
        .btn-place-order { background: #d27b5a; color: white; border: none; padding: 15px 30px; border-radius: 8px; font-size: 1.1rem; font-weight: 600; width: 100%; cursor: pointer; }
        .btn-place-order:hover { background: #b8654a; }
        .success-box { text-align: center; padding: 3rem; background: var(--card-bg, #fff); border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .order-number { background: #f8f5f2; padding: 1rem 2rem; border-radius: 8px; font-family: monospace; font-size: 1.2rem; display: inline-block; margin: 1rem 0; }
        .discount-row { color: #28a745; font-weight: 600; }
        .deal-badge { background: #28a745; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; }
        .savings-box { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 1rem; border-radius: 8px; text-align: center; margin: 1rem 0; }
        .promo-applied { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <main class="container checkout-page">
        <?php if ($orderPlaced): ?>
            <div class="success-box">
                <div style="font-size: 5rem;">✅</div>
                <h1 style="font-family: 'Playfair Display', serif;">Order Placed Successfully!</h1>
                <p class="text-muted">Thank you for your purchase.</p>
                <div class="order-number"><?php echo $orderNumber; ?></div>
                <?php if ($savedAmount > 0): ?>
                    <div class="savings-box">🎉 You saved <strong>£<?php echo number_format($savedAmount, 2); ?></strong> with our 3 for 2 offer!</div>
                <?php endif; ?>
                <?php if (!empty($purchasedProducts)): ?>
                    <div style="background: #f8f5f2; padding: 1.5rem; border-radius: 12px; margin: 2rem auto; max-width: 500px;">
                        <h4>⭐ Leave a Review</h4>
                        <?php foreach ($purchasedProducts as $p): ?>
                            <?php $pImage = $p['image'] ?? 'assets/images/products/default.svg'; if (strpos($pImage, 'http') !== 0) { $pImage = $u . '/' . ltrim($pImage, '/'); } ?>
                            <a href="<?php echo $u; ?>/add_review.php?product_id=<?php echo $p['id']; ?>&from=checkout" style="display: flex; align-items: center; gap: 15px; padding: 0.75rem; background: white; border-radius: 8px; text-decoration: none; color: inherit; margin-top: 0.5rem;">
                                <img src="<?php echo e($pImage); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">
                                <div style="flex-grow: 1; text-align: left;"><strong><?php echo e($p['name']); ?></strong><div class="text-muted small">Write a review →</div></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="mt-4 d-flex justify-content-center gap-3 flex-wrap">
                    <a href="<?php echo $u; ?>/orders.php" class="btn btn-outline-primary btn-lg">📦 View My Orders</a>
                    <a href="<?php echo $u; ?>/products.php" class="btn btn-primary btn-lg">Continue Shopping</a>
                </div>
            </div>
        <?php else: ?>
            <h1 style="font-family: 'Playfair Display', serif; margin-bottom: 2rem;">Checkout</h1>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
            <?php if ($discount > 0): ?><div class="promo-applied">🎁 <strong>3 for 2 Deal Applied!</strong> You're saving £<?php echo number_format($discount, 2); ?>!</div><?php endif; ?>

            <form method="POST">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="checkout-section">
                            <h3 class="section-title"><span class="section-number">1</span> Shipping Information</h3>
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" class="form-control" name="full_name" required value="<?php echo e($_POST['full_name'] ?? ''); ?>"></div>
                                <div class="col-md-6"><label class="form-label">Email *</label><input type="email" class="form-control" name="email" required value="<?php echo e($_POST['email'] ?? getCurrentUserEmail()); ?>"></div>
                                <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" class="form-control" name="phone" value="<?php echo e($_POST['phone'] ?? ''); ?>"></div>
                                <div class="col-12"><label class="form-label">Address *</label><input type="text" class="form-control" name="address" required value="<?php echo e($_POST['address'] ?? ''); ?>"></div>
                                <div class="col-md-6"><label class="form-label">City *</label><input type="text" class="form-control" name="city" required value="<?php echo e($_POST['city'] ?? ''); ?>"></div>
                                <div class="col-md-6"><label class="form-label">Postcode *</label><input type="text" class="form-control" name="postcode" required value="<?php echo e($_POST['postcode'] ?? ''); ?>"></div>
                            </div>
                        </div>
                        <div class="checkout-section">
                            <h3 class="section-title"><span class="section-number">2</span> Payment Information</h3>
                            <div class="row g-3">
                                <div class="col-12"><label class="form-label">Name on Card *</label><input type="text" class="form-control" name="card_name" required value="<?php echo e($_POST['card_name'] ?? ''); ?>"></div>
                                <div class="col-12"><label class="form-label">Card Number *</label><input type="text" class="form-control" name="card_number" required placeholder="1234 5678 9012 3456" maxlength="19" value="<?php echo e($_POST['card_number'] ?? ''); ?>"></div>
                                <div class="col-md-6"><label class="form-label">Expiry Date *</label><input type="text" class="form-control" name="expiry" required placeholder="MM/YY" maxlength="5" value="<?php echo e($_POST['expiry'] ?? ''); ?>"></div>
                                <div class="col-md-6"><label class="form-label">CVV *</label><input type="text" class="form-control" name="cvv" required placeholder="123" maxlength="4" value="<?php echo e($_POST['cvv'] ?? ''); ?>"></div>
                            </div>
                            <div class="mt-3"><small class="text-muted">🔒 Your payment information is secure and encrypted.</small></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="order-summary">
                            <h4 class="mb-4">Order Summary</h4>
                            <div class="mb-3" style="max-height: 300px; overflow-y: auto;">
                                <?php foreach ($cartItems as $item): ?>
                                    <?php $imagePath = $item['image'] ?? 'assets/images/products/default.svg'; if (strpos($imagePath, 'http') !== 0) { $imagePath = BASE_URL . '/' . ltrim($imagePath, '/'); } ?>
                                    <div class="order-item">
                                        <img src="<?php echo e($imagePath); ?>" alt="">
                                        <div class="flex-grow-1"><div class="fw-bold small"><?php echo e($item['name']); ?></div><div class="text-muted small">Qty: <?php echo $item['qty']; ?></div></div>
                                        <div class="fw-bold">£<?php echo number_format($item['price'] * $item['qty'], 2); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="summary-row"><span>Subtotal (<?php echo $itemCount; ?> items)</span><span>£<?php echo number_format($subtotal, 2); ?></span></div>
                            <?php if ($discount > 0): ?><div class="summary-row discount-row"><span><span class="deal-badge">3 FOR 2</span> Discount</span><span>-£<?php echo number_format($discount, 2); ?></span></div><?php endif; ?>
                            <div class="summary-row"><span>Shipping</span><span class="text-success">FREE</span></div>
                            <div class="summary-row summary-total"><span>Total</span><span>£<?php echo number_format($total, 2); ?></span></div>
                            <?php if ($discount > 0): ?><div class="text-center text-success fw-bold mb-3">🎉 You're saving £<?php echo number_format($discount, 2); ?>!</div><?php endif; ?>
                            <button type="submit" class="btn-place-order">Place Order - £<?php echo number_format($total, 2); ?></button>
                            <div class="text-center mt-3"><a href="<?php echo $u; ?>/cart.php" class="text-muted small">← Back to Cart</a></div>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
