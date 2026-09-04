<?php
/**
 * Restaurants API
 * GET /api/restaurants.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../data/db.php';

try {
    $db = getDB();

    $category = trim($_GET['category'] ?? '');
    $search = trim($_GET['search'] ?? '');
    $sort = trim($_GET['sort'] ?? 'recommended');
    $freeDelivery = isset($_GET['free_delivery']) && $_GET['free_delivery'] === '1';
    $halal = isset($_GET['halal']) && $_GET['halal'] === '1';
    $featured = isset($_GET['featured']) && $_GET['featured'] === '1';
    $dietary = trim($_GET['dietary'] ?? '');

    $where = [];
    $params = [];

    if ($category !== '' && $category !== 'all') {
        $where[] = "(r.cuisine_type LIKE :category_like OR EXISTS (
            SELECT 1 FROM menu_items mi 
            WHERE mi.restaurant_id = r.id AND (mi.category_name LIKE :category_like2)
        ))";
        $params[':category_like'] = "%{$category}%";
        $params[':category_like2'] = "%{$category}%";
    }

    if ($search !== '') {
        $where[] = "(r.name LIKE :search OR r.cuisine_type LIKE :search OR r.tagline LIKE :search OR EXISTS (
            SELECT 1 FROM menu_items mi 
            WHERE mi.restaurant_id = r.id AND (mi.name LIKE :search2 OR mi.description LIKE :search3)
        ))";
        $params[':search'] = "%{$search}%";
        $params[':search2'] = "%{$search}%";
        $params[':search3'] = "%{$search}%";
    }

    if ($freeDelivery) {
        $where[] = "r.is_free_delivery = 1";
    }

    if ($halal) {
        $where[] = "r.is_halal = 1";
    }

    if ($featured) {
        $where[] = "r.is_featured = 1";
    }

    if ($dietary !== '') {
        if ($dietary === 'vegetarian') {
            $where[] = "EXISTS (SELECT 1 FROM menu_items mi WHERE mi.restaurant_id = r.id AND mi.is_vegetarian = 1)";
        } elseif ($dietary === 'vegan') {
            $where[] = "EXISTS (SELECT 1 FROM menu_items mi WHERE mi.restaurant_id = r.id AND mi.is_vegan = 1)";
        } elseif ($dietary === 'gluten_free') {
            $where[] = "EXISTS (SELECT 1 FROM menu_items mi WHERE mi.restaurant_id = r.id AND mi.is_gluten_free = 1)";
        } elseif ($dietary === 'spicy') {
            $where[] = "EXISTS (SELECT 1 FROM menu_items mi WHERE mi.restaurant_id = r.id AND mi.is_spicy = 1)";
        }
    }

    $sql = "SELECT r.* FROM `restaurants` r";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    switch ($sort) {
        case 'rating':
            $sql .= " ORDER BY r.rating DESC, r.rating_count DESC";
            break;
        case 'delivery_time':
            $sql .= " ORDER BY CAST(SUBSTRING_INDEX(r.delivery_time, '-', 1) AS UNSIGNED) ASC";
            break;
        case 'fee_low':
            $sql .= " ORDER BY r.delivery_fee ASC";
            break;
        case 'min_order':
            $sql .= " ORDER BY r.min_order ASC";
            break;
        case 'recommended':
        default:
            $sql .= " ORDER BY r.is_featured DESC, r.rating DESC";
            break;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $restaurants = $stmt->fetchAll();

    // Also return category list for filters
    $categories = $db->query("SELECT * FROM `categories` ORDER BY `id` ASC")->fetchAll();

    echo json_encode([
        'success' => true,
        'count' => count($restaurants),
        'restaurants' => $restaurants,
        'categories' => $categories
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
