<?php
/**
 * Coupons & Deals API
 * GET /api/coupons.php (list available vouchers)
 * POST /api/coupons.php (validate & apply voucher)
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

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $db->query("SELECT * FROM `coupons` ORDER BY `discount_value` DESC");
        $coupons = $stmt->fetchAll();
        echo json_encode([
            'success' => true,
            'coupons' => $coupons
        ]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $code = strtoupper(trim($input['code'] ?? ''));
        $subtotal = isset($input['subtotal']) ? (float)$input['subtotal'] : 0.0;

        if (empty($code)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Please provide a coupon code']);
            exit;
        }

        $stmt = $db->prepare("SELECT * FROM `coupons` WHERE UPPER(`code`) = ?");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => "Coupon '{$code}' is invalid or expired."]);
            exit;
        }

        if ($subtotal < (float)$coupon['min_order_amount']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => "Minimum order amount of $" . number_format($coupon['min_order_amount'], 2) . " required for this voucher."
            ]);
            exit;
        }

        $discount = 0.00;
        if ($coupon['discount_type'] === 'percentage') {
            $discount = ($subtotal * (float)$coupon['discount_value']) / 100.0;
            if ($discount > (float)$coupon['max_discount']) {
                $discount = (float)$coupon['max_discount'];
            }
        } else {
            $discount = (float)$coupon['discount_value'];
            if ($discount > $subtotal) {
                $discount = $subtotal;
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "Coupon {$coupon['code']} applied successfully!",
            'coupon' => $coupon,
            'discount' => round($discount, 2),
            'new_subtotal' => round(max(0, $subtotal - $discount), 2)
        ]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
