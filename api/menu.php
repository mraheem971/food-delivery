<?php
/**
 * Restaurant & Menu API
 * GET /api/menu.php?restaurant_id=1 (or ?slug=...)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../data/db.php';

try {
    $db = getDB();

    $restaurantId = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : 0;
    $slug = trim($_GET['slug'] ?? '');

    if ($restaurantId > 0) {
        $stmt = $db->prepare("SELECT * FROM `restaurants` WHERE `id` = ?");
        $stmt->execute([$restaurantId]);
    } elseif (!empty($slug)) {
        $stmt = $db->prepare("SELECT * FROM `restaurants` WHERE `slug` = ?");
        $stmt->execute([$slug]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'restaurant_id or slug is required']);
        exit;
    }

    $restaurant = $stmt->fetch();
    if (!$restaurant) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Restaurant not found']);
        exit;
    }

    // Fetch menu items
    $stmtItems = $db->prepare("SELECT * FROM `menu_items` WHERE `restaurant_id` = ? ORDER BY `category_name` ASC, `is_popular` DESC, `id` ASC");
    $stmtItems->execute([$restaurant['id']]);
    $items = $stmtItems->fetchAll();

    // Fetch options for all items in this restaurant
    $stmtOpts = $db->prepare("SELECT o.* FROM `menu_item_options` o JOIN `menu_items` m ON o.item_id = m.id WHERE m.restaurant_id = ? ORDER BY o.id ASC");
    $stmtOpts->execute([$restaurant['id']]);
    $allOptions = $stmtOpts->fetchAll();

    // Group options by item_id -> group_name
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
    foreach ($items as $it) {
        $cat = $it['category_name'];
        if (!isset($menuByCategory[$cat])) {
            $menuByCategory[$cat] = [];
        }
        $it['options'] = isset($optionsByItem[$it['id']]) ? array_values($optionsByItem[$it['id']]) : [];
        $menuByCategory[$cat][] = $it;
    }

    // Fetch Reviews
    $stmtRev = $db->prepare("SELECT * FROM `reviews` WHERE `restaurant_id` = ? ORDER BY `created_at` DESC LIMIT 10");
    $stmtRev->execute([$restaurant['id']]);
    $reviews = $stmtRev->fetchAll();

    // Fetch Similar / Recommended Restaurants
    $stmtSim = $db->prepare("SELECT id, name, slug, image_url, rating, delivery_time, cuisine_type FROM `restaurants` WHERE `id` != ? LIMIT 3");
    $stmtSim->execute([$restaurant['id']]);
    $similar = $stmtSim->fetchAll();

    echo json_encode([
        'success' => true,
        'restaurant' => $restaurant,
        'categories' => array_keys($menuByCategory),
        'menu' => $menuByCategory,
        'reviews' => $reviews,
        'similar_restaurants' => $similar
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
