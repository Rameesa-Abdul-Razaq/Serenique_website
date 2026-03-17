-- =====================================================
-- SERENIQUE MARKETPLACE - COMPLETE DATABASE SETUP
-- Database : u_240364436_db
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS products_stock;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- USERS TABLE
-- =====================================================
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50)  NOT NULL UNIQUE,
    email           VARCHAR(100) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('admin','user') NOT NULL DEFAULT 'user',
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    last_login_at   TIMESTAMP    NULL DEFAULT NULL,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email    (email),
    INDEX idx_username (username),
    INDEX idx_role     (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PRODUCTS TABLE
-- =====================================================
CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255)    NOT NULL,
    price       DECIMAL(10,2)   NOT NULL,
    description TEXT,
    category    VARCHAR(100),
    image       VARCHAR(255)    DEFAULT 'assets/images/products/default.svg',
    stock       INT             NOT NULL DEFAULT 50,
    is_active   TINYINT(1)      NOT NULL DEFAULT 1,
    is_featured TINYINT(1)      NOT NULL DEFAULT 0,
    posted_by   INT             NOT NULL,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category  (category),
    INDEX idx_posted_by (posted_by),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CART TABLE
-- =====================================================
CREATE TABLE cart (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    product_id INT NOT NULL,
    qty        INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user_id    (user_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- REVIEWS TABLE
-- =====================================================
CREATE TABLE reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT  NOT NULL,
    user_id    INT  NOT NULL,
    rating     INT  NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review     TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_user_id    (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ORDERS TABLE
-- =====================================================
CREATE TABLE orders (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT           NOT NULL,
    order_number     VARCHAR(20)   NOT NULL UNIQUE,
    subtotal         DECIMAL(10,2) NOT NULL,
    discount         DECIMAL(10,2) DEFAULT 0,
    total            DECIMAL(10,2) NOT NULL,
    status           ENUM('pending','processing','shipped','completed','cancelled') DEFAULT 'completed',
    shipping_address TEXT,
    email            VARCHAR(100),
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id     (user_id),
    INDEX idx_order_number(order_number),
    INDEX idx_status      (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ORDER ITEMS TABLE
-- =====================================================
CREATE TABLE order_items (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    order_id     INT          NOT NULL,
    product_id   INT,
    product_name VARCHAR(255) NOT NULL,
    price        DECIMAL(10,2) NOT NULL,
    qty          INT           NOT NULL DEFAULT 1,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order_id (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DEFAULT ADMIN USER
-- Email: admin@site.com  |  Password: Admin123
-- =====================================================
INSERT INTO users (username, email, password_hash, role, is_active) VALUES
('admin', 'admin@site.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'admin', 1);

-- =====================================================
-- SAMPLE PRODUCTS (100 products)
-- stock values mirror the Flask in-memory seed pattern
-- =====================================================

-- SKINCARE (20)
INSERT INTO products (name, price, description, category, image, stock, posted_by) VALUES
('Rose Hydrating Serum',           39.99, 'A lightweight serum infused with organic rose hip oil and hyaluronic acid for intense hydration.',       'Skincare', 'assets/images/products/default.svg', 42,  1),
('Vitamin C Brightening Serum',    44.99, 'Powerful antioxidant serum with 20% Vitamin C to brighten and even skin tone.',                          'Skincare', 'assets/images/products/default.svg', 7,   1),
('Retinol Night Cream',            54.99, 'Advanced anti-aging cream with encapsulated retinol for smoother, younger-looking skin.',                 'Skincare', 'assets/images/products/default.svg', 150, 1),
('Gentle Foaming Cleanser',        24.99, 'pH-balanced cleanser that removes impurities without stripping natural oils.',                            'Skincare', 'assets/images/products/default.svg', 3,   1),
('Rose Cleansing Balm',            29.99, 'Luxurious balm that melts away makeup while nourishing skin with rose extracts.',                         'Skincare', 'assets/images/products/default.svg', 95,  1),
('Hyaluronic Acid Moisturizer',    36.99, 'Deep hydration moisturizer with triple-weight hyaluronic acid complex.',                                  'Skincare', 'assets/images/products/default.svg', 28,  1),
('Niacinamide Pore Minimizer',     32.99, 'Lightweight serum with 10% niacinamide to refine pores and control oil.',                                'Skincare', 'assets/images/products/default.svg', 0,   1),
('Collagen Boosting Cream',        48.99, 'Rich cream with peptides and collagen to improve skin elasticity.',                                       'Skincare', 'assets/images/products/default.svg', 63,  1),
('Green Tea Antioxidant Toner',    22.99, 'Refreshing toner packed with green tea extracts to protect against free radicals.',                       'Skincare', 'assets/images/products/default.svg', 11,  1),
('Salicylic Acid Spot Treatment',  19.99, 'Targeted treatment with 2% salicylic acid to clear blemishes fast.',                                      'Skincare', 'assets/images/products/default.svg', 200, 1),
('Ceramide Barrier Repair Cream',  42.99, 'Intensive cream that restores and strengthens the skin barrier.',                                         'Skincare', 'assets/images/products/default.svg', 5,   1),
('AHA BHA Exfoliating Serum',      34.99, 'Chemical exfoliant with glycolic and salicylic acid for radiant skin.',                                   'Skincare', 'assets/images/products/default.svg', 80,  1),
('Squalane Facial Oil',            28.99, 'Lightweight oil that mimics natural sebum for balanced hydration.',                                       'Skincare', 'assets/images/products/default.svg', 34,  1),
('Bakuchiol Anti-Aging Serum',     46.99, 'Plant-based retinol alternative for sensitive skin types.',                                               'Skincare', 'assets/images/products/default.svg', 120, 1),
('Centella Calming Gel',           26.99, 'Soothing gel with cica extract to calm irritated and sensitive skin.',                                    'Skincare', 'assets/images/products/default.svg', 2,   1),
('Peptide Eye Cream',              38.99, 'Targeted eye treatment to reduce dark circles and fine lines.',                                           'Skincare', 'assets/images/products/default.svg', 55,  1),
('Mineral Sunscreen SPF 50',       32.99, 'Broad spectrum protection with zinc oxide, no white cast formula.',                                       'Skincare', 'assets/images/products/default.svg', 18,  1),
('Overnight Recovery Mask',        44.99, 'Intensive sleeping mask that repairs and rejuvenates while you sleep.',                                   'Skincare', 'assets/images/products/default.svg', 99,  1),
('Enzyme Exfoliating Powder',      29.99, 'Gentle powder cleanser with papaya enzymes for smooth skin.',                                             'Skincare', 'assets/images/products/default.svg', 73,  1),
('Probiotic Skin Balance Serum',   41.99, 'Microbiome-friendly serum to strengthen skin health.',                                                    'Skincare', 'assets/images/products/default.svg', 41,  1),

-- MAKEUP (18)
('Sheer Matte Lipstick',           18.99, 'Long-lasting matte lipstick with a comfortable, non-drying formula.',            'Makeup', 'assets/images/products/default.svg', 42,  1),
('Velvet Lip Gloss',               16.99, 'High-shine gloss with plumping peptides for fuller-looking lips.',               'Makeup', 'assets/images/products/default.svg', 7,   1),
('Silk Foundation SPF 15',         38.99, 'Buildable coverage foundation with natural satin finish.',                       'Makeup', 'assets/images/products/default.svg', 150, 1),
('Creamy Concealer Stick',         22.99, 'Full coverage concealer that blends seamlessly and lasts all day.',              'Makeup', 'assets/images/products/default.svg', 3,   1),
('Baked Highlighter',              28.99, 'Luminous highlighter for a natural lit-from-within glow.',                       'Makeup', 'assets/images/products/default.svg', 95,  1),
('Contour Palette',                34.99, 'Professional sculpting palette with matte and shimmer shades.',                  'Makeup', 'assets/images/products/default.svg', 28,  1),
('Volumizing Mascara',             19.99, 'Dramatic volume and length without clumping or flaking.',                        'Makeup', 'assets/images/products/default.svg', 0,   1),
('Waterproof Eyeliner Pen',        17.99, 'Precision tip liner that stays put through tears and sweat.',                    'Makeup', 'assets/images/products/default.svg', 63,  1),
('Eyeshadow Palette Nude',         42.99, '12 versatile shades from matte to shimmer for everyday looks.',                  'Makeup', 'assets/images/products/default.svg', 11,  1),
('Eyeshadow Palette Sunset',       42.99, 'Warm-toned palette with rich oranges, reds, and golds.',                        'Makeup', 'assets/images/products/default.svg', 200, 1),
('Setting Powder Translucent',     26.99, 'Finely milled powder that sets makeup without adding coverage.',                 'Makeup', 'assets/images/products/default.svg', 5,   1),
('Cream Blush Stick',              23.99, 'Buildable cream blush for a natural, dewy flush of color.',                     'Makeup', 'assets/images/products/default.svg', 80,  1),
('Brow Pomade',                    21.99, 'Long-wearing pomade for defined, natural-looking brows.',                        'Makeup', 'assets/images/products/default.svg', 34,  1),
('Lip Liner Pencil Set',           24.99, 'Set of 6 versatile lip liners for precise definition.',                          'Makeup', 'assets/images/products/default.svg', 120, 1),
('Setting Spray Matte Finish',     18.99, 'All-day makeup lock with a shine-free matte finish.',                            'Makeup', 'assets/images/products/default.svg', 2,   1),
('Primer Pore Blurring',           29.99, 'Silky primer that minimizes pores and smooths texture.',                         'Makeup', 'assets/images/products/default.svg', 55,  1),
('Bronzer Duo',                    31.99, 'Matte and shimmer bronzer duo for sun-kissed dimension.',                        'Makeup', 'assets/images/products/default.svg', 18,  1),
('Lash Curler Deluxe',             14.99, 'Professional-grade lash curler for dramatic curl.',                              'Makeup', 'assets/images/products/default.svg', 99,  1),

-- HAIRCARE (15)
('Argan Oil Shampoo',              24.99, 'Nourishing shampoo infused with pure argan oil for silky smooth hair.',          'Haircare', 'assets/images/products/default.svg', 73,  1),
('Keratin Repair Conditioner',     26.99, 'Deep conditioning treatment with keratin to repair damaged hair.',               'Haircare', 'assets/images/products/default.svg', 41,  1),
('Hair Growth Serum',              49.99, 'Stimulating serum with biotin and caffeine to promote hair growth.',             'Haircare', 'assets/images/products/default.svg', 42,  1),
('Coconut Hair Mask',              32.99, 'Intensive hydrating mask with virgin coconut oil.',                              'Haircare', 'assets/images/products/default.svg', 7,   1),
('Heat Protection Spray',          19.99, 'Lightweight spray that shields hair from heat damage up to 450°F.',              'Haircare', 'assets/images/products/default.svg', 150, 1),
('Dry Shampoo Volume',             16.99, 'Refreshes hair between washes while adding body and texture.',                   'Haircare', 'assets/images/products/default.svg', 3,   1),
('Silk Hair Oil',                  34.99, 'Luxurious finishing oil for frizz control and brilliant shine.',                 'Haircare', 'assets/images/products/default.svg', 95,  1),
('Scalp Treatment Serum',          38.99, 'Targeted treatment for dry, itchy scalp with tea tree oil.',                    'Haircare', 'assets/images/products/default.svg', 28,  1),
('Purple Shampoo Blonde',          22.99, 'Color-correcting shampoo to eliminate brassiness in blonde hair.',               'Haircare', 'assets/images/products/default.svg', 0,   1),
('Curl Defining Cream',            27.99, 'Enhances and defines natural curls without crunchiness.',                        'Haircare', 'assets/images/products/default.svg', 63,  1),
('Leave-In Conditioner',           21.99, 'Lightweight detangling spray for everyday moisture.',                            'Haircare', 'assets/images/products/default.svg', 11,  1),
('Hair Thickening Spray',          28.99, 'Volumizing spray for fuller, thicker-looking hair.',                             'Haircare', 'assets/images/products/default.svg', 200, 1),
('Bond Repair Treatment',          44.99, 'Professional-grade treatment to rebuild broken hair bonds.',                     'Haircare', 'assets/images/products/default.svg', 5,   1),
('Texture Spray Sea Salt',         18.99, 'Creates beachy waves and effortless texture.',                                   'Haircare', 'assets/images/products/default.svg', 80,  1),
('Anti-Dandruff Shampoo',          23.99, 'Gentle formula with zinc pyrithione to control flakes.',                         'Haircare', 'assets/images/products/default.svg', 34,  1),

-- TOOLS (12)
('Rose Quartz Face Roller',        34.99, 'Natural rose quartz roller to reduce puffiness and promote circulation.',        'Tools', 'assets/images/products/default.svg', 120, 1),
('Jade Gua Sha Set',               29.99, 'Traditional jade gua sha tool for facial sculpting and lymphatic drainage.',     'Tools', 'assets/images/products/default.svg', 2,   1),
('Professional Brush Set 12pc',    54.99, 'Complete set of 12 professional makeup brushes with case.',                     'Tools', 'assets/images/products/default.svg', 55,  1),
('Beauty Blender Set',             19.99, 'Set of 3 premium makeup sponges for flawless application.',                     'Tools', 'assets/images/products/default.svg', 18,  1),
('LED Face Mask',                 149.99, 'At-home LED therapy mask for anti-aging and acne treatment.',                   'Tools', 'assets/images/products/default.svg', 99,  1),
('Facial Steamer',                 44.99, 'Nano ionic facial steamer for deep pore cleansing.',                             'Tools', 'assets/images/products/default.svg', 73,  1),
('Dermaplaning Tool Set',          24.99, 'Professional dermaplaning tools for smooth, peach-fuzz free skin.',              'Tools', 'assets/images/products/default.svg', 41,  1),
('Ice Roller',                     16.99, 'Stainless steel ice roller to depuff and soothe skin.',                         'Tools', 'assets/images/products/default.svg', 42,  1),
('Silicone Face Scrubber',         12.99, 'Gentle silicone brush for deep cleansing and exfoliation.',                     'Tools', 'assets/images/products/default.svg', 7,   1),
('Eyelash Curler',                 14.99, 'Professional lash curler with silicone pad for perfect curl.',                  'Tools', 'assets/images/products/default.svg', 150, 1),
('Makeup Mirror LED',              39.99, 'Light-up vanity mirror with 10x magnification.',                                'Tools', 'assets/images/products/default.svg', 3,   1),
('Micro-Needling Roller',          27.99, 'At-home derma roller to improve product absorption.',                            'Tools', 'assets/images/products/default.svg', 95,  1),

-- FRAGRANCE (15)
('Rose Garden Eau de Parfum',      79.99, 'Elegant floral fragrance with Bulgarian rose and white musk.',                   'Fragrance', 'assets/images/products/default.svg', 28,  1),
('Ocean Breeze Body Mist',         24.99, 'Light, refreshing body spray with marine and citrus notes.',                     'Fragrance', 'assets/images/products/default.svg', 0,   1),
('Vanilla Dreams Perfume Oil',     34.99, 'Warm vanilla perfume oil with hints of sandalwood.',                             'Fragrance', 'assets/images/products/default.svg', 63,  1),
('Lavender Fields EDT',            49.99, 'Calming eau de toilette with French lavender essence.',                          'Fragrance', 'assets/images/products/default.svg', 11,  1),
('Citrus Burst Cologne',           44.99, 'Energizing citrus blend with bergamot and grapefruit.',                          'Fragrance', 'assets/images/products/default.svg', 200, 1),
('Midnight Jasmine EDP',           89.99, 'Sensual evening fragrance with jasmine and amber.',                              'Fragrance', 'assets/images/products/default.svg', 5,   1),
('Fresh Cotton Body Spray',        19.99, 'Clean, fresh scent reminiscent of line-dried linens.',                           'Fragrance', 'assets/images/products/default.svg', 80,  1),
('Sandalwood Meditation',          64.99, 'Grounding fragrance with Indian sandalwood and vetiver.',                        'Fragrance', 'assets/images/products/default.svg', 34,  1),
('Cherry Blossom Mist',            22.99, 'Delicate spring fragrance with Japanese cherry blossom.',                        'Fragrance', 'assets/images/products/default.svg', 120, 1),
('Oud Luxe Parfum',               129.99, 'Premium oud fragrance with rose and saffron accords.',                           'Fragrance', 'assets/images/products/default.svg', 2,   1),
('Green Tea Cologne',              39.99, 'Light, refreshing scent with green tea and mint.',                               'Fragrance', 'assets/images/products/default.svg', 55,  1),
('Peony Blush EDP',                74.99, 'Romantic floral with peony, rose, and white peach.',                             'Fragrance', 'assets/images/products/default.svg', 18,  1),
('Amber Nights Perfume',           84.99, 'Warm oriental fragrance with amber and vanilla.',                                'Fragrance', 'assets/images/products/default.svg', 99,  1),
('Coconut Paradise Mist',          21.99, 'Tropical body mist with coconut and frangipani.',                                'Fragrance', 'assets/images/products/default.svg', 73,  1),
('White Musk Body Spray',          18.99, 'Clean, subtle musk for everyday wear.',                                          'Fragrance', 'assets/images/products/default.svg', 41,  1),

-- BATH & BODY (12)
('Shea Butter Body Lotion',        26.99, 'Rich moisturizing lotion with pure African shea butter.',                        'Bath & Body', 'assets/images/products/default.svg', 42,  1),
('Coffee Body Scrub',              24.99, 'Energizing exfoliant with Arabica coffee grounds.',                              'Bath & Body', 'assets/images/products/default.svg', 7,   1),
('Lavender Bath Bombs Set',        19.99, 'Set of 6 relaxing lavender bath bombs with essential oils.',                     'Bath & Body', 'assets/images/products/default.svg', 150, 1),
('Coconut Body Butter',            29.99, 'Intensive moisture with virgin coconut and cocoa butter.',                       'Bath & Body', 'assets/images/products/default.svg', 3,   1),
('Rose Petal Body Wash',           18.99, 'Gentle cleansing gel infused with rose petals.',                                 'Bath & Body', 'assets/images/products/default.svg', 95,  1),
('Dead Sea Salt Soak',             22.99, 'Mineral-rich bath soak for muscle relaxation.',                                  'Bath & Body', 'assets/images/products/default.svg', 28,  1),
('Almond Body Oil',                27.99, 'Nourishing sweet almond oil for silky soft skin.',                               'Bath & Body', 'assets/images/products/default.svg', 0,   1),
('Sugar Lip Scrub',                14.99, 'Gentle exfoliating scrub for smooth, soft lips.',                                'Bath & Body', 'assets/images/products/default.svg', 63,  1),
('Hand Cream Trio',                24.99, 'Set of 3 intensely moisturizing hand creams.',                                   'Bath & Body', 'assets/images/products/default.svg', 11,  1),
('Foot Repair Balm',               21.99, 'Intensive treatment for dry, cracked heels.',                                    'Bath & Body', 'assets/images/products/default.svg', 200, 1),
('Bubble Bath Luxury',             23.99, 'Indulgent bubble bath with jasmine and honey.',                                  'Bath & Body', 'assets/images/products/default.svg', 5,   1),
('Body Shimmer Oil',               32.99, 'Luminous body oil with subtle golden shimmer.',                                  'Bath & Body', 'assets/images/products/default.svg', 80,  1),

-- WELLNESS (8)
('Aromatherapy Diffuser',          44.99, 'Ultrasonic essential oil diffuser with color-changing LED.',     'Wellness', 'assets/images/products/default.svg', 34,  1),
('Lavender Essential Oil',         18.99, 'Pure lavender essential oil for relaxation and sleep.',          'Wellness', 'assets/images/products/default.svg', 120, 1),
('Eucalyptus Essential Oil',       16.99, 'Refreshing eucalyptus oil for respiratory wellness.',            'Wellness', 'assets/images/products/default.svg', 2,   1),
('Peppermint Essential Oil',       15.99, 'Invigorating peppermint oil for energy and focus.',              'Wellness', 'assets/images/products/default.svg', 55,  1),
('Sleep Well Pillow Mist',         19.99, 'Calming pillow spray with lavender and chamomile.',              'Wellness', 'assets/images/products/default.svg', 18,  1),
('Stress Relief Roll-On',          14.99, 'Portable aromatherapy blend for on-the-go calm.',               'Wellness', 'assets/images/products/default.svg', 99,  1),
('Meditation Candle Set',          34.99, 'Set of 3 soy candles with calming scents.',                     'Wellness', 'assets/images/products/default.svg', 73,  1),
('Essential Oil Gift Set',         49.99, 'Collection of 6 popular essential oils with carrier oil.',      'Wellness', 'assets/images/products/default.svg', 41,  1);

-- =====================================================
-- SETUP COMPLETE
-- Admin login: admin@site.com / Admin123
-- =====================================================
