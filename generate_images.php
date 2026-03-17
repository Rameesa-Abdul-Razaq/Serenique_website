<?php
/**
 * Image Generator Script
 * Run once to generate all product and category placeholder images
 * Access: http://localhost/public_html/generate_images.php
 */

// Category colors
$categoryColors = [
    'Skincare' => ['bg' => '#FDF2F4', 'fg' => '#E8B4B8', 'text' => '#8B4557'],
    'Makeup' => ['bg' => '#FDF5F0', 'fg' => '#D27B5A', 'text' => '#8B4A2B'],
    'Tools' => ['bg' => '#F5F0EB', 'fg' => '#8B7355', 'text' => '#5C4A38'],
    'Haircare' => ['bg' => '#F5F0FF', 'fg' => '#9B7EDE', 'text' => '#5A4A8B'],
    'Fragrance' => ['bg' => '#FFF8F0', 'fg' => '#F5C6AA', 'text' => '#8B6B4A'],
    'Bath & Body' => ['bg' => '#F0F8FF', 'fg' => '#87CEEB', 'text' => '#4A6A8B'],
    'Wellness' => ['bg' => '#F0FFF5', 'fg' => '#98D8AA', 'text' => '#4A8B5A'],
];

// All 100 products with their categories
$products = [
    // Skincare (20)
    ['rose-hydrating-serum', 'Skincare'],
    ['vitamin-c-brightening-serum', 'Skincare'],
    ['retinol-night-cream', 'Skincare'],
    ['gentle-foaming-cleanser', 'Skincare'],
    ['rose-cleansing-balm', 'Skincare'],
    ['hyaluronic-acid-moisturizer', 'Skincare'],
    ['niacinamide-pore-minimizer', 'Skincare'],
    ['collagen-boosting-cream', 'Skincare'],
    ['green-tea-antioxidant-toner', 'Skincare'],
    ['salicylic-acid-spot-treatment', 'Skincare'],
    ['ceramide-barrier-repair-cream', 'Skincare'],
    ['aha-bha-exfoliating-serum', 'Skincare'],
    ['squalane-facial-oil', 'Skincare'],
    ['bakuchiol-anti-aging-serum', 'Skincare'],
    ['centella-calming-gel', 'Skincare'],
    ['peptide-eye-cream', 'Skincare'],
    ['mineral-sunscreen-spf50', 'Skincare'],
    ['overnight-recovery-mask', 'Skincare'],
    ['enzyme-exfoliating-powder', 'Skincare'],
    ['probiotic-skin-balance-serum', 'Skincare'],
    
    // Makeup (18)
    ['sheer-matte-lipstick', 'Makeup'],
    ['velvet-lip-gloss', 'Makeup'],
    ['silk-foundation-spf15', 'Makeup'],
    ['creamy-concealer-stick', 'Makeup'],
    ['baked-highlighter', 'Makeup'],
    ['contour-palette', 'Makeup'],
    ['volumizing-mascara', 'Makeup'],
    ['waterproof-eyeliner-pen', 'Makeup'],
    ['eyeshadow-palette-nude', 'Makeup'],
    ['eyeshadow-palette-sunset', 'Makeup'],
    ['setting-powder-translucent', 'Makeup'],
    ['cream-blush-stick', 'Makeup'],
    ['brow-pomade', 'Makeup'],
    ['lip-liner-pencil-set', 'Makeup'],
    ['setting-spray-matte-finish', 'Makeup'],
    ['primer-pore-blurring', 'Makeup'],
    ['bronzer-duo', 'Makeup'],
    ['lash-curling-primer', 'Makeup'],
    
    // Tools (14)
    ['jade-face-roller', 'Tools'],
    ['gua-sha-rose-quartz', 'Tools'],
    ['professional-brush-set-12pc', 'Tools'],
    ['beauty-blender-duo', 'Tools'],
    ['eyelash-curler-gold', 'Tools'],
    ['facial-cleansing-brush', 'Tools'],
    ['derma-roller-05mm', 'Tools'],
    ['led-light-therapy-mask', 'Tools'],
    ['ice-roller-cryotherapy', 'Tools'],
    ['tweezer-set-precision', 'Tools'],
    ['silicone-face-scrubber', 'Tools'],
    ['makeup-mirror-led-10x', 'Tools'],
    ['brush-cleaning-mat', 'Tools'],
    ['travel-cosmetic-bag', 'Tools'],
    
    // Haircare (16)
    ['argan-oil-shampoo', 'Haircare'],
    ['argan-oil-conditioner', 'Haircare'],
    ['keratin-hair-mask', 'Haircare'],
    ['scalp-detox-scrub', 'Haircare'],
    ['heat-protectant-spray', 'Haircare'],
    ['biotin-hair-growth-serum', 'Haircare'],
    ['dry-shampoo-volume-boost', 'Haircare'],
    ['leave-in-conditioner-spray', 'Haircare'],
    ['hair-oil-treatment', 'Haircare'],
    ['anti-frizz-smoothing-cream', 'Haircare'],
    ['purple-toning-shampoo', 'Haircare'],
    ['curl-defining-cream', 'Haircare'],
    ['bond-repair-treatment', 'Haircare'],
    ['volumizing-mousse', 'Haircare'],
    ['silk-pillowcase-hair', 'Haircare'],
    ['bamboo-hair-brush', 'Haircare'],
    
    // Fragrance (12)
    ['eau-de-parfum-rose-garden', 'Fragrance'],
    ['eau-de-toilette-fresh-citrus', 'Fragrance'],
    ['perfume-oil-vanilla-dreams', 'Fragrance'],
    ['body-mist-ocean-breeze', 'Fragrance'],
    ['solid-perfume-compact', 'Fragrance'],
    ['eau-de-parfum-midnight-oud', 'Fragrance'],
    ['hair-perfume-mist', 'Fragrance'],
    ['perfume-discovery-set', 'Fragrance'],
    ['room-spray-lavender-calm', 'Fragrance'],
    ['scented-candle-jasmine', 'Fragrance'],
    ['eau-de-parfum-cherry-blossom', 'Fragrance'],
    ['cologne-sport-fresh', 'Fragrance'],
    
    // Bath & Body (12)
    ['shea-butter-body-lotion', 'Bath & Body'],
    ['exfoliating-body-scrub-coffee', 'Bath & Body'],
    ['bath-bomb-set-6pc', 'Bath & Body'],
    ['coconut-body-oil', 'Bath & Body'],
    ['hand-cream-duo-gift-set', 'Bath & Body'],
    ['bubble-bath-relaxing', 'Bath & Body'],
    ['body-butter-whipped-mango', 'Bath & Body'],
    ['shower-gel-citrus-burst', 'Bath & Body'],
    ['dead-sea-salt-soak', 'Bath & Body'],
    ['foot-cream-peppermint', 'Bath & Body'],
    ['body-mist-peach-nectar', 'Bath & Body'],
    ['exfoliating-gloves-pair', 'Bath & Body'],
    
    // Wellness (8)
    ['aromatherapy-diffuser', 'Wellness'],
    ['essential-oil-set-6pc', 'Wellness'],
    ['sleep-aid-pillow-spray', 'Wellness'],
    ['stress-relief-roll-on', 'Wellness'],
    ['jade-facial-massage-set', 'Wellness'],
    ['meditation-eye-pillow', 'Wellness'],
    ['herbal-bath-tea-bags', 'Wellness'],
    ['acupressure-mat-set', 'Wellness'],
];

