<?php
/**
 * Products Listing Page
 */
require_once __DIR__ . '/session.php';

$u = SITE_URL;

// ── Filters ──────────────────────────────────────────────────────────────
$category    = trim($_GET['category']  ?? '');
$subcategory = trim($_GET['sub']       ?? '');
$search      = trim($_GET['search']    ?? '');
$sort        = trim($_GET['sort']      ?? 'newest');
$minPrice    = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$maxPrice    = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
$inStock     = isset($_GET['in_stock']) && $_GET['in_stock'] === '1';
$mine        = isset($_GET['mine']) && isLoggedIn();
$page        = max(1, (int)($_GET['page'] ?? 1));
$perPage     = 24;

$conn = getDB();

// ── Category counts for badges ────────────────────────────────────────────
$catCountsRaw = $conn->query("SELECT category, COUNT(*) as cnt FROM products GROUP BY category")->fetch_all(MYSQLI_ASSOC);
$catCounts = [];
foreach ($catCountsRaw as $row) $catCounts[$row['category']] = $row['cnt'];

// ── Build main query ──────────────────────────────────────────────────────
$sql = "SELECT p.*, u.username AS seller_name,
               ROUND(COALESCE(AVG(r.rating),0),1) AS avg_rating,
               COUNT(DISTINCT r.id) AS review_count
        FROM products p
        LEFT JOIN users u   ON p.posted_by = u.id
        LEFT JOIN reviews r ON r.product_id = p.id
        WHERE 1=1";
$params = [];
$types  = "";

if ($category) {
    $sql .= " AND p.category = ?";
    $params[] = $category; $types .= "s";
}
if ($subcategory) {
    $sql .= " AND p.subcategory = ?";
    $params[] = $subcategory; $types .= "s";
}
if ($search) {
    $sql .= " AND (LOWER(p.name) LIKE LOWER(?) OR LOWER(p.description) LIKE LOWER(?))";
    $t = "%$search%";
    $params[] = $t; $params[] = $t; $types .= "ss";
}
if ($minPrice !== null) {
    $sql .= " AND p.price >= ?";
    $params[] = $minPrice; $types .= "d";
}
if ($maxPrice !== null) {
    $sql .= " AND p.price <= ?";
    $params[] = $maxPrice; $types .= "d";
}
if ($inStock) {
    $sql .= " AND p.stock > 0";
}
if ($mine) {
    $sql .= " AND p.posted_by = ?";
    $params[] = getCurrentUserId(); $types .= "i";
}

$sql .= " GROUP BY p.id";

$sortMap = [
    'newest'     => 'p.created_at DESC',
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'name_asc'   => 'p.name ASC',
    'rating'     => 'avg_rating DESC',
];
$sql .= " ORDER BY " . ($sortMap[$sort] ?? 'p.created_at DESC');

// Count total for pagination
$countStmt = $conn->prepare("SELECT COUNT(*) FROM ($sql) AS sub");
if (!empty($params)) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalProducts = $countStmt->get_result()->fetch_row()[0];
$countStmt->close();
$totalPages = max(1, ceil($totalProducts / $perPage));
$page = min($page, $totalPages);

// Add LIMIT/OFFSET
$sql .= " LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = ($page - 1) * $perPage;
$types .= "ii";

$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Price range for current filter set ───────────────────────────────────
$priceRangeSQL = "SELECT MIN(price) as mn, MAX(price) as mx FROM products WHERE 1=1";
$priceParams = []; $priceTypes = "";
if ($category) { $priceRangeSQL .= " AND category = ?"; $priceParams[] = $category; $priceTypes .= "s"; }
$priceStmt = $conn->prepare($priceRangeSQL);
if (!empty($priceParams)) $priceStmt->bind_param($priceTypes, ...$priceParams);
$priceStmt->execute();
$priceRange = $priceStmt->get_result()->fetch_assoc();
$priceStmt->close();
$globalMin = floor($priceRange['mn'] ?? 0);
$globalMax = ceil($priceRange['mx']  ?? 500);

