<?php
/**
 * Add Review Page
 * Allows logged-in users to add product reviews
 */
require_once __DIR__ . '/session.php';

requireLogin();

$productId = (int)($_GET['product_id'] ?? $_POST['product_id'] ?? 0);
$userId = getCurrentUserId();
$fromCheckout = isset($_GET['from']) && $_GET['from'] === 'checkout';

if (!$productId) {
    redirect('products.php');
}

$conn = getDB();

// Get product info
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    redirect('products.php');
}

// Check if user already reviewed this product
$reviewCheck = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
$reviewCheck->bind_param("ii", $userId, $productId);
$reviewCheck->execute();
$existingReview = $reviewCheck->get_result()->fetch_assoc();
$reviewCheck->close();

if ($existingReview) {
    setFlash('error', 'You have already reviewed this product.');
    redirect('product.php?id=' . $productId);
}

$error = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)($_POST['rating'] ?? 0);
    $review = trim($_POST['review'] ?? '');
    
    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating between 1 and 5 stars.';
    } elseif (empty($review)) {
        $error = 'Please write your review.';
    } else {
        $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, review, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiis", $productId, $userId, $rating, $review);
        
        if ($stmt->execute()) {
            $success = true;
            
            // Check if there are more products to review from checkout
            if (isset($_SESSION['products_to_review']) && !empty($_SESSION['products_to_review'])) {
                // Remove this product from the list
                $_SESSION['products_to_review'] = array_filter($_SESSION['products_to_review'], function($p) use ($productId) {
                    return $p['id'] != $productId;
                });
            }
        } else {
            $error = 'Failed to submit review. Please try again.';
        }
        $stmt->close();
    }
}

