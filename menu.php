<?php
require_once __DIR__ . '/data/db.php';
$db = getDB();

$restaurantId = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$categoryFilter = trim($_GET['category'] ?? 'all');
$search = trim($_GET['search'] ?? '');

$restaurants = $db->query("SELECT id, name, slug, image_url, cuisine_type FROM `restaurants` ORDER BY `id` ASC")->fetchAll();
$categories = $db->query("SELECT * FROM `categories` ORDER BY `id` ASC")->fetchAll();

$where = [];
$params = [];

if ($restaurantId > 0) {
    $where[] = "m.restaurant_id = :rest_id";
    $params[':rest_id'] = $restaurantId;
}

if ($categoryFilter !== 'all' && !empty($categoryFilter)) {
    $where[] = "(m.category_name LIKE :cat OR r.cuisine_type LIKE :cat2)";
    $params[':cat'] = "%{$categoryFilter}%";
    $params[':cat2'] = "%{$categoryFilter}%";
}

if (!empty($search)) {
    $where[] = "(m.name LIKE :s OR m.description LIKE :s2 OR r.name LIKE :s3)";
    $params[':s'] = "%{$search}%";
    $params[':s2'] = "%{$search}%";
    $params[':s3'] = "%{$search}%";
}

$sql = "SELECT m.*, r.name as restaurant_name, r.delivery_time, r.delivery_fee 
        FROM `menu_items` m 
        JOIN `restaurants` r ON m.restaurant_id = r.id";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY m.is_popular DESC, m.id ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$dishes = $stmt->fetchAll();

// Fetch options for all items
$allOptions = $db->query("SELECT * FROM `menu_item_options` ORDER BY `id` ASC")->fetchAll();
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

foreach ($dishes as &$d) {
    $d['options'] = isset($optionsByItem[$d['id']]) ? array_values($optionsByItem[$d['id']]) : [];
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top: 28px; padding-bottom: 60px;">
    <!-- Menu Header Banner -->
    <div style="background: linear-gradient(135deg, #1e1e2d 0%, #2b2d42 100%); color: white; padding: 36px; border-radius: var(--radius-lg); margin-bottom: 32px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="max-width: 600px;">
            <span class="hero-tag" style="background: rgba(255, 43, 133, 0.3); color: #ff6b9d;">🍽️ Food & Dishes Catalog</span>
            <h1 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 8px;">Explore All Fresh Dishes & Menus</h1>
            <p style="opacity: 0.9; font-size: 1rem;">Browse signature smash burgers, artisan pizzas, Japanese ramen, fresh salads, and desserts across all local kitchens.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="deals.php" class="voucher-chip" style="background: #ffffff; color: var(--primary); font-weight: 800;">
                <span>🎁 Use Code: WELCOME50 for 50% OFF</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="filter-bar" style="margin-bottom: 28px;">
        <form method="GET" action="menu.php" style="display: flex; gap: 12px; flex-wrap: wrap; width: 100%; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <!-- Restaurant Selector -->
                <select name="restaurant_id" onchange="this.form.submit()" class="custom-select" style="padding: 8px 14px; font-weight: 700;">
                    <option value="0">All Restaurants (<?= count($restaurants) ?>)</option>
                    <?php foreach ($restaurants as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= $restaurantId == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Search Input -->
                <div style="position: relative;">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search dish name, ingredient..." style="padding: 8px 14px 8px 32px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.88rem; outline: none; min-width: 220px;">
                    <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;">🔍</span>
                </div>

                <button type="submit" class="apply-btn" style="padding: 8px 16px; background: var(--secondary);">Filter</button>
            </div>

            <div style="font-size: 0.88rem; color: var(--text-muted); font-weight: 700;">
                Showing <span style="color: var(--primary);"><?= count($dishes) ?></span> delicious items
            </div>
        </form>
    </div>

    <!-- Category Pills -->
    <div class="category-slider" style="margin-bottom: 24px;">
        <a href="menu.php" class="cat-pill <?= $categoryFilter === 'all' ? 'active' : '' ?>">
            <span class="cat-icon">🍽️</span>
            <span>All Dishes</span>
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="menu.php?category=<?= urlencode($cat['slug']) ?><?= $restaurantId > 0 ? '&restaurant_id='.$restaurantId : '' ?>" class="cat-pill <?= $categoryFilter === $cat['slug'] ? 'active' : '' ?>">
                <span class="cat-icon"><?= htmlspecialchars($cat['icon']) ?></span>
                <span><?= htmlspecialchars($cat['name']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Dishes Grid -->
    <div class="dishes-grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        <?php if (empty($dishes)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: white; border-radius: var(--radius-md); border: 1px dashed var(--border);">
                <div style="font-size: 3rem; margin-bottom: 8px;">🍽️</div>
                <h3 style="font-weight: 800; font-size: 1.25rem; margin-bottom: 6px;">No dishes found matching your selection</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 16px;">Try clearing filters or search for another delicious dish.</p>
                <a href="menu.php" class="checkout-btn" style="width: auto; margin: 0 auto; display: inline-flex; padding: 10px 24px;">View Full Menu</a>
            </div>
        <?php else: ?>
            <?php foreach ($dishes as $dish): ?>
                <?php
                    $dishJson = htmlspecialchars(json_encode($dish), ENT_QUOTES, 'UTF-8');
                    $restNameJson = htmlspecialchars(json_encode($dish['restaurant_name']), ENT_QUOTES, 'UTF-8');
                ?>
                <div class="dish-card" onclick='openDishCustomizer(<?= $dishJson ?>, <?= $dish['restaurant_id'] ?>, <?= $restNameJson ?>)' style="padding: 18px; border-radius: var(--radius-md);">
                    <div class="dish-details">
                        <div style="font-size: 0.78rem; font-weight: 700; color: var(--primary); text-transform: uppercase;">
                            <?= htmlspecialchars($dish['restaurant_name']) ?> • <?= htmlspecialchars($dish['category_name']) ?>
                        </div>
                        <h3 class="dish-title" style="margin: 4px 0;"><?= htmlspecialchars($dish['name']) ?></h3>
                        <p class="dish-desc"><?= htmlspecialchars($dish['description']) ?></p>

                        <!-- Dietary Badges -->
                        <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 10px;">
                            <?php if ($dish['calories']): ?>
                                <span style="font-size: 0.72rem; background: #fef2f2; color: #b91c1c; font-weight: 700; padding: 2px 6px; border-radius: 4px;">🔥 <?= $dish['calories'] ?> kcal</span>
                            <?php endif; ?>
                            <?php if ($dish['is_vegetarian']): ?>
                                <span style="font-size: 0.72rem; background: #ecfdf5; color: #047857; font-weight: 700; padding: 2px 6px; border-radius: 4px;">🥬 Vegetarian</span>
                            <?php endif; ?>
                            <?php if ($dish['is_spicy']): ?>
                                <span style="font-size: 0.72rem; background: #fff1f2; color: #be123c; font-weight: 700; padding: 2px 6px; border-radius: 4px;">🌶️ Spicy</span>
                            <?php endif; ?>
                        </div>

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
                    <div class="dish-img-box" style="width: 105px; height: 105px;">
                        <img src="<?= htmlspecialchars($dish['image_url']) ?>" alt="<?= htmlspecialchars($dish['name']) ?>" loading="lazy">
                        <button type="button" class="dish-add-btn" title="Customize & Add to Cart">+</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<script>
function openDishCustomizer(dish, restId, restName) {
    if (window.dishModalManager) {
        window.dishModalManager.open(dish, restId, restName);
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