// ── Subcategory labels ────────────────────────────────────────────────────
$subLabels = [
    'serum'      => 'Serums',
    'cleanser'   => 'Cleansers',
    'cream'      => 'Creams & Moisturisers',
    'toner'      => 'Toners',
    'mask'       => 'Masks',
    'lipstick'   => 'Lipsticks & Lip Gloss',
    'foundation' => 'Foundation & Base',
    'mascara'    => 'Mascara & Eyeliner',
    'eyeshadow'  => 'Eyeshadow & Cheeks',
    'shampoo'    => 'Shampoo & Treatments',
    'conditioner'=> 'Conditioner & Leave-In',
    'oil'        => 'Hair Oils & Serums',
    'brush'      => 'Brushes & Applicators',
    'sponge'     => 'Sponges & Scrubbers',
    'roller'     => 'Face Rollers & Devices',
    'parfum'     => 'Perfumes & Eau de Parfum',
    'mist'       => 'Body Mists & Sprays',
];

// ── Page title ────────────────────────────────────────────────────────────
$pageTitle = 'All Products';
if ($category && $subcategory) {
    $pageTitle = $category . ' — ' . ($subLabels[$subcategory] ?? ucfirst($subcategory));
} elseif ($category) {
    $pageTitle = $category;
}
if ($mine)   $pageTitle = 'My Products';
if ($search) $pageTitle = 'Search: "' . e($search) . '"';

// Active filters count (for badge)
$activeFilters = array_filter([$category, $subcategory, $search,
    $minPrice !== null ? '1' : '',
    $maxPrice !== null ? '1' : '',
    $inStock ? '1' : '']);
$filterCount = count($activeFilters);

// ── URL builder helper ────────────────────────────────────────────────────
function buildUrl($overrides = []) {
    $base = ['category','sub','search','sort','min_price','max_price','in_stock','mine'];
    $params = [];
    foreach ($base as $k) {
        $val = $overrides[$k] ?? ($_GET[$k] ?? '');
        if ($val !== '' && $val !== null) $params[$k] = $val;
    }
    foreach ($overrides as $k => $v) {
        if ($v === '' || $v === null) unset($params[$k]);
        else $params[$k] = $v;
    }
    unset($params['page']);
    if (isset($overrides['page']) && $overrides['page'] > 1) $params['page'] = $overrides['page'];
    return 'products.php?' . http_build_query($params);
}

