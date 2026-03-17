<?php
/**
 * Homepage
 */
require_once __DIR__ . '/session.php';
$u = SITE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Serenique | Home</title>
    <link rel="stylesheet" href="<?php echo $u; ?>/frontend/home.css">
    <script src="<?php echo $u; ?>/frontend/home.js"></script>
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <main>
        <div class="background-image d-flex justify-content-center align-items-center text-center">
            <img src="<?php echo $u; ?>/frontend/images/aboutUs.jpeg" alt="Beauty products flatlay" class="bg-img-file" />
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h2 class="display-4 text-white fw-bold hero-title-shadow">Natural Glow, Simplified.</h2>
                <p class="text-white lead mb-4 hero-text-shadow">Premium organic skincare for sensitive skin.</p>
                <a href="<?php echo url('products.php'); ?>" class="btn btn-light btn-lg rounded-pill px-4 hero-cta-btn">Shop New Arrivals</a>
            </div>
        </div>

        <br class="d-none d-lg-block"><br class="d-none d-lg-block">


  
  <section class="promo-banner-section my-5">
      <div id="promoCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          
          <div class="carousel-item active promo-slide promo-holiday-theme">
            <div class="container d-flex align-items-center promo-content-wrapper">
              <div class="row align-items-center w-100">
                <div class="col-lg-6 promo-text-container">
                  <h3 class="promo-headline">A Very Serenique Winter</h3>
                  <p class="promo-description">Give the gift of glow with our exclusive Winter Solstice set. Featuring our best-selling face oil and signature amber cream. Limited stock!</p>
                  <button class="btn btn-dark rounded-0 px-4 py-2 promo-cta-btn">SHOP COLLECTION</button>
                </div>
                <div class="col-lg-6 d-none d-lg-block text-end">
                    <img src="uploads/winter_promo.png" alt="Winter Solstice Promo Set" class="img-fluid promo-product-image">
                </div>
              </div>
            </div>
          </div>
          
          <div class="carousel-item promo-slide promo-sale-theme">
             <div class="container d-flex align-items-center promo-content-wrapper">
                <div class="row align-items-center w-100">
                    <div class="col-lg-6 promo-text-container">
                        <h3 class="promo-headline">30% OFF Summer Essentials</h3>
                        <p class="promo-description">Protect and hydrate your skin all season long. Use code **SUMMERGLOW** at checkout for instant savings.</p>
                        <button class="btn btn-dark rounded-0 px-4 py-2 promo-cta-btn">VIEW SALE</button>
                    </div>
                    <div class="col-lg-6 d-none d-lg-block text-end">
                        <img src="uploads/summer_promo.png" alt="Summer Sale Products" class="img-fluid promo-product-image">
                    </div>
                </div>
            </div>
          </div>
          
        </div>
        
        <button class="carousel-control-prev promo-control" type="button" data-bs-target="#promoCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next promo-control" type="button" data-bs-target="#promoCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
        
      </div>
    </section>


        <section class="container my-5">
            <h2 class="text-center mb-5 section-title">Top Categories</h2>
            <div class="row g-4 justify-content-center">
                <div class="col-md-4 col-sm-6">
                    <a href="<?php echo url('products.php?category=Skincare'); ?>" class="category-link">
                        <div class="category-card text-center">
                            <div class="img-wrapper mb-3">
                                <img src="<?php echo $u; ?>/frontend/images/skincare.png" alt="Skincare" class="img-fluid rounded-circle shadow-sm">
                            </div>
                            <h4 class="fw-semibold">Skincare</h4>
                            <span class="btn btn-link text-dark text-decoration-none">Shop Now →</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 col-sm-6">
                    <a href="<?php echo url('products.php?category=Makeup'); ?>" class="category-link">
                        <div class="category-card text-center">
                            <div class="img-wrapper mb-3">
                                <img src="<?php echo $u; ?>/frontend/images/makeup.png" alt="Makeup" class="img-fluid rounded-circle shadow-sm">
                            </div>
                            <h4 class="fw-semibold">Makeup</h4>
                            <span class="btn btn-link text-dark text-decoration-none">Shop Now →</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 col-sm-6">
                    <a href="<?php echo url('products.php?category=Haircare'); ?>" class="category-link">
                        <div class="category-card text-center">
                            <div class="img-wrapper mb-3">
                                <img src="<?php echo $u; ?>/frontend/images/skincare.png" alt="Haircare" class="img-fluid rounded-circle shadow-sm">
                            </div>
                            <h4 class="fw-semibold">Haircare</h4>
                            <span class="btn btn-link text-dark text-decoration-none">Shop Now →</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 col-sm-6">
                    <a href="<?php echo url('products.php?category=Fragrance'); ?>" class="category-link">
                        <div class="category-card text-center">
                            <div class="img-wrapper mb-3">
                                <img src="<?php echo $u; ?>/frontend/images/makeup.png" alt="Fragrance" class="img-fluid rounded-circle shadow-sm">
                            </div>
                            <h4 class="fw-semibold">Fragrance</h4>
                            <span class="btn btn-link text-dark text-decoration-none">Shop Now →</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 col-sm-6">
                    <a href="<?php echo url('products.php?category=Tools'); ?>" class="category-link">
                        <div class="category-card text-center">
                            <div class="img-wrapper mb-3">
                                <img src="<?php echo $u; ?>/frontend/images/tools.png" alt="Tools" class="img-fluid rounded-circle shadow-sm">
                            </div>
                            <h4 class="fw-semibold">Tools</h4>
                            <span class="btn btn-link text-dark text-decoration-none">Shop Now →</span>
                        </div>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <br><br>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
