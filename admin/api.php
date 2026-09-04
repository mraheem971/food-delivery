<?php
/**
 * Admin API Layer
 * Handles order updates, dish management, coupons, and WhatsApp management
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../data/db.php';
require_once __DIR__ . '/../data/whatsapp.php';

try {
    $db = getDB();
    $action = $_GET['action'] ?? '';

    // 1. UPDATE ORDER STATUS & SEND WHATSAPP NOTIFICATION
    if ($action === 'update_order_status') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $orderId = (int)($input['order_id'] ?? 0);
        $orderNumber = trim($input['order_number'] ?? '');
        $status = trim($input['status'] ?? '');

        $validStatuses = ['placed', 'confirmed', 'preparing', 'on_way', 'delivered', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid status']);
            exit;
        }

        if ($orderId > 0) {
            $stmt = $db->prepare("UPDATE `orders` SET `order_status` = ? WHERE `id` = ?");
            $stmt->execute([$status, $orderId]);
            $stmtGet = $db->prepare("SELECT * FROM `orders` WHERE `id` = ?");
            $stmtGet->execute([$orderId]);
            $order = $stmtGet->fetch();
        } elseif (!empty($orderNumber)) {
            $stmt = $db->prepare("UPDATE `orders` SET `order_status` = ? WHERE `order_number` = ?");
            $stmt->execute([$status, $orderNumber]);
            $stmtGet = $db->prepare("SELECT * FROM `orders` WHERE `order_number` = ?");
            $stmtGet->execute([$orderNumber]);
            $order = $stmtGet->fetch();
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Order ID or number required']);
            exit;
        }

        // Send WhatsApp alert to customer
        if ($order) {
            WhatsAppService::sendOrderStatusUpdate($order, $status);
        }

        echo json_encode(['success' => true, 'message' => "Order updated to {$status} & WhatsApp sent!"]);
        exit;
    }

    // 2. SEND TEST WHATSAPP MESSAGE
    if ($action === 'send_whatsapp_test') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $phone = trim($input['phone'] ?? '');
        $msg = trim($input['message'] ?? 'Hello from FoodHub WhatsApp System! 🐼🍔');

        if (empty($phone)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Phone number is required']);
            exit;
        }

        $res = WhatsAppService::sendMessage($phone, $msg);
        echo json_encode($res);
        exit;
    }

    // 3. GET WHATSAPP STATUS
    if ($action === 'get_whatsapp_status') {
        $status = WhatsAppService::getSessionStatus();
        echo json_encode(['success' => true, 'session' => $status]);
        exit;
    }

    // 4. START WHATSAPP SESSION (GET LIVE QR CODE)
    if ($action === 'start_whatsapp_session') {
        $sessionId = WHATSAPP_DEFAULT_SESSION;
        $res = WhatsAppService::startSession($sessionId, 'FoodHub Express Store');
        echo json_encode($res);
        exit;
    }

    // 5. ADD FRESH DISH
    if ($action === 'add_dish') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $restaurantId = (int)($input['restaurant_id'] ?? 1);
        $name = trim($input['name'] ?? '');
        $categoryName = trim($input['category_name'] ?? 'Chef Specials');
        $description = trim($input['description'] ?? '');
        $price = (float)($input['price'] ?? 0);
        $originalPrice = !empty($input['original_price']) ? (float)$input['original_price'] : null;
        $imageUrl = trim($input['image_url'] ?? '');
        $isPopular = !empty($input['is_popular']) ? 1 : 0;
        $isVegetarian = !empty($input['is_vegetarian']) ? 1 : 0;
        $isVegan = !empty($input['is_vegan']) ? 1 : 0;
        $isGlutenFree = !empty($input['is_gluten_free']) ? 1 : 0;
        $isSpicy = !empty($input['is_spicy']) ? 1 : 0;
        $calories = !empty($input['calories']) ? (int)$input['calories'] : null;
        $prepTime = trim($input['prep_time'] ?? '15-20 min');
        $options = $input['options'] ?? [];

        if (empty($name) || $price <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Dish name and valid price are required']);
            exit;
        }

        if (empty($imageUrl)) {
            $imageUrl = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&auto=format&fit=crop&q=80';
        }

        $db->beginTransaction();

        $stmt = $db->prepare("INSERT INTO `menu_items` 
            (`restaurant_id`, `category_name`, `name`, `description`, `price`, `original_price`, `image_url`, `is_popular`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `is_spicy`, `calories`, `prep_time`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $restaurantId, $categoryName, $name, $description, $price, $originalPrice,
            $imageUrl, $isPopular, $isVegetarian, $isVegan, $isGlutenFree, $isSpicy,
            $calories, $prepTime
        ]);

        $itemId = $db->lastInsertId();

        if (!empty($options) && is_array($options)) {
            $stmtOpt = $db->prepare("INSERT INTO `menu_item_options` (`item_id`, `group_name`, `option_name`, `additional_price`, `is_required`, `max_choices`) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($options as $opt) {
                if (!empty($opt['option_name'])) {
                    $stmtOpt->execute([
                        $itemId,
                        $opt['group_name'] ?: 'Options',
                        $opt['option_name'],
                        (float)($opt['additional_price'] ?? 0),
                        !empty($opt['is_required']) ? 1 : 0,
                        (int)($opt['max_choices'] ?? 1)
                    ]);
                }
            }
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Fresh dish added successfully!',
            'item_id' => $itemId
        ]);
        exit;
    }

    // 6. EDIT DISH
    if ($action === 'edit_dish') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $itemId = (int)($input['item_id'] ?? 0);
        $name = trim($input['name'] ?? '');
        $categoryName = trim($input['category_name'] ?? '');
        $description = trim($input['description'] ?? '');
        $price = (float)($input['price'] ?? 0);
        $originalPrice = !empty($input['original_price']) ? (float)$input['original_price'] : null;
        $imageUrl = trim($input['image_url'] ?? '');
        $isPopular = !empty($input['is_popular']) ? 1 : 0;
        $isVegetarian = !empty($input['is_vegetarian']) ? 1 : 0;
        $calories = !empty($input['calories']) ? (int)$input['calories'] : null;

        if ($itemId <= 0 || empty($name) || $price <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Valid dish ID, name, and price are required']);
            exit;
        }

        $stmt = $db->prepare("UPDATE `menu_items` SET 
            `name` = ?, `category_name` = ?, `description` = ?, `price` = ?, `original_price` = ?, 
            `image_url` = ?, `is_popular` = ?, `is_vegetarian` = ?, `calories` = ?
            WHERE `id` = ?");

        $stmt->execute([$name, $categoryName, $description, $price, $originalPrice, $imageUrl, $isPopular, $isVegetarian, $calories, $itemId]);

        echo json_encode(['success' => true, 'message' => 'Dish updated successfully!']);
        exit;
    }

    // 7. DELETE DISH
    if ($action === 'delete_dish') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $itemId = (int)($input['item_id'] ?? 0);

        if ($itemId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Dish ID required']);
            exit;
        }

        $stmt = $db->prepare("DELETE FROM `menu_items` WHERE `id` = ?");
        $stmt->execute([$itemId]);

        echo json_encode(['success' => true, 'message' => 'Dish deleted from menu']);
        exit;
    }

    // 8. ADD COUPON
    if ($action === 'add_coupon') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $code = strtoupper(trim($input['code'] ?? ''));
        $title = trim($input['title'] ?? '');
        $desc = trim($input['description'] ?? '');
        $type = $input['discount_type'] ?? 'percentage';
        $val = (float)($input['discount_value'] ?? 0);
        $min = (float)($input['min_order_amount'] ?? 0);
        $max = (float)($input['max_discount'] ?? 50);
        $badge = trim($input['badge'] ?? 'SPECIAL');

        if (empty($code) || $val <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Coupon code and value are required']);
            exit;
        }

        $stmt = $db->prepare("INSERT INTO `coupons` (`code`, `title`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount`, `expires_at`, `badge`) VALUES (?, ?, ?, ?, ?, ?, ?, '2026-12-31', ?)");
        $stmt->execute([$code, $title, $desc, $type, $val, $min, $max, $badge]);

        echo json_encode(['success' => true, 'message' => "Coupon {$code} created!"]);
        exit;
    }

    // 9. DELETE COUPON
    if ($action === 'delete_coupon') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $couponId = (int)($input['id'] ?? 0);

        $stmt = $db->prepare("DELETE FROM `coupons` WHERE `id` = ?");
        $stmt->execute([$couponId]);

        echo json_encode(['success' => true, 'message' => 'Coupon deleted']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid action']);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