// Create directories
$productsDir = __DIR__ . '/assets/images/products/';
$categoriesDir = __DIR__ . '/assets/images/categories/';

if (!is_dir($productsDir)) mkdir($productsDir, 0755, true);
if (!is_dir($categoriesDir)) mkdir($categoriesDir, 0755, true);

// Generate SVG for a product
function generateProductSVG($name, $category, $colors) {
    $displayName = ucwords(str_replace('-', ' ', $name));
    $words = explode(' ', $displayName);
    
    // Split into two lines if too long
    $line1 = '';
    $line2 = '';
    $currentLine = 1;
    foreach ($words as $word) {
        if ($currentLine == 1 && strlen($line1 . ' ' . $word) < 20) {
            $line1 .= ($line1 ? ' ' : '') . $word;
        } else {
            $currentLine = 2;
            $line2 .= ($line2 ? ' ' : '') . $word;
        }
    }
    
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">
  <defs>
    <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:{$colors['bg']};stop-opacity:1" />
      <stop offset="100%" style="stop-color:{$colors['fg']};stop-opacity:0.3" />
    </linearGradient>
  </defs>
  <rect width="400" height="400" fill="url(#grad)"/>
  <circle cx="200" cy="160" r="80" fill="{$colors['fg']}" opacity="0.3"/>
  <circle cx="200" cy="160" r="50" fill="{$colors['fg']}" opacity="0.5"/>
  <text x="200" y="280" font-family="Arial, sans-serif" font-size="18" font-weight="bold" fill="{$colors['text']}" text-anchor="middle">{$line1}</text>
  <text x="200" y="305" font-family="Arial, sans-serif" font-size="18" font-weight="bold" fill="{$colors['text']}" text-anchor="middle">{$line2}</text>
  <text x="200" y="340" font-family="Arial, sans-serif" font-size="12" fill="{$colors['text']}" text-anchor="middle" opacity="0.7">{$category}</text>
  <rect x="150" y="360" width="100" height="4" rx="2" fill="{$colors['fg']}"/>
</svg>
SVG;
    return $svg;
}