$success = getFlash('success');
$error   = getFlash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Serenique | <?php echo e($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo $u; ?>/frontend/products.css">
    <style>
        /* ── Layout ── */
        .shop-layout { display: grid; grid-template-columns: 260px 1fr; gap: 2rem; align-items: start; }
        @media(max-width:900px) { .shop-layout { grid-template-columns: 1fr; } .sidebar { display: none; } .sidebar.open { display: block; } }

        /* ── Sidebar ── */
        .sidebar { background: #fff; border-radius: 16px; padding: 1.5rem; box-shadow: 0 3px 16px rgba(0,0,0,.07); position: sticky; top: 80px; }
        .sidebar h5 { font-family: 'Playfair Display', serif; font-size: 1rem; color: #2c2c2c; margin-bottom: 1rem; padding-bottom: .5rem; border-bottom: 2px solid #f0ebe6; }
        .sidebar-section { margin-bottom: 1.6rem; }
        .cat-pill { display: flex; justify-content: space-between; align-items: center; padding: .45rem .75rem; border-radius: 8px; font-size: .85rem; color: #555; cursor: pointer; text-decoration: none; transition: background .15s, color .15s; }
        .cat-pill:hover { background: #fdf0ea; color: #d27b5a; }
        .cat-pill.active { background: #d27b5a; color: #fff; font-weight: 600; }
        .cat-pill .cnt { font-size: .72rem; background: rgba(0,0,0,.08); border-radius: 20px; padding: 1px 7px; }
        .cat-pill.active .cnt { background: rgba(255,255,255,.3); }

        .price-inputs { display: flex; gap: .5rem; align-items: center; }
        .price-inputs input { width: 80px; padding: .4rem .6rem; border: 1.5px solid #e8e0da; border-radius: 8px; font-size: .82rem; }
        .price-inputs input:focus { outline: none; border-color: #d27b5a; }
        .price-inputs span { color: #aaa; font-size: .8rem; }

        .check-label { display: flex; align-items: center; gap: .5rem; font-size: .85rem; color: #555; cursor: pointer; }
        .check-label input[type=checkbox] { accent-color: #d27b5a; width: 16px; height: 16px; }

        .btn-apply-filter { width: 100%; padding: .6rem; background: #d27b5a; color: #fff; border: none; border-radius: 8px; font-size: .875rem; font-weight: 600; cursor: pointer; margin-top: .5rem; }
        .btn-apply-filter:hover { background: #b8654a; }
        .btn-clear-filter { width: 100%; padding: .5rem; background: none; color: #aaa; border: 1px solid #e8e0da; border-radius: 8px; font-size: .8rem; cursor: pointer; margin-top: .4rem; }
        .btn-clear-filter:hover { color: #d27b5a; border-color: #d27b5a; }

        /* ── Main area ── */
        .products-main {}

        /* ── Toolbar ── */
        .products-toolbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .75rem; margin-bottom: 1.25rem; }
        .toolbar-left { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
        .toolbar-right { display: flex; align-items: center; gap: .5rem; }

        /* Active filter chips */
        .filter-chips { display: flex; flex-wrap: wrap; gap: .4rem; }
        .chip { display: inline-flex; align-items: center; gap: .35rem; background: #f0ebe6; color: #7c4a2e; padding: .25rem .65rem; border-radius: 20px; font-size: .78rem; font-weight: 600; text-decoration: none; }
        .chip .chip-x { color: #b07050; font-size: .85rem; line-height: 1; }
        .chip:hover { background: #e8d5c8; }

        /* Sort select */
        .sort-select { padding: .4rem .75rem; border: 1.5px solid #e8e0da; border-radius: 8px; font-size: .85rem; color: #555; background: #fff; cursor: pointer; }
        .sort-select:focus { outline: none; border-color: #d27b5a; }

        /* View toggle */
        .view-btn { width: 36px; height: 36px; border: 1.5px solid #e8e0da; border-radius: 8px; background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #aaa; transition: all .15s; }
        .view-btn.active, .view-btn:hover { border-color: #d27b5a; color: #d27b5a; background: #fdf0ea; }

        /* Results count */
        .results-count { font-size: .85rem; color: #888; }

        /* Mobile filter toggle */
        .mobile-filter-btn { display: none; padding: .45rem 1rem; background: #fff; border: 1.5px solid #e8e0da; border-radius: 8px; font-size: .85rem; color: #555; cursor: pointer; }
        @media(max-width:900px) { .mobile-filter-btn { display: flex; align-items: center; gap: .4rem; } }

        /* ── Product Grid ── */
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.5rem; }
        .products-grid.list-view { grid-template-columns: 1fr; }

        /* ── Product Card ── */
        .product-card { background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 3px 14px rgba(0,0,0,.08); transition: transform .25s, box-shadow .25s; position: relative; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,.13); }
        .product-card.out-of-stock { opacity: .65; }

        /* Image wrapper with hover overlay */
        .card-img-wrap { position: relative; overflow: hidden; }
        .card-img-wrap img { width: 100%; height: 220px; object-fit: cover; display: block; transition: transform .4s; }
        .product-card:hover .card-img-wrap img { transform: scale(1.05); }
        .card-overlay { position: absolute; inset: 0; background: rgba(0,0,0,.35); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity .25s; z-index: 2; pointer-events: none; }
        .product-card:hover .card-overlay { opacity: 1; pointer-events: auto; }
        .overlay-btn { background: #fff; color: #2c2c2c; padding: .5rem 1.2rem; border-radius: 20px; font-size: .82rem; font-weight: 700; text-decoration: none; transition: background .15s; display: inline-block; }
        .overlay-btn:hover { background: #d27b5a; color: #fff; }

        /* Badges */
        .card-badges { position: absolute; top: .6rem; left: .6rem; display: flex; flex-direction: column; gap: .3rem; z-index: 2; }
        .badge-new { background: #d27b5a; color: #fff; font-size: .68rem; font-weight: 700; padding: .2rem .55rem; border-radius: 4px; }
        .badge-oos { background: #dc3545; color: #fff; font-size: .68rem; font-weight: 700; padding: .2rem .55rem; border-radius: 4px; }
        .badge-low { background: #f59e0b; color: #fff; font-size: .68rem; font-weight: 700; padding: .2rem .55rem; border-radius: 4px; }

        /* Card body */
        .card-body { padding: 1rem 1.1rem; }
        .card-cat { font-size: .72rem; color: #d27b5a; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        .card-title { font-family: 'Playfair Display', serif; font-size: 1rem; color: #2c2c2c; margin: .3rem 0 .4rem; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .card-stars { display: flex; align-items: center; gap: .3rem; margin-bottom: .45rem; }
        .stars { color: #f59e0b; font-size: .8rem; letter-spacing: .05em; }
        .stars-count { font-size: .72rem; color: #aaa; }
        .card-footer { display: flex; justify-content: space-between; align-items: center; margin-top: .6rem; gap: .5rem; }
        .card-price { color: #d27b5a; font-weight: 700; font-size: 1.1rem; }
        .btn-cart { flex: 1; padding: .5rem .6rem; background: linear-gradient(135deg, #d27b5a, #b8654a); color: #fff; border: none; border-radius: 8px; font-size: .8rem; font-weight: 600; cursor: pointer; transition: opacity .2s; text-align: center; text-decoration: none; display: block; }
        .btn-cart:hover { opacity: .88; color: #fff; }
        .btn-cart:disabled, .btn-cart.disabled { background: #ccc; cursor: not-allowed; opacity: 1; }
        .btn-login-buy { flex: 1; padding: .5rem .6rem; background: none; color: #d27b5a; border: 1.5px solid #d27b5a; border-radius: 8px; font-size: .8rem; font-weight: 600; text-align: center; text-decoration: none; display: block; }
        .btn-login-buy:hover { background: #d27b5a; color: #fff; }
        .btn-delete { width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; border: 1.5px solid #fee2e2; border-radius: 8px; color: #dc3545; background: none; font-size: .85rem; cursor: pointer; text-decoration: none; flex-shrink: 0; }
        .btn-delete:hover { background: #fee2e2; color: #dc3545; }

        /* List view card */
        .list-view .product-card { display: flex; flex-direction: row; }
        .list-view .card-img-wrap { width: 160px; flex-shrink: 0; }
        .list-view .card-img-wrap img { height: 100%; min-height: 130px; }
        .list-view .card-body { flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .list-view .card-title { -webkit-line-clamp: 1; font-size: 1.05rem; }
        .list-view .card-desc { font-size: .82rem; color: #888; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: .5rem; }
        @media(max-width:600px) { .list-view .product-card { flex-direction: column; } .list-view .card-img-wrap { width: 100%; } }

        /* ── Pagination ── */
        .pagination-wrap { display: flex; justify-content: center; align-items: center; gap: .4rem; margin-top: 2.5rem; flex-wrap: wrap; }
        .page-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 38px; padding: 0 .7rem; border: 1.5px solid #e8e0da; border-radius: 8px; font-size: .85rem; color: #555; text-decoration: none; background: #fff; transition: all .15s; }
        .page-btn:hover { border-color: #d27b5a; color: #d27b5a; }
        .page-btn.active { background: #d27b5a; border-color: #d27b5a; color: #fff; font-weight: 700; }
        .page-btn.disabled { opacity: .4; pointer-events: none; }

        /* ── Empty state ── */
        .empty-state { text-align: center; padding: 4rem 2rem; }
        .empty-state .empty-icon { font-size: 4rem; margin-bottom: 1rem; }
        .empty-state h3 { font-family: 'Playfair Display', serif; color: #2c2c2c; }
        .empty-state p { color: #888; }

        /* ── Promo banner ── */
        .promo-banner { background: linear-gradient(135deg, #d27b5a 0%, #e8a87c 100%); color: white; padding: 1rem 1.5rem; border-radius: 12px; display: flex; align-items: center; gap: 15px; margin-bottom: 1.5rem; }
        .promo-banner .promo-icon { font-size: 2.2rem; flex-shrink: 0; }
        .promo-banner h4 { margin: 0; font-size: 1.1rem; }
        .promo-banner p { margin: 0; opacity: .9; font-size: .85rem; }

        /* ── Breadcrumb ── */
        .bc { font-size: .83rem; color: #aaa; margin-bottom: 1.2rem; }
        .bc a { color: #d27b5a; text-decoration: none; }
        .bc a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<?php include 'includes/nav.php'; ?>

<main class="container py-4">

    <!-- Promo banner -->
    <div class="promo-banner">
        <div class="promo-icon">🎁</div>
        <div>
            <h4>3 FOR 2 — Buy 3, Get the Cheapest FREE!</h4>
            <p>Mix &amp; match any products. Discount applied automatically at checkout.</p>
        </div>
        <a href="<?php echo $u; ?>/products.php" class="ms-auto btn btn-sm" style="background:rgba(255,255,255,.25);color:#fff;border-radius:20px;white-space:nowrap;">Shop Now →</a>
    </div>

    <!-- Breadcrumb -->
    <div class="bc">
        <a href="<?php echo $u; ?>/products.php">All Products</a>
        <?php if ($category): ?>
            <span> / </span><a href="<?php echo $u; ?>/products.php?category=<?php echo urlencode($category); ?>"><?php echo e($category); ?></a>
        <?php endif; ?>
        <?php if ($subcategory): ?>
            <span> / </span><span><?php echo e($subLabels[$subcategory] ?? ucfirst($subcategory)); ?></span>
        <?php endif; ?>
        <?php if ($search): ?>
            <span> / </span><span>Search: "<?php echo e($search); ?>"</span>
        <?php endif; ?>
    </div>

    <?php if ($success): ?><div class="alert alert-success mb-3"><?php echo e($success); ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger mb-3"><?php echo e($error); ?></div><?php endif; ?>

    <div class="shop-layout">

        <!-- ── SIDEBAR ── -->
        <aside class="sidebar" id="sidebar">

            <!-- Search -->
            <div class="sidebar-section">
                <h5>Search</h5>
                <form method="GET" action="<?php echo url('products.php'); ?>">
                    <?php if ($category): ?><input type="hidden" name="category" value="<?php echo e($category); ?>"><?php endif; ?>
                    <div style="display:flex;gap:.4rem">
                        <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search products…"
                               style="flex:1;padding:.5rem .75rem;border:1.5px solid #e8e0da;border-radius:8px;font-size:.85rem;outline:none">
                        <button type="submit" style="padding:.5rem .8rem;background:#d27b5a;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:.85rem;">→</button>
                    </div>
                </form>
            </div>

            <!-- Categories -->
            <div class="sidebar-section">
                <h5>Categories</h5>
                <a href="<?php echo $u; ?>/products.php" class="cat-pill <?php echo !$category ? 'active' : ''; ?>">
                    All Products <span class="cnt"><?php echo array_sum($catCounts); ?></span>
                </a>
                <?php
                $cats = ['Skincare','Makeup','Haircare','Fragrance','Bath & Body','Tools','Wellness'];
                foreach ($cats as $cat):
                    $cnt = $catCounts[$cat] ?? 0;
                    $isActive = $category === $cat;
                ?>
                <a href="<?php echo $u; ?>/products.php?category=<?php echo urlencode($cat); ?>"
                   class="cat-pill <?php echo $isActive ? 'active' : ''; ?>">
                    <?php echo e($cat); ?> <span class="cnt"><?php echo $cnt; ?></span>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Price range -->
            <div class="sidebar-section">
                <h5>Price Range</h5>
                <form method="GET" id="priceForm">
                    <?php if ($category):    ?><input type="hidden" name="category"  value="<?php echo e($category); ?>"><?php endif; ?>
                    <?php if ($subcategory): ?><input type="hidden" name="sub"       value="<?php echo e($subcategory); ?>"><?php endif; ?>
                    <?php if ($search):      ?><input type="hidden" name="search"    value="<?php echo e($search); ?>"><?php endif; ?>
                    <?php if ($sort !== 'newest'): ?><input type="hidden" name="sort" value="<?php echo e($sort); ?>"><?php endif; ?>
                    <?php if ($inStock):     ?><input type="hidden" name="in_stock"  value="1"><?php endif; ?>
                    <div class="price-inputs">
                        <input type="number" name="min_price" placeholder="£<?php echo $globalMin; ?>"
                               value="<?php echo $minPrice !== null ? $minPrice : ''; ?>" min="0" step="1">
                        <span>—</span>
                        <input type="number" name="max_price" placeholder="£<?php echo $globalMax; ?>"
                               value="<?php echo $maxPrice !== null ? $maxPrice : ''; ?>" min="0" step="1">
                    </div>
                    <button type="submit" class="btn-apply-filter">Apply</button>
                    <?php if ($minPrice !== null || $maxPrice !== null): ?>
                        <a href="<?php echo $u; ?>/<?php echo e(buildUrl(['min_price'=>'','max_price'=>''])); ?>" class="btn-clear-filter" style="display:block;text-align:center;text-decoration:none;padding:.5rem;color:#aaa;font-size:.8rem;">Clear price filter</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- In stock filter -->
            <div class="sidebar-section">
                <h5>Availability</h5>
                <label class="check-label">
                    <input type="checkbox" id="inStockCheck" <?php echo $inStock ? 'checked' : ''; ?>>
                    In stock only
                </label>
            </div>

            <!-- Clear all -->
            <?php if ($filterCount > 0): ?>
            <a href="<?php echo $u; ?>/products.php" class="btn-clear-filter" style="display:block;text-align:center;text-decoration:none;padding:.5rem;color:#d27b5a;font-size:.82rem;border-color:#d27b5a;">✕ Clear all filters</a>
            <?php endif; ?>
        </aside>

        <!-- ── MAIN ── -->
        <div class="products-main">

            <!-- Toolbar -->
            <div class="products-toolbar">
                <div class="toolbar-left">
                    <!-- Mobile filter toggle -->
                    <button class="mobile-filter-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">
                        ☰ Filters <?php if ($filterCount > 0): ?><span style="background:#d27b5a;color:#fff;border-radius:20px;padding:0 6px;font-size:.72rem;"><?php echo $filterCount; ?></span><?php endif; ?>
                    </button>

                    <!-- Active filter chips -->
                    <div class="filter-chips">
                        <?php if ($category): ?>
                            <a href="<?php echo $u; ?>/<?php echo e(buildUrl(['category'=>'','sub'=>''])); ?>" class="chip"><?php echo e($category); ?> <span class="chip-x">×</span></a>
                        <?php endif; ?>
                        <?php if ($subcategory): ?>
                            <a href="<?php echo $u; ?>/<?php echo e(buildUrl(['sub'=>''])); ?>" class="chip"><?php echo e($subLabels[$subcategory] ?? $subcategory); ?> <span class="chip-x">×</span></a>
                        <?php endif; ?>
                        <?php if ($search): ?>
                            <a href="<?php echo $u; ?>/<?php echo e(buildUrl(['search'=>''])); ?>" class="chip">"<?php echo e($search); ?>" <span class="chip-x">×</span></a>
                        <?php endif; ?>
                        <?php if ($minPrice !== null): ?>
                            <a href="<?php echo $u; ?>/<?php echo e(buildUrl(['min_price'=>''])); ?>" class="chip">From £<?php echo $minPrice; ?> <span class="chip-x">×</span></a>
                        <?php endif; ?>
                        <?php if ($maxPrice !== null): ?>
                            <a href="<?php echo $u; ?>/<?php echo e(buildUrl(['max_price'=>''])); ?>" class="chip">To £<?php echo $maxPrice; ?> <span class="chip-x">×</span></a>
                        <?php endif; ?>
                        <?php if ($inStock): ?>
                            <a href="<?php echo $u; ?>/<?php echo e(buildUrl(['in_stock'=>''])); ?>" class="chip">In Stock <span class="chip-x">×</span></a>
                        <?php endif; ?>
                    </div>

                    <span class="results-count"><?php echo number_format($totalProducts); ?> product<?php echo $totalProducts !== 1 ? 's' : ''; ?></span>
                </div>

                <div class="toolbar-right">
                    <!-- Sort -->
                    <select class="sort-select" onchange="changeSortUrl(this.value)">
                        <option value="newest"     <?php echo $sort==='newest'     ?'selected':''; ?>>Newest</option>
                        <option value="price_asc"  <?php echo $sort==='price_asc'  ?'selected':''; ?>>Price: Low → High</option>
                        <option value="price_desc" <?php echo $sort==='price_desc' ?'selected':''; ?>>Price: High → Low</option>
                        <option value="name_asc"   <?php echo $sort==='name_asc'   ?'selected':''; ?>>Name: A → Z</option>
                        <option value="rating"     <?php echo $sort==='rating'     ?'selected':''; ?>>Top Rated</option>
                    </select>

                    <!-- Grid/List toggle -->
                    <button class="view-btn active" id="btnGrid" onclick="setView('grid')" title="Grid view">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor"><rect x="0" y="0" width="6" height="6"/><rect x="8" y="0" width="6" height="6"/><rect x="0" y="8" width="6" height="6"/><rect x="8" y="8" width="6" height="6"/></svg>
                    </button>
                    <button class="view-btn" id="btnList" onclick="setView('list')" title="List view">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor"><rect x="0" y="0" width="14" height="3"/><rect x="0" y="5.5" width="14" height="3"/><rect x="0" y="11" width="14" height="3"/></svg>
                    </button>

                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo $u; ?>/add_product.php" class="btn btn-sm btn-primary">+ Add</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Grid -->
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🔍</div>
                    <h3>No products found</h3>
                    <p>Try adjusting your filters or searching for something different.</p>
                    <a href="<?php echo $u; ?>/products.php" class="btn btn-primary mt-3">View all products</a>
                </div>
            <?php else: ?>
            <div class="products-grid" id="productsGrid">
                <?php foreach ($products as $p):
                    $imgPath = $p['image'] ?? 'assets/images/products/default.svg';
                    if (strpos($imgPath, 'http') !== 0) $imgPath = BASE_URL . '/' . ltrim($imgPath, '/');
                    $stock   = (int)$p['stock'];
                    $isOOS   = $stock === 0;
                    $isLow   = $stock > 0 && $stock <= 5;
                    $isNew   = strtotime($p['created_at']) > strtotime('-30 days');
                    $rating  = round((float)$p['avg_rating']);
                    $stars   = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                ?>
                <div class="product-card <?php echo $isOOS ? 'out-of-stock' : ''; ?>">

                    <!-- Badges -->
                    <div class="card-badges">
                        <?php if ($isNew && !$isOOS): ?><span class="badge-new">NEW</span><?php endif; ?>
                        <?php if ($isOOS): ?><span class="badge-oos">OUT OF STOCK</span><?php endif; ?>
                        <?php if ($isLow && !$isOOS): ?><span class="badge-low">ONLY <?php echo $stock; ?> LEFT</span><?php endif; ?>
                    </div>

                    <!-- Image + hover overlay -->
                    <div class="card-img-wrap">
                        <a href="<?php echo url('product.php?id=' . $p['id']); ?>">
                            <img src="<?php echo e($imgPath); ?>"
                                 alt="<?php echo e($p['name']); ?>"
                                 loading="lazy">
                        </a>
                        <a href="<?php echo url('product.php?id=' . $p['id']); ?>" class="card-overlay">
                            <span class="overlay-btn">Quick View</span>
                        </a>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <div class="card-cat"><?php echo e($p['category']); ?></div>
                        <div class="card-title"><?php echo e($p['name']); ?></div>

                        <!-- Star rating -->
                        <div class="card-stars">
                            <span class="stars"><?php echo $stars; ?></span>
                            <span class="stars-count">(<?php echo (int)$p['review_count']; ?>)</span>
                        </div>

                        <!-- Description (list view only) -->
                        <div class="card-desc"><?php echo e(substr($p['description'] ?? '', 0, 100)); ?></div>

                        <div class="card-footer">
                            <span class="card-price">£<?php echo number_format($p['price'], 2); ?></span>

                            <?php if ($isOOS): ?>
                                <span class="btn-cart disabled">Out of Stock</span>
                            <?php elseif (isLoggedIn()): ?>
                                <form action="<?php echo $u; ?>/cart_add.php" method="POST" style="flex:1">
                                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="qty"        value="1">
                                    <button type="submit" class="btn-cart w-100">🛒 Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <a href="<?php echo $u; ?>/login.php" class="btn-login-buy">Login to Buy</a>
                            <?php endif; ?>

                            <?php if (isLoggedIn() && ($p['posted_by'] == getCurrentUserId() || isAdmin())): ?>
                                <a href="<?php echo $u; ?>/delete_product.php?id=<?php echo $p['id']; ?>"
                                   class="btn-delete" onclick="return confirm('Delete this product?');" title="Delete">🗑</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination-wrap">
                <?php
                $prevUrl = $page > 1 ? buildUrl(['page' => $page - 1]) : null;
                $nextUrl = $page < $totalPages ? buildUrl(['page' => $page + 1]) : null;
                ?>
                <a href="<?php echo $prevUrl ? $u.'/'.$prevUrl : '#'; ?>" class="page-btn <?php echo !$prevUrl ? 'disabled' : ''; ?>">‹ Prev</a>

                <?php
                $start = max(1, $page - 2);
                $end   = min($totalPages, $page + 2);
                if ($start > 1): ?>
                    <a href="<?php echo $u.'/'.buildUrl(['page'=>1]); ?>" class="page-btn">1</a>
                    <?php if ($start > 2): ?><span class="page-btn disabled">…</span><?php endif; ?>
                <?php endif;
                for ($i = $start; $i <= $end; $i++): ?>
                    <a href="<?php echo $u.'/'.buildUrl(['page'=>$i]); ?>" class="page-btn <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor;
                if ($end < $totalPages): ?>
                    <?php if ($end < $totalPages - 1): ?><span class="page-btn disabled">…</span><?php endif; ?>
                    <a href="<?php echo $u.'/'.buildUrl(['page'=>$totalPages]); ?>" class="page-btn"><?php echo $totalPages; ?></a>
                <?php endif; ?>

                <a href="<?php echo $nextUrl ? $u.'/'.$nextUrl : '#'; ?>" class="page-btn <?php echo !$nextUrl ? 'disabled' : ''; ?>">Next ›</a>
            </div>
            <p class="text-center text-muted mt-2" style="font-size:.8rem;">
                Page <?php echo $page; ?> of <?php echo $totalPages; ?> &mdash; <?php echo number_format($totalProducts); ?> products total
            </p>
            <?php endif; ?>

            <?php endif; ?>
        </div><!-- /products-main -->
    </div><!-- /shop-layout -->
</main>

<?php include 'includes/footer.php'; ?>

<script>
// ── View toggle (grid / list) ─────────────────────────────────────────────
function setView(mode) {
    const grid = document.getElementById('productsGrid');
    if (!grid) return;
    if (mode === 'list') {
        grid.classList.add('list-view');
        document.getElementById('btnList').classList.add('active');
        document.getElementById('btnGrid').classList.remove('active');
    } else {
        grid.classList.remove('list-view');
        document.getElementById('btnGrid').classList.add('active');
        document.getElementById('btnList').classList.remove('active');
    }
    localStorage.setItem('productView', mode);
}
// Restore saved view preference
const savedView = localStorage.getItem('productView');
if (savedView) setView(savedView);

// ── Sort dropdown ─────────────────────────────────────────────────────────
function changeSortUrl(sort) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sort);
    url.searchParams.delete('page');
    window.location = url.toString();
}

// ── In-stock checkbox ─────────────────────────────────────────────────────
var inStockEl = document.getElementById('inStockCheck');
if (inStockEl) {
    inStockEl.addEventListener('change', function () {
        const url = new URL(window.location.href);
        if (this.checked) url.searchParams.set('in_stock', '1');
        else              url.searchParams.delete('in_stock');
        url.searchParams.delete('page');
        window.location = url.toString();
    });
}
</script>
</body>
</html>
