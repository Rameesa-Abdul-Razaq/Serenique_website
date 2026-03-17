<?php
/**
 * Admin Dashboard — Users, Products & Stock Levels
 */
require_once __DIR__ . '/../session.php';

requireAdmin();

$conn = getDB();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_user') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId && $userId != getCurrentUserId()) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            if ($stmt->execute()) {
                $message = 'User deleted successfully.';
                $messageType = 'success';
            }
            $stmt->close();
        }
    } elseif ($action === 'delete_product') {
        $productId = (int)($_POST['product_id'] ?? 0);
        if ($productId) {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $productId);
            if ($stmt->execute()) {
                $message = 'Product deleted successfully.';
                $messageType = 'success';
            }
            $stmt->close();
        }
    } elseif ($action === 'update_stock') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $newStock   = (int)($_POST['new_stock']  ?? 0);
        if ($productId && $newStock >= 0) {
            $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $stmt->bind_param("ii", $newStock, $productId);
            if ($stmt->execute()) {
                $message = 'Stock updated successfully.';
                $messageType = 'success';
            }
            $stmt->close();
        }
    }
}

// Fetch data
$users    = $conn->query("SELECT * FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$products = $conn->query("
    SELECT p.*, u.username AS seller_name
    FROM   products p
    LEFT   JOIN users u ON p.posted_by = u.id
    ORDER  BY p.stock ASC, p.name ASC
")->fetch_all(MYSQLI_ASSOC);

// Stock summary by category
$catStock = $conn->query("
    SELECT category,
           SUM(stock)                           AS total_stock,
           COUNT(*)                             AS product_count,
           SUM(CASE WHEN stock = 0  THEN 1 ELSE 0 END) AS out_of_stock,
           SUM(CASE WHEN stock > 0 AND stock <= 10 THEN 1 ELSE 0 END) AS low_stock
    FROM   products
    GROUP  BY category
    ORDER  BY category
")->fetch_all(MYSQLI_ASSOC);

$userCount      = count($users);
$productCount   = count($products);
$outOfStock     = array_sum(array_column(array_filter($products, fn($p) => $p['stock'] == 0), 'stock') ?: []) === 0
                  ? count(array_filter($products, fn($p) => (int)$p['stock'] === 0))
                  : 0;
$outOfStock     = count(array_filter($products, fn($p) => (int)$p['stock'] === 0));
$lowStock       = count(array_filter($products, fn($p) => (int)$p['stock'] > 0 && (int)$p['stock'] <= 10));
$totalStockValue = array_sum(array_map(fn($p) => $p['price'] * $p['stock'], $products));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <title>Serenique | Admin Dashboard</title>
    <style>
        :root { --admin-primary: #d27b5a; --admin-dark: #b86a4d; --admin-light: #fdf8f5; }

        body { background: var(--admin-light); }

        .admin-topbar {
            background: linear-gradient(135deg, #2c2c2c 0%, #3d3d3d 100%);
            color: #fff;
            padding: 0.9rem 0;
            position: sticky; top: 0; z-index: 1000;
            box-shadow: 0 2px 12px rgba(0,0,0,0.25);
        }
        .admin-topbar .brand { font-family: 'Playfair Display', serif; font-size: 1.3rem; color: var(--admin-primary); }
        .admin-topbar .nav-link { color: rgba(255,255,255,.8); font-size: .875rem; }
        .admin-topbar .nav-link:hover, .admin-topbar .nav-link.active { color: #fff; }

        .page-hero {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-dark) 100%);
            color: #fff; padding: 2rem 0; margin-bottom: 2rem;
        }
        .page-hero h1 { font-family: 'Playfair Display', serif; font-size: 1.8rem; margin: 0; }
        .page-hero p  { margin: 0; opacity: .85; }

        .stat-card {
            background: #fff; border-radius: 14px; padding: 1.4rem 1.6rem;
            box-shadow: 0 3px 16px rgba(0,0,0,.08);
            border-left: 5px solid transparent;
            transition: transform .2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card.primary   { border-color: var(--admin-primary); }
        .stat-card.success   { border-color: #28a745; }
        .stat-card.danger    { border-color: #dc3545; }
        .stat-card.warning   { border-color: #ffc107; }
        .stat-card .stat-num { font-size: 2.2rem; font-weight: 700; line-height: 1; }
        .stat-card .stat-lbl { color: #666; font-size: .85rem; margin-top: .25rem; }
        .stat-card.danger  .stat-num { color: #dc3545; }
        .stat-card.warning .stat-num { color: #d97706; }
        .stat-card.success .stat-num { color: #28a745; }
        .stat-card.primary .stat-num { color: var(--admin-primary); }

        .section-card {
            background: #fff; border-radius: 14px; padding: 1.5rem;
            box-shadow: 0 3px 16px rgba(0,0,0,.08); margin-bottom: 2rem;
        }
        .section-card h3 {
            font-family: 'Playfair Display', serif; font-size: 1.15rem;
            border-bottom: 2px solid #f3ede8; padding-bottom: .6rem; margin-bottom: 1rem;
        }

        .stock-bar-wrap { min-width: 100px; }
        .stock-bar {
            height: 8px; border-radius: 4px; background: #e9ecef; overflow: hidden;
        }
        .stock-bar-fill { height: 100%; border-radius: 4px; transition: width .3s; }

        .badge-out   { background: #fee2e2; color: #b91c1c; }
        .badge-low   { background: #fff3cd; color: #92400e; }
        .badge-ok    { background: #d1fae5; color: #065f46; }
        .badge-high  { background: #dbeafe; color: #1e3a8a; }

        .table th { background: #f8f5f2; font-size: .8rem; text-transform: uppercase; letter-spacing: .03em; }
        .table td { vertical-align: middle; font-size: .875rem; }

        .inline-stock-form { display: flex; align-items: center; gap: .35rem; }
        .inline-stock-form input[type=number] {
            width: 72px; padding: .25rem .4rem; border: 1px solid #d1d5db;
            border-radius: 6px; font-size: .82rem;
        }
        .inline-stock-form button {
            padding: .25rem .55rem; font-size: .78rem; border-radius: 6px;
            background: var(--admin-primary); color: #fff; border: none; cursor: pointer;
        }
        .inline-stock-form button:hover { background: var(--admin-dark); }

        .cat-pill {
            display: inline-block; padding: .2rem .65rem; border-radius: 20px;
            font-size: .78rem; font-weight: 600; background: #f0ebe6; color: #7c4a2e;
        }

        @media(max-width:768px) { .stat-card { margin-bottom: 1rem; } }
    </style>
</head>
<body>

<!-- Top Navigation Bar -->
<nav class="admin-topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <span class="brand">Serenique Admin</span>
        <div class="d-flex gap-3 align-items-center">
            <a href="#stock"    class="nav-link">Stock</a>
            <a href="#users"    class="nav-link">Users</a>
            <a href="#products" class="nav-link">Products</a>
            <a href="../homepage.php" class="nav-link">View Site</a>
            <a href="../logout.php"   class="btn btn-sm" style="background:var(--admin-primary);color:#fff;border-radius:8px;">Logout</a>
        </div>
    </div>
</nav>

<!-- Hero -->
<div class="page-hero">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1>Admin Dashboard</h1>
            <p>Welcome back, <?php echo e(getCurrentUsername()); ?> &mdash; <?php echo date('l, j F Y'); ?></p>
        </div>
    </div>
</div>

<main class="container pb-5">

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show mb-4">
            <?php echo e($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ─── STAT CARDS ─── -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card primary">
                <div class="stat-num"><?php echo $userCount; ?></div>
                <div class="stat-lbl">Total Users</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card success">
                <div class="stat-num"><?php echo $productCount; ?></div>
                <div class="stat-lbl">Total Products</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card danger">
                <div class="stat-num"><?php echo $outOfStock; ?></div>
                <div class="stat-lbl">Out of Stock</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card warning">
                <div class="stat-num"><?php echo $lowStock; ?></div>
                <div class="stat-lbl">Low Stock (&le;10)</div>
            </div>
        </div>
    </div>

    <!-- ─── STOCK OVERVIEW BY CATEGORY ─── -->
    <div id="stock" class="section-card">
        <h3>📊 Stock Levels by Category</h3>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Products</th>
                        <th>Total Units</th>
                        <th>Out of Stock</th>
                        <th>Low Stock</th>
                        <th>Stock Bar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $maxCatStock = max(array_column($catStock, 'total_stock') ?: [1]);
                    foreach ($catStock as $cat):
                        $pct = $maxCatStock > 0 ? ($cat['total_stock'] / $maxCatStock * 100) : 0;
                        $barColor = '#28a745';
                        if ($cat['out_of_stock'] > 0 && $cat['out_of_stock'] >= $cat['product_count'] * 0.5) $barColor = '#dc3545';
                        elseif ($cat['low_stock'] > 0) $barColor = '#ffc107';
                    ?>
                    <tr>
                        <td><span class="cat-pill"><?php echo e($cat['category'] ?? 'Uncategorised'); ?></span></td>
                        <td><?php echo $cat['product_count']; ?></td>
                        <td><strong><?php echo number_format($cat['total_stock']); ?></strong></td>
                        <td>
                            <?php if ($cat['out_of_stock'] > 0): ?>
                                <span class="badge badge-out rounded-pill"><?php echo $cat['out_of_stock']; ?> out</span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($cat['low_stock'] > 0): ?>
                                <span class="badge badge-low rounded-pill"><?php echo $cat['low_stock']; ?> low</span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="width:160px">
                            <div class="stock-bar">
                                <div class="stock-bar-fill" style="width:<?php echo round($pct); ?>%;background:<?php echo $barColor; ?>"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ─── PRODUCT STOCK TABLE ─── -->
    <div id="products" class="section-card">
        <h3>📦 All Products &amp; Stock</h3>
        <p class="text-muted small mb-3">Sorted by lowest stock first. You can update stock inline.</p>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Stock</th>
                        <th>Update Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p):
                        $stock = (int)$p['stock'];
                        if ($stock === 0)       { $badgeCls = 'badge-out';  $label = 'Out of Stock'; }
                        elseif ($stock <= 5)    { $badgeCls = 'badge-out';  $label = 'Critical'; }
                        elseif ($stock <= 10)   { $badgeCls = 'badge-low';  $label = 'Low'; }
                        elseif ($stock <= 50)   { $badgeCls = 'badge-ok';   $label = 'Good'; }
                        else                    { $badgeCls = 'badge-high'; $label = 'High'; }
                    ?>
                    <tr>
                        <td class="text-muted"><?php echo $p['id']; ?></td>
                        <td><?php echo e($p['name']); ?></td>
                        <td><span class="cat-pill"><?php echo e($p['category'] ?? '—'); ?></span></td>
                        <td>£<?php echo number_format($p['price'], 2); ?></td>
                        <td>
                            <span class="badge <?php echo $p['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $p['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo $badgeCls; ?> rounded-pill px-2">
                                <?php echo $stock; ?> &mdash; <?php echo $label; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" class="inline-stock-form">
                                <input type="hidden" name="action"     value="update_stock">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <input type="number"  name="new_stock"  value="<?php echo $stock; ?>" min="0" max="9999">
                                <button type="submit">Save</button>
                            </form>
                        </td>
                        <td>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete <?php echo e(addslashes($p['name'])); ?>?');">
                                <input type="hidden" name="action"     value="delete_product">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ─── USERS TABLE ─── -->
    <div id="users" class="section-card">
        <h3>👥 All Users</h3>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="text-muted"><?php echo $user['id']; ?></td>
                        <td>
                            <?php echo e($user['username']); ?>
                            <?php if ($user['id'] == getCurrentUserId()): ?>
                                <span class="badge bg-info ms-1">You</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($user['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-danger' : 'bg-secondary'; ?>">
                                <?php echo $user['role']; ?>
                            </span>
                        </td>
                        <td><?php echo date('j M Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if ($user['id'] != getCurrentUserId()): ?>
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete user <?php echo e(addslashes($user['username'])); ?>? This removes all their products too.');">
                                    <input type="hidden" name="action"  value="delete_user">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-center pb-3">
        <a href="../homepage.php" class="btn btn-secondary me-2">← Back to Site</a>
        <a href="../products.php" class="btn btn-outline-secondary">View Products Page</a>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