// Get remaining products to review
$remainingProducts = $_SESSION['products_to_review'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Serenique | Review Product</title>
    <style>
        .review-page { padding: 2rem 0; min-height: 70vh; }
        .review-card { max-width: 600px; margin: 0 auto; background: var(--card-bg, #fff); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); overflow: hidden; }
        .review-header { background: linear-gradient(135deg, #d27b5a 0%, #e8a87c 100%); color: white; padding: 2rem; text-align: center; }
        .review-header img { width: 100px; height: 100px; object-fit: cover; border-radius: 12px; border: 3px solid white; margin-bottom: 1rem; }
        .review-header h2 { font-family: 'Playfair Display', serif; margin: 0; font-size: 1.5rem; }
        .review-body { padding: 2rem; }
        .star-rating { display: flex; justify-content: center; gap: 8px; margin: 1.5rem 0; }
        .star-rating input { display: none; }
        .star-rating label { font-size: 2.5rem; color: #ddd; cursor: pointer; transition: all 0.2s; }
        .star-rating label:hover { transform: scale(1.2); }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label { color: #ffc107; }
        .star-rating { flex-direction: row-reverse; }
        .btn-submit-review { background: #d27b5a; color: white; border: none; padding: 15px 40px; border-radius: 8px; font-size: 1.1rem; font-weight: 600; width: 100%; cursor: pointer; transition: all 0.3s; }
        .btn-submit-review:hover { background: #b8654a; transform: translateY(-2px); }
        .success-message { text-align: center; padding: 3rem 2rem; }
        .success-icon { font-size: 4rem; margin-bottom: 1rem; }
        .more-products { background: #f8f5f2; padding: 1.5rem; border-radius: 12px; margin-top: 1.5rem; }
        .more-products h5 { margin-bottom: 1rem; }
        .product-to-review { display: flex; align-items: center; gap: 15px; padding: 0.75rem; background: white; border-radius: 8px; margin-bottom: 0.5rem; text-decoration: none; color: inherit; transition: all 0.2s; }
        .product-to-review:hover { transform: translateX(5px); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .product-to-review img { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; }
        .rating-text { text-align: center; color: #666; font-size: 0.9rem; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <main class="container review-page">
        <?php if ($success): ?>
            <div class="review-card">
                <div class="success-message">
                    <div class="success-icon">⭐</div>
                    <h2 style="font-family: 'Playfair Display', serif;">Thank You!</h2>
                    <p class="text-muted">Your review for <strong><?php echo e($product['name']); ?></strong> has been submitted.</p>
                    
                    <?php if (!empty($remainingProducts)): ?>
                        <div class="more-products">
                            <h5>📦 More products to review:</h5>
                            <?php foreach ($remainingProducts as $p): ?>
                                <a href="<?php echo url('add_review.php?product_id=' . $p['id'] . '&from=checkout'); ?>" class="product-to-review">
                                    <img src="<?php echo e(BASE_URL . '/' . ltrim($p['image'] ?? 'assets/images/products/default.svg', '/')); ?>" alt="">
                                    <div>
                                        <strong><?php echo e($p['name']); ?></strong>
                                        <div class="text-muted small">Leave a review →</div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="mt-4">
                            <a href="<?php echo url('products.php'); ?>" class="btn btn-primary">Continue Shopping</a>
                            <a href="<?php echo url('product.php?id=' . $productId); ?>" class="btn btn-outline-secondary">View Product</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="review-card">
                <div class="review-header">
                    <?php 
                    $imagePath = $product['image'] ?? 'assets/images/products/default.svg';
                    if (strpos($imagePath, 'http') !== 0) {
                        $imagePath = BASE_URL . '/' . ltrim($imagePath, '/');
                    }
                    ?>
                    <img src="<?php echo e($imagePath); ?>" alt="<?php echo e($product['name']); ?>">
                    <h2><?php echo e($product['name']); ?></h2>
                    <p style="opacity: 0.9; margin: 0.5rem 0 0;">Share your experience with this product</p>
                </div>
                
                <div class="review-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo e($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($fromCheckout): ?>
                        <div class="alert alert-success">
                            🎉 Thank you for your purchase! Please share your thoughts about this product.
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                        
                        <div class="mb-4">
                            <label class="form-label text-center d-block fw-bold">How would you rate this product?</label>
                            <div class="star-rating">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo (($_POST['rating'] ?? 5) == $i) ? 'checked' : ''; ?>>
                                    <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> stars">★</label>
                                <?php endfor; ?>
                            </div>
                            <p class="rating-text" id="ratingText">Excellent!</p>
                        </div>

                        <div class="mb-4">
                            <label for="review" class="form-label fw-bold">Your Review</label>
                            <textarea class="form-control" id="review" name="review" rows="5" required
                                      placeholder="What did you like or dislike about this product? How was the quality? Would you recommend it?"><?php echo e($_POST['review'] ?? ''); ?></textarea>
                            <small class="text-muted">Minimum 10 characters</small>
                        </div>

                        <button type="submit" class="btn-submit-review">
                            ⭐ Submit Review
                        </button>
                        
                        <div class="text-center mt-3">
                            <a href="<?php echo url('product.php?id=' . $productId); ?>" class="text-muted">Skip for now</a>
                        </div>
                    </form>
                    
                    <?php if (!empty($remainingProducts) && count($remainingProducts) > 1): ?>
                        <div class="more-products mt-4">
                            <h6>📦 Other products to review (<?php echo count($remainingProducts); ?>):</h6>
                            <?php foreach (array_slice($remainingProducts, 0, 3) as $p): ?>
                                <?php if ($p['id'] != $productId): ?>
                                    <a href="<?php echo url('add_review.php?product_id=' . $p['id'] . '&from=checkout'); ?>" class="product-to-review">
                                        <img src="<?php echo e(BASE_URL . '/' . ltrim($p['image'] ?? 'assets/images/products/default.svg', '/')); ?>" alt="">
                                        <span><?php echo e($p['name']); ?></span>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Update rating text based on selection
        const ratingLabels = {
            5: 'Excellent! 🌟',
            4: 'Very Good! 😊',
            3: 'Good 👍',
            2: 'Fair 😐',
            1: 'Poor 😞'
        };
        
        document.querySelectorAll('.star-rating input').forEach(input => {
            input.addEventListener('change', function() {
                document.getElementById('ratingText').textContent = ratingLabels[this.value];
            });
        });
    </script>
</body>
</html>
