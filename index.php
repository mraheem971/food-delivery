<?php
require_once __DIR__ . '/data/db.php';
$db = getDB();

// Fetch Categories
$categories = $db->query("SELECT * FROM `categories` ORDER BY `id` ASC")->fetchAll();

// Fetch Featured Restaurants
$restaurants = $db->query("SELECT * FROM `restaurants` ORDER BY `is_featured` DESC, `rating` DESC")->fetchAll();

// Fetch Active Deals for Hero
$deals = $db->query("SELECT * FROM `coupons` LIMIT 3")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <!-- Hero Banner (Foodpanda Style) -->
    <section class="hero-section">
        <div class="hero-banner">
            <div class="hero-content">
                <span class="hero-tag">⚡ Fast 25-Min Delivery</span>
                <h1 class="hero-title">Crave it? Get it delivered hot & fresh.</h1>
                <p class="hero-desc">Explore top-rated gourmet restaurants, artisan pizza makers, smash burgers, and sweet desserts near you.</p>
                <div class="hero-cta-box">
                    <span style="font-weight: 700; font-size: 0.9rem;">Special Voucher:</span>
                    <div class="voucher-chip" data-code="WELCOME50" title="Click to copy code">
                        <span>🎁 50% OFF First Order:</span>
                        <span class="v-code">WELCOME50</span>
                        <span style="font-size: 0.8rem; color: #ff2b85;">(Copy)</span>
                    </div>
                </div>
            </div>
            <div class="hero-img-box">
                <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&auto=format&fit=crop&q=80" alt="Delicious Food">
            </div>
        </div>
    </section>

    <!-- Category Pills Slider -->
    <section class="category-section">
        <div class="section-header">
            <h2 class="section-title">Explore by Cuisines</h2>
        </div>
        <div class="category-slider">
            <button type="button" class="cat-pill active" data-category="all">
                <span class="cat-icon">🍽️</span>
                <span>All Cuisines</span>
            </button>
            <?php foreach ($categories as $cat): ?>
                <button type="button" class="cat-pill" data-category="<?= htmlspecialchars($cat['slug']) ?>">
                    <span class="cat-icon"><?= htmlspecialchars($cat['icon']) ?></span>
                    <span><?= htmlspecialchars($cat['name']) ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Filters & Sort Bar -->
    <div class="filter-bar">
        <div class="filter-chips">
            <button type="button" class="filter-chip" data-filter="free_delivery">🛵 Free Delivery</button>
            <button type="button" class="filter-chip" data-filter="halal">🌙 Halal Certified</button>
            <button type="button" class="filter-chip" data-filter="vegetarian">🥬 Vegetarian Options</button>
            <a href="deals.php" class="filter-chip" style="background: var(--primary-light); color: var(--primary); border-color: #fbcfe8;">🏷️ Active Deals & Vouchers</a>
        </div>

        <div class="sort-select-box">
            <label for="restaurantSortSelect">Sort by:</label>
            <select id="restaurantSortSelect" class="custom-select">
                <option value="recommended">⭐ Top Recommended</option>
                <option value="rating">★ Highest Rating</option>
                <option value="delivery_time">⚡ Fastest Delivery</option>
                <option value="fee_low">🛵 Lowest Delivery Fee</option>
                <option value="min_order">💵 Lowest Min Order</option>
            </select>
        </div>
    </div>

    <!-- Restaurant Grid -->
    <section>
        <div class="section-header">
            <h2 class="section-title" id="gridTitleHeading">Restaurants in Your Area (<?= count($restaurants) ?>)</h2>
        </div>

        <div class="restaurant-grid" id="restaurantGrid">
            <?php foreach ($restaurants as $r): ?>
                <?php 
                    $feeText = (float)$r['delivery_fee'] == 0 ? 'Free Delivery' : '$' . number_format($r['delivery_fee'], 2) . ' Delivery';
                ?>
                <div class="restaurant-card">
                    <div class="rest-img-wrapper" onclick="window.location.href='restaurant.php?id=<?= $r['id'] ?>'" style="cursor: pointer;">
                        <img src="<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['name']) ?>" loading="lazy">
                        <?php if ($r['is_featured']): ?>
                            <span class="badge-tag badge-featured">Featured</span>
                        <?php endif; ?>
                        <?php if ($r['is_free_delivery']): ?>
                            <span class="badge-tag badge-free" style="left: <?= $r['is_featured'] ? '96px' : '12px' ?>;">Free Del</span>
                        <?php endif; ?>
                        <button type="button" class="fav-btn" data-id="<?= $r['id'] ?>" onclick="event.stopPropagation(); toggleFavorite(<?= $r['id'] ?>, this)">❤️</button>
                    </div>
                    <div class="rest-info" onclick="window.location.href='restaurant.php?id=<?= $r['id'] ?>'" style="cursor: pointer;">
                        <div class="rest-header">
                            <h3 class="rest-name"><?= htmlspecialchars($r['name']) ?></h3>
                            <div class="rating-chip">★ <?= number_format($r['rating'], 1) ?></div>
                        </div>
                        <div class="rest-cuisine"><?= htmlspecialchars($r['cuisine_type']) ?></div>
                        <div class="rest-footer">
                            <span class="rest-meta-item">⏱️ <?= htmlspecialchars($r['delivery_time']) ?></span>
                            <span class="rest-meta-item">🛵 <?= $feeText ?></span>
                            <span class="rest-meta-item">Min $<?= number_format($r['min_order'], 0) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Customer Resources & Value Added Hub -->
    <section class="resources-section">
        <div style="padding: 0 28px;">
            <div class="section-header" style="margin-bottom: 8px;">
                <h2 class="section-title">🌟 More Customer Resources & Perks</h2>
            </div>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Designed to give you the most transparent, rewarding, and reliable food experience.</p>
            
            <div class="resource-grid">
                <a href="deals.php" class="resource-card">
                    <div class="resource-icon">🏷️</div>
                    <h4>Deals & Vouchers Hub</h4>
                    <p>Unlock daily flash codes and family feast discounts up to 50% off your entire order.</p>
                </a>

                <a href="help.php" class="resource-card">
                    <div class="resource-icon">🥗</div>
                    <h4>Dietary & Allergen Guide</h4>
                    <p>Easily browse Halal-certified kitchens, Vegan bowls, Nut-free, and Gluten-free menus.</p>
                </a>

                <a href="track.php" class="resource-card">
                    <div class="resource-icon">🛵</div>
                    <h4>Live GPS Motorbike Tracker</h4>
                    <p>Watch your rider progress in real-time from the restaurant stove to your front door.</p>
                </a>

                <div class="resource-card" style="cursor: pointer;" onclick="document.getElementById('floatingSupportBtn').click();">
                    <div class="resource-icon">💬</div>
                    <h4>24/7 Help & Instant Refunds</h4>
                    <p>Need assistance or have special dietary queries? Chat with our instant AI assistant.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
