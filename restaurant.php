<?php
require_once __DIR__ . '/data/db.php';
$db = getDB();

$restaurantId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$stmt = $db->prepare("SELECT * FROM `restaurants` WHERE `id` = ?");
$stmt->execute([$restaurantId]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    header('Location: index.php');
    exit;
}

// Fetch menu items
$stmtItems = $db->prepare("SELECT * FROM `menu_items` WHERE `restaurant_id` = ? ORDER BY `category_name` ASC, `is_popular` DESC, `id` ASC");
$stmtItems->execute([$restaurant['id']]);
$items = $stmtItems->fetchAll();

// Fetch options for all items
$stmtOpts = $db->prepare("SELECT o.* FROM `menu_item_options` o JOIN `menu_items` m ON o.item_id = m.id WHERE m.restaurant_id = ? ORDER BY o.id ASC");
$stmtOpts->execute([$restaurant['id']]);
$allOptions = $stmtOpts->fetchAll();

$optionsByItem = [];
foreach ($allOptions as $opt) {
    $itemId = $opt['item_id'];
    $grp = $opt['group_name'];
    if (!isset($optionsByItem[$itemId])) {
        $optionsByItem[$itemId] = [];
    }
    if (!isset($optionsByItem[$itemId][$grp])) {
        $optionsByItem[$itemId][$grp] = [
            'group_name' => $grp,
            'is_required' => (bool)$opt['is_required'],
            'max_choices' => (int)$opt['max_choices'],
            'options' => []
        ];
    }
    $optionsByItem[$itemId][$grp]['options'][] = [
        'id' => $opt['id'],
        'name' => $opt['option_name'],
        'price' => (float)$opt['additional_price']
    ];
}

// Group items by category
$menuByCategory = [];
foreach ($items as &$it) {
    $cat = $it['category_name'];
    if (!isset($menuByCategory[$cat])) {
        $menuByCategory[$cat] = [];
    }
    $it['options'] = isset($optionsByItem[$it['id']]) ? array_values($optionsByItem[$it['id']]) : [];
    $menuByCategory[$cat][] = $it;
}

