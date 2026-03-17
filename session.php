<?php
/**
 * Footer Include
 * Uses dynamic URLs for both LOCAL and ASTON SERVER
 */
$u = SITE_URL;
?>
<footer class="text-center py-4" style="background-color: var(--navbar-bg, #e8dcd5);">
    <div class="container">
        <div class="row mb-3">
            <div class="col-md-4 mb-3 mb-md-0">
                <h6>Quick Links</h6>
                <a href="<?php echo $u; ?>/products.php" class="text-muted d-block small">Shop All</a>
                <a href="<?php echo $u; ?>/aboutus.php" class="text-muted d-block small">About Us</a>
                <a href="<?php echo $u; ?>/contact.php" class="text-muted d-block small">Contact</a>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <h6>Customer Service</h6>
                <a href="<?php echo $u; ?>/orders.php" class="text-muted d-block small">Track Order</a>
                <a href="<?php echo $u; ?>/contact.php" class="text-muted d-block small">Returns</a>
                <a href="<?php echo $u; ?>/contact.php" class="text-muted d-block small">FAQ</a>
            </div>
            <div class="col-md-4">
                <h6>Special Offer</h6>
                <p class="text-muted small mb-0">🎁 Buy 3, Get 1 FREE!</p>
                <a href="<?php echo $u; ?>/products.php" class="small" style="color: #d27b5a;">Shop Now →</a>
            </div>
        </div>
        <hr class="my-3">
        <p class="m-0 text-muted small">&copy; 2025 Serenique. All rights reserved.</p>
    </div>
</footer>

<?php include __DIR__ . '/chatbot.php'; ?>
