<?php
require_once __DIR__ . '/../data/db.php';
$db = getDB();

$restaurantFilter = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : 0;
$categoryFilter = trim($_GET['category'] ?? 'all');
$search = trim($_GET['search'] ?? '');

$restaurants = $db->query("SELECT id, name FROM `restaurants` ORDER BY `name` ASC")->fetchAll();

$where = [];
$params = [];

if ($restaurantFilter > 0) {
    $where[] = "m.restaurant_id = :rest_id";
    $params[':rest_id'] = $restaurantFilter;
}

if ($categoryFilter !== 'all' && !empty($categoryFilter)) {
    $where[] = "m.category_name = :cat";
    $params[':cat'] = $categoryFilter;
}

if (!empty($search)) {
    $where[] = "(m.name LIKE :s OR m.description LIKE :s2 OR r.name LIKE :s3)";
    $params[':s'] = "%{$search}%";
    $params[':s2'] = "%{$search}%";
    $params[':s3'] = "%{$search}%";
}

$sql = "SELECT m.*, r.name as restaurant_name 
        FROM `menu_items` m 
        JOIN `restaurants` r ON m.restaurant_id = r.id";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY m.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$dishes = $stmt->fetchAll();

require_once __DIR__ . '/header.php';
?>

<!-- Filter & Add Dish Bar -->
<div style="background: white; padding: 18px 24px; border-radius: var(--radius-md); border: 1px solid var(--border); margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <!-- Filters -->
        <form method="GET" action="menu.php" style="display: flex; gap: 10px; flex-wrap: wrap; flex: 1;">
            <select name="restaurant_id" onchange="this.form.submit()" style="padding: 8px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.88rem; font-weight: 700;">
                <option value="0">All Restaurants (<?= count($restaurants) ?>)</option>
                <?php foreach ($restaurants as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= $restaurantFilter == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search dish name..." style="padding: 8px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.88rem; outline: none; min-width: 200px;">
            <button type="submit" class="apply-btn" style="padding: 8px 16px; background: var(--secondary);">Filter</button>
        </form>

        <button type="button" class="checkout-btn" onclick="openAddDishModal()" style="width: auto; padding: 10px 22px; margin-top: 0; font-size: 0.9rem;">
            <span>➕ Add Fresh Dish</span>
        </button>
    </div>
</div>

<!-- Dishes Grid -->
<div class="section-header">
    <h3 class="section-title">Menu Dishes Catalog (<?= count($dishes) ?>)</h3>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-bottom: 48px;">
    <?php if (empty($dishes)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: white; border-radius: var(--radius-md); border: 1px dashed var(--border);">
            <div style="font-size: 3rem; margin-bottom: 8px;">🍽️</div>
            <h4 style="font-size: 1.2rem; font-weight: 800; color: var(--secondary);">No dishes found</h4>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 16px;">Click the button below to add your first fresh dish to the menu!</p>
            <button type="button" class="checkout-btn" onclick="openAddDishModal()" style="width: auto; margin: 0 auto; padding: 10px 24px;">➕ Add Fresh Dish</button>
        </div>
    <?php else: ?>
        <?php foreach ($dishes as $d): ?>
            <div style="background: white; border-radius: var(--radius-md); border: 1px solid var(--border); overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column;">
                <div style="position: relative; height: 160px; background: #e2e8f0;">
                    <img src="<?= htmlspecialchars($d['image_url']) ?>" alt="<?= htmlspecialchars($d['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <span style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.7); color: white; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">
                        <?= htmlspecialchars($d['restaurant_name']) ?>
                    </span>
                    <?php if ($d['is_popular']): ?>
                        <span class="badge-tag badge-featured" style="top: 10px; right: 10px; left: auto; font-size: 0.72rem;">⭐ Popular</span>
                    <?php endif; ?>
                </div>

                <div style="padding: 16px; display: flex; flex-direction: column; flex: 1;">
                    <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;"><?= htmlspecialchars($d['category_name']) ?></div>
                    <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--secondary); margin: 4px 0;"><?= htmlspecialchars($d['name']) ?></h4>
                    <p style="font-size: 0.84rem; color: var(--text-muted); line-height: 1.4; margin-bottom: 12px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        <?= htmlspecialchars($d['description']) ?>
                    </p>

                    <!-- Tags -->
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px;">
                        <?php if ($d['calories']): ?>
                            <span style="font-size: 0.75rem; background: #fef2f2; color: #b91c1c; font-weight: 700; padding: 2px 6px; border-radius: 4px;">🔥 <?= $d['calories'] ?> kcal</span>
                        <?php endif; ?>
                        <?php if ($d['is_vegetarian']): ?>
                            <span style="font-size: 0.75rem; background: #ecfdf5; color: #047857; font-weight: 700; padding: 2px 6px; border-radius: 4px;">🥬 Vegetarian</span>
                        <?php endif; ?>
                        <?php if ($d['is_spicy']): ?>
                            <span style="font-size: 0.75rem; background: #fff1f2; color: #be123c; font-weight: 700; padding: 2px 6px; border-radius: 4px;">🌶️ Spicy</span>
                        <?php endif; ?>
                        <?php if ($d['is_gluten_free']): ?>
                            <span style="font-size: 0.75rem; background: #eff6ff; color: #1d4ed8; font-weight: 700; padding: 2px 6px; border-radius: 4px;">🌾 GF</span>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: auto; padding-top: 10px; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <span style="font-size: 1.15rem; font-weight: 800; color: var(--primary);">$<?= number_format($d['price'], 2) ?></span>
                            <?php if (!empty($d['original_price'])): ?>
                                <span style="font-size: 0.85rem; color: var(--text-light); text-decoration: line-through; margin-left: 4px;">$<?= number_format($d['original_price'], 2) ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; gap: 6px;">
                            <a href="../restaurant.php?id=<?= $d['restaurant_id'] ?>" target="_blank" class="btn-action-pill" style="background: var(--bg-surface); color: var(--text-main);">View</a>
                            <button type="button" class="btn-action-pill" style="background: #fee2e2; color: #b91c1c;" onclick="deleteDish(<?= $d['id'] ?>)">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
