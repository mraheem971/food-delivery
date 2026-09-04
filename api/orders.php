<?php
/**
 * Orders Management API
 * POST /api/orders.php (place order)
 * GET /api/orders.php?order_number=ORD-XXXX
 * GET /api/orders.php?recent=1
 * POST /api/orders.php?action=advance_status
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

    // 1. ADVANCE STATUS (For live tracking & kitchen dispatch)
    if (isset($_GET['action']) && $_GET['action'] === 'advance_status') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $orderNumber = trim($input['order_number'] ?? '');
        $newStatus = trim($input['status'] ?? '');

        $validStatuses = ['placed', 'confirmed', 'preparing', 'on_way', 'delivered', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid status']);
            exit;
        }

        $stmt = $db->prepare("UPDATE `orders` SET `order_status` = ? WHERE `order_number` = ?");
        $stmt->execute([$newStatus, $orderNumber]);

        // Send WhatsApp Live Status Update to Customer
        $stmtGet = $db->prepare("SELECT * FROM `orders` WHERE `order_number` = ?");
        $stmtGet->execute([$orderNumber]);
        $order = $stmtGet->fetch();
        if ($order) {
            WhatsAppService::sendOrderStatusUpdate($order, $newStatus);
        }

        echo json_encode(['success' => true, 'order_status' => $newStatus]);
        exit;
    }

    // 2. GET ORDERS (By order number or recent list)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $orderNumber = trim($_GET['order_number'] ?? $_GET['order_id'] ?? '');

        if (!empty($orderNumber)) {
            $stmt = $db->prepare("SELECT * FROM `orders` WHERE `order_number` = ? OR `id` = ?");
            $stmt->execute([$orderNumber, is_numeric($orderNumber) ? (int)$orderNumber : 0]);
            $order = $stmt->fetch();

            if (!$order) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Order not found']);
                exit;
            }

            // Fetch order items
            $stmtItems = $db->prepare("SELECT * FROM `order_items` WHERE `order_id` = ?");
            $stmtItems->execute([$order['id']]);
            $items = $stmtItems->fetchAll();

            foreach ($items as &$it) {
                $it['selected_options'] = !empty($it['selected_options']) ? json_decode($it['selected_options'], true) : [];
            }

            // Restaurant info
            $stmtRest = $db->prepare("SELECT id, name, image_url, address, delivery_time FROM `restaurants` WHERE `id` = ?");
            $stmtRest->execute([$order['restaurant_id']]);
            $restaurant = $stmtRest->fetch();

            echo json_encode([
                'success' => true,
                'order' => $order,
                'items' => $items,
                'restaurant' => $restaurant
            ]);
            exit;
        }

        // Return recent orders
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        $stmtRecent = $db->prepare("SELECT * FROM `orders` ORDER BY `created_at` DESC LIMIT ?");
        $stmtRecent->bindValue(1, $limit, PDO::PARAM_INT);
        $stmtRecent->execute();
        $recentOrders = $stmtRecent->fetchAll();

        echo json_encode([
            'success' => true,
            'orders' => $recentOrders
        ]);
        exit;
    }

    // 3. PLACE NEW ORDER (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $customerName = trim($input['customer_name'] ?? '');
        $customerPhone = trim($input['customer_phone'] ?? '');
        $customerEmail = trim($input['customer_email'] ?? '');
        $deliveryAddress = trim($input['delivery_address'] ?? '');
        $deliveryInstructions = trim($input['delivery_instructions'] ?? '');
        $paymentMethod = trim($input['payment_method'] ?? 'cash_on_delivery');
        $restaurantId = (int)($input['restaurant_id'] ?? 0);
        $items = $input['items'] ?? [];
        $appliedCoupon = trim($input['applied_coupon'] ?? '');
        $driverTip = (float)($input['driver_tip'] ?? 0.00);

        if (empty($customerName) || empty($customerPhone) || empty($deliveryAddress)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Please fill in your name, phone number, and delivery address.']);
            exit;
        }

        if ($restaurantId <= 0 || empty($items)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Your cart is empty or restaurant is invalid.']);
            exit;
        }

        // Fetch restaurant details
        $stmtR = $db->prepare("SELECT * FROM `restaurants` WHERE `id` = ?");
        $stmtR->execute([$restaurantId]);
        $restaurant = $stmtR->fetch();
        if (!$restaurant) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Restaurant not found']);
            exit;
        }

        // Calculate Subtotal & Verify items
        $subtotal = 0.00;
        $processedItems = [];

        foreach ($items as $cartItem) {
            $itemId = (int)($cartItem['item_id'] ?? 0);
            $qty = max(1, (int)($cartItem['quantity'] ?? 1));
            $specialNotes = trim($cartItem['special_instructions'] ?? '');
            $selectedOpts = $cartItem['selected_options'] ?? [];

            // Fetch dish
            $stmtItem = $db->prepare("SELECT * FROM `menu_items` WHERE `id` = ? AND `restaurant_id` = ?");
            $stmtItem->execute([$itemId, $restaurantId]);
            $dish = $stmtItem->fetch();

            if (!$dish) {
                continue;
            }

            $basePrice = (float)$dish['price'];
            $optionsCost = 0.00;

            if (is_array($selectedOpts)) {
                foreach ($selectedOpts as $opt) {
                    $optionsCost += (float)($opt['price'] ?? 0.00);
                }
            }

            $singleItemPrice = $basePrice + $optionsCost;
            $lineTotal = $singleItemPrice * $qty;
            $subtotal += $lineTotal;

            $processedItems[] = [
                'name' => $dish['name'],
                'price' => $singleItemPrice,
                'quantity' => $qty,
                'options' => $selectedOpts,
                'special_instructions' => $specialNotes,
                'total' => $lineTotal
            ];
        }

        if (empty($processedItems)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No valid items found in order.']);
            exit;
        }

        // Delivery & Platform fees
        $deliveryFee = (float)$restaurant['delivery_fee'];
        $platformFee = 0.50;
        $discountAmount = 0.00;

        // Apply coupon if provided
        if (!empty($appliedCoupon)) {
            $stmtC = $db->prepare("SELECT * FROM `coupons` WHERE UPPER(`code`) = ?");
            $stmtC->execute([strtoupper($appliedCoupon)]);
            $coupon = $stmtC->fetch();

            if ($coupon && $subtotal >= (float)$coupon['min_order_amount']) {
                if ($coupon['discount_type'] === 'percentage') {
                    $discountAmount = ($subtotal * (float)$coupon['discount_value']) / 100.0;
                    if ($discountAmount > (float)$coupon['max_discount']) {
                        $discountAmount = (float)$coupon['max_discount'];
                    }
                } else {
                    $discountAmount = min((float)$coupon['discount_value'], $subtotal);
                }
            }
        }

        $totalAmount = max(0, $subtotal + $deliveryFee + $platformFee + $driverTip - $discountAmount);

        // Generate unique order number
        $orderNumber = 'ORD-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $estimatedTime = date('h:i A', strtotime('+30 minutes'));

        // Begin transaction
        $db->beginTransaction();

        $stmtOrder = $db->prepare("INSERT INTO `orders` 
            (`order_number`, `customer_name`, `customer_phone`, `customer_email`, `delivery_address`, `delivery_instructions`, `payment_method`, `restaurant_id`, `restaurant_name`, `subtotal`, `delivery_fee`, `platform_fee`, `discount_amount`, `driver_tip`, `total_amount`, `applied_coupon`, `order_status`, `estimated_delivery_time`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'placed', ?)");

        $stmtOrder->execute([
            $orderNumber,
            $customerName,
            $customerPhone,
            $customerEmail,
            $deliveryAddress,
            $deliveryInstructions,
            $paymentMethod,
            $restaurantId,
            $restaurant['name'],
            $subtotal,
            $deliveryFee,
            $platformFee,
            $discountAmount,
            $driverTip,
            $totalAmount,
            $appliedCoupon ?: null,
            $estimatedTime
        ]);

        $orderId = $db->lastInsertId();

        $stmtOrderItem = $db->prepare("INSERT INTO `order_items`
            (`order_id`, `item_name`, `item_price`, `quantity`, `selected_options`, `special_instructions`, `item_total`)
            VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($processedItems as $item) {
            $stmtOrderItem->execute([
                $orderId,
                $item['name'],
                $item['price'],
                $item['quantity'],
                json_encode($item['options']),
                $item['special_instructions'],
                $item['total']
            ]);
        }

        $db->commit();

        // 4. Send Automated WhatsApp Notification to Customer & Store Owner
        $orderData = [
            'id' => $orderId,
            'order_number' => $orderNumber,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'customer_email' => $customerEmail,
            'delivery_address' => $deliveryAddress,
            'payment_method' => $paymentMethod,
            'restaurant_name' => $restaurant['name'],
            'total_amount' => $totalAmount,
            'estimated_delivery_time' => $estimatedTime
        ];

        WhatsAppService::sendOrderConfirmation($orderData, $processedItems);

        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully! WhatsApp confirmation sent.',
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'total_amount' => $totalAmount,
            'estimated_delivery_time' => $estimatedTime,
            'track_url' => "track.php?order_number={$orderNumber}"
        ]);
        exit;
    }

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