// Generate SVG for a category
function generateCategorySVG($name, $colors) {
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
  <defs>
    <linearGradient id="catgrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:{$colors['bg']};stop-opacity:1" />
      <stop offset="100%" style="stop-color:{$colors['fg']};stop-opacity:0.5" />
    </linearGradient>
  </defs>
  <rect width="200" height="200" rx="20" fill="url(#catgrad)"/>
  <circle cx="100" cy="80" r="40" fill="{$colors['fg']}" opacity="0.6"/>
  <circle cx="100" cy="80" r="25" fill="{$colors['fg']}" opacity="0.8"/>
  <text x="100" y="150" font-family="Arial, sans-serif" font-size="16" font-weight="bold" fill="{$colors['text']}" text-anchor="middle">{$name}</text>
  <rect x="60" y="170" width="80" height="3" rx="1.5" fill="{$colors['fg']}"/>
</svg>
SVG;
    return $svg;
}

echo "<h1>🖼️ Serenique Image Generator</h1>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;background:#f5f5f5;} .success{color:green;} .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;margin:20px 0;} .item{background:white;padding:10px;border-radius:8px;text-align:center;font-size:11px;}</style>";

// Generate product images
echo "<h2>📦 Generating Product Images...</h2>";
$productCount = 0;
$productList = "<div class='grid'>";

foreach ($products as $product) {
    $filename = $product[0] . '.svg';
    $category = $product[1];
    $colors = $categoryColors[$category];
    
    $svg = generateProductSVG($product[0], $category, $colors);
    $filepath = $productsDir . $filename;
    
    if (file_put_contents($filepath, $svg)) {
        $productCount++;
        $productList .= "<div class='item'><img src='assets/images/products/{$filename}' width='100' height='100'><br>{$filename}</div>";
    }
}
$productList .= "</div>";

echo "<p class='success'>✅ Generated <strong>{$productCount}/100</strong> product images</p>";
echo $productList;

// Generate category icons
echo "<h2>🏷️ Generating Category Icons...</h2>";
$catCount = 0;
$catList = "<div class='grid'>";

$categories = [
    'skincare' => 'Skincare',
    'makeup' => 'Makeup', 
    'tools' => 'Tools',
    'haircare' => 'Haircare',
    'fragrance' => 'Fragrance',
    'bath-body' => 'Bath & Body',
    'wellness' => 'Wellness',
];

foreach ($categories as $slug => $name) {
    $filename = $slug . '.svg';
    $colors = $categoryColors[$name];
    
    $svg = generateCategorySVG($name, $colors);
    $filepath = $categoriesDir . $filename;
    
    if (file_put_contents($filepath, $svg)) {
        $catCount++;
        $catList .= "<div class='item'><img src='assets/images/categories/{$filename}' width='80' height='80'><br>{$filename}</div>";
    }
}
$catList .= "</div>";

echo "<p class='success'>✅ Generated <strong>{$catCount}/7</strong> category icons</p>";
echo $catList;

// Summary
echo "<h2>📊 Summary</h2>";
echo "<ul>";
echo "<li>Product images: <strong>{$productCount}/100</strong></li>";
echo "<li>Category icons: <strong>{$catCount}/7</strong></li>";
echo "<li>Products directory: <code>assets/images/products/</code></li>";
echo "<li>Categories directory: <code>assets/images/categories/</code></li>";
echo "<li>Categories JSON: <code>data/categories.json</code></li>";
echo "</ul>";

echo "<h2>✅ All Done!</h2>";
echo "<p>You can now <a href='products.php'>view the products page</a>.</p>";
?>