// Fetch reviews
$stmtRev = $db->prepare("SELECT * FROM `reviews` WHERE `restaurant_id` = ? ORDER BY `created_at` DESC LIMIT 6");
$stmtRev->execute([$restaurant['id']]);
$reviews = $stmtRev->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top: 24px; padding-bottom: 60px;">
    <!-- Restaurant Hero Banner -->
    <div class="restaurant-hero-banner">
        <img src="<?= htmlspecialchars($restaurant['banner_url']) ?>" alt="<?= htmlspecialchars($restaurant['name']) ?>">
        <div class="restaurant-hero-overlay">
            <div class="restaurant-profile-info">
                <h1><?= htmlspecialchars($restaurant['name']) ?></h1>
                <p><?= htmlspecialchars($restaurant['tagline']) ?></p>
                <div class="restaurant-meta-pills">
                    <span class="meta-pill" style="background: #f59e0b; color: #1e1e2d;">★ <?= number_format($restaurant['rating'], 1) ?> (<?= $restaurant['rating_count'] ?>+ reviews)</span>
                    <span class="meta-pill">⏱️ <?= htmlspecialchars($restaurant['delivery_time']) ?></span>
                    <span class="meta-pill">🛵 <?= (float)$restaurant['delivery_fee'] == 0 ? 'Free Delivery' : '$' . number_format($restaurant['delivery_fee'], 2) . ' Delivery' ?></span>
                    <span class="meta-pill">💵 Min Order $<?= number_format($restaurant['min_order'], 2) ?></span>
                    <?php if ($restaurant['is_halal']): ?>
                        <span class="meta-pill" style="background: #059669; color: white;">🌙 Halal Certified</span>
                    <?php endif; ?>
                    <a href="https://wa.me/923216793596?text=<?= urlencode("Hi, I want to inquire about menu items from " . $restaurant['name']) ?>" target="_blank" class="meta-pill" style="background: #25d366; color: white;">
                        💬 WhatsApp Chat
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu & Sidebar Layout -->
    <div class="menu-layout">
        <!-- Sticky Category Nav -->
        <aside class="category-nav-sticky">
            <div class="cat-nav-title">Menu Categories</div>
            <?php foreach ($menuByCategory as $catName => $catItems): ?>
                <a href="#cat-<?= md5($catName) ?>" class="cat-nav-link">
                    <?= htmlspecialchars($catName) ?> (<?= count($catItems) ?>)
                </a>
            <?php endforeach; ?>
            <a href="#restaurantReviewsSection" class="cat-nav-link" style="color: #f59e0b;">
                ★ Reviews (<?= count($reviews) ?>)
            </a>
        </aside>

        <!-- Menu Categories & Dishes -->
        <section class="menu-content">
            <?php foreach ($menuByCategory as $catName => $catItems): ?>
                <div class="menu-category-block" id="cat-<?= md5($catName) ?>">
                    <h3 class="menu-category-title"><?= htmlspecialchars($catName) ?></h3>
                    <div class="dishes-grid">
                        <?php foreach ($catItems as $dish): ?>
                            <?php
                                $dishJson = htmlspecialchars(json_encode($dish), ENT_QUOTES, 'UTF-8');
                                $restNameJson = htmlspecialchars(json_encode($restaurant['name']), ENT_QUOTES, 'UTF-8');
                            ?>
                            <div class="dish-card" onclick='openDishCustomizer(<?= $dishJson ?>, <?= $restaurant['id'] ?>, <?= $restNameJson ?>)'>
                                <div class="dish-details">
                                    <div class="dish-title"><?= htmlspecialchars($dish['name']) ?></div>
                                    <div class="dish-desc"><?= htmlspecialchars($dish['description']) ?></div>
                                    <div class="dish-price-row">
                                        <span class="dish-price">$<?= number_format($dish['price'], 2) ?></span>
                                        <?php if (!empty($dish['original_price'])): ?>
                                            <span class="dish-old-price">$<?= number_format($dish['original_price'], 2) ?></span>
                                        <?php endif; ?>
                                        <?php if ($dish['is_popular']): ?>
                                            <span style="font-size: 0.72rem; font-weight: 800; color: #ff2b85; background: #fff0f5; padding: 2px 6px; border-radius: 4px;">POPULAR</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="dish-img-box">
                                    <img src="<?= htmlspecialchars($dish['image_url']) ?>" alt="<?= htmlspecialchars($dish['name']) ?>" loading="lazy">
                                    <button type="button" class="dish-add-btn" title="Add to Cart">+</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Customer Reviews Section -->
            <div id="restaurantReviewsSection" style="margin-top: 48px; padding-top: 24px; border-top: 2px solid var(--border);">
                <div class="section-header">
                    <h3 class="section-title">Customer Reviews & Ratings (★ <?= number_format($restaurant['rating'], 1) ?>)</h3>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-bottom: 28px;">
                    <?php if (empty($reviews)): ?>
                        <p style="color: var(--text-muted); font-size: 0.9rem;">No reviews yet. Be the first to review!</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $rev): ?>
                            <div style="background: white; padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                    <span style="font-weight: 700; font-size: 0.95rem;"><?= htmlspecialchars($rev['customer_name']) ?></span>
                                    <span style="color: #f59e0b; font-weight: 800; font-size: 0.9rem;"><?= str_repeat('★', (int)$rev['rating']) ?></span>
                                </div>
                                <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.45;"><?= htmlspecialchars($rev['comment']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Add Review Form -->
                <div style="background: white; padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                    <h4 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 12px; color: var(--secondary);">Leave a Review for <?= htmlspecialchars($restaurant['name']) ?></h4>
                    <form id="submitReviewForm" onsubmit="handleReviewSubmit(event, <?= $restaurant['id'] ?>)">
                        <div style="display: grid; grid-template-columns: 1fr 140px; gap: 12px; margin-bottom: 12px;">
                            <input type="text" id="reviewCustomerName" placeholder="Your Name" required style="padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                            <select id="reviewRatingScore" style="padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem; font-weight: 700;">
                                <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                                <option value="4">⭐⭐⭐⭐ (4/5)</option>
                                <option value="3">⭐⭐⭐ (3/5)</option>
                                <option value="2">⭐⭐ (2/5)</option>
                                <option value="1">⭐ (1/5)</option>
                            </select>
                        </div>
                        <textarea id="reviewCommentText" rows="3" placeholder="How was the taste, packaging, and delivery speed?" required style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem; margin-bottom: 12px;"></textarea>
                        <button type="submit" class="apply-btn" style="background: var(--primary);">Submit Rating</button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
function openDishCustomizer(dish, restId, restName) {
    if (window.dishModalManager) {
        window.dishModalManager.open(dish, restId, restName);
    }
}

function handleReviewSubmit(e, restId) {
    e.preventDefault();
    const name = document.getElementById('reviewCustomerName').value.trim();
    const rating = document.getElementById('reviewRatingScore').value;
    const comment = document.getElementById('reviewCommentText').value.trim();

    fetch('api/reviews.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            restaurant_id: restId,
            customer_name: name,
            rating: parseInt(rating),
            comment: comment
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Thank you! Your review was posted.', 'success');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showToast(data.error || 'Failed to submit review', 'error');
        }
    })
    .catch(err => {
        showToast('Error submitting review', 'error');
        console.error(err);
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
