<?php
/**
 * WhatsApp Integration Service for FoodHub
 * Powered by Baileys WhatsApp Microservice (http://127.0.0.1:3000)
 */

require_once __DIR__ . '/config.php';

if (!defined('WHATSAPP_API_URL')) {
    define('WHATSAPP_API_URL', 'http://127.0.0.1:3000');
}
if (!defined('WHATSAPP_DEFAULT_SESSION')) {
    define('WHATSAPP_DEFAULT_SESSION', 'wa_1788497395_gb6iZITG');
}
if (!defined('WHATSAPP_ADMIN_PHONE')) {
    define('WHATSAPP_ADMIN_PHONE', '923216793596');
}
if (!defined('WHATSAPP_ENABLED')) {
    define('WHATSAPP_ENABLED', true);
}

class WhatsAppService {

    /**
     * Send direct WhatsApp text or media message
     */
    public static function sendMessage(string $receiver, string $message, ?string $sessionId = null): array {
        if (!WHATSAPP_ENABLED) {
            return ['success' => false, 'error' => 'WhatsApp notifications disabled in config'];
        }

        $sessionId = $sessionId ?: WHATSAPP_DEFAULT_SESSION;
        $apiUrl = rtrim(WHATSAPP_API_URL, '/') . '/api/messages/send';

        $cleanPhone = preg_replace('/[^0-9]/', '', $receiver);
        if (empty($cleanPhone)) {
            return ['success' => false, 'error' => 'Invalid receiver phone number'];
        }

        $payload = [
            'sessionId' => $sessionId,
            'receiver' => $cleanPhone,
            'message' => $message
        ];

        try {
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return ['success' => false, 'error' => 'cURL Error: ' . $error];
            }

            $data = json_decode($response, true);
            return [
                'success' => $httpCode >= 200 && $httpCode < 300,
                'response' => $data,
                'http_code' => $httpCode
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check Baileys Server Health & Session Status
     */
    public static function getSessionStatus(?string $sessionId = null): array {
        $sessionId = $sessionId ?: WHATSAPP_DEFAULT_SESSION;
        $url = rtrim(WHATSAPP_API_URL, '/') . "/api/sessions/status/{$sessionId}";

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response && $httpCode === 200) {
                return json_decode($response, true) ?: ['status' => 'offline'];
            }
            return ['status' => 'offline', 'error' => 'Cannot connect to WhatsApp microservice'];
        } catch (Exception $e) {
            return ['status' => 'offline', 'error' => $e->getMessage()];
        }
    }

    /**
     * Start/Initialize a new WhatsApp Session and generate QR Code
     */
    public static function startSession(string $sessionId, string $accountName = 'FoodHub Store'): array {
        $url = rtrim(WHATSAPP_API_URL, '/') . '/api/sessions/start';
        $payload = [
            'sessionId' => $sessionId,
            'accountName' => $accountName
        ];

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            curl_close($ch);

            return json_decode($response, true) ?: ['error' => 'Failed to start session'];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Send Rich Order Confirmation to Customer via WhatsApp
     */
    public static function sendOrderConfirmation(array $order, array $items): array {
        $customerName = $order['customer_name'] ?? 'Valued Customer';
        $orderNo = $order['order_number'] ?? 'ORD-XXXX';
        $restaurantName = $order['restaurant_name'] ?? 'FoodHub Kitchen';
        $total = number_format((float)($order['total_amount'] ?? 0), 2);
        $address = $order['delivery_address'] ?? 'Your Address';
        $eta = $order['estimated_delivery_time'] ?? '25-35 mins';
        $phone = $order['customer_phone'] ?? '';

        $itemsSummary = "";
        foreach ($items as $it) {
            $name = $it['name'] ?? $it['item_name'] ?? 'Dish';
            $qty = $it['quantity'] ?? 1;
            $price = number_format((float)($it['total'] ?? $it['item_total'] ?? 0), 2);
            $itemsSummary .= "  • {$qty}x {$name} - \${$price}\n";
        }

        $trackUrl = APP_URL . "/track.php?order_number={$orderNo}";

        $message = "🐼 *FOODHUB ORDER CONFIRMATION* 🍔\n"
                 . "━━━━━━━━━━━━━━━━━━━━━\n"
                 . "Hello *{$customerName}*, your order has been received!\n\n"
                 . "📋 *Order Number:* `{$orderNo}`\n"
                 . "🍳 *Restaurant:* {$restaurantName}\n"
                 . "⏱️ *Estimated Delivery:* {$eta}\n"
                 . "📍 *Delivery Address:* {$address}\n\n"
                 . "🛒 *Items Ordered:*\n"
                 . "{$itemsSummary}\n"
                 . "💰 *Total to Pay:* *\$" . $total . "* (" . ($order['payment_method'] === 'cash_on_delivery' ? 'Cash on Delivery' : 'Paid Online') . ")\n"
                 . "━━━━━━━━━━━━━━━━━━━━━\n"
                 . "🛵 *Live GPS Order Tracking:*\n"
                 . "{$trackUrl}\n\n"
                 . "Thank you for ordering with FoodHub Express! 😋";

        // Send to Customer
        $res = self::sendMessage($phone, $message);

        // Also notify Store Admin / Owner
        self::notifyAdminNewOrder($order, $items);

        return $res;
    }

    /**
     * Send WhatsApp Notification on Order Status Changes
     */
    public static function sendOrderStatusUpdate(array $order, string $newStatus): array {
        $customerName = $order['customer_name'] ?? 'Customer';
        $orderNo = $order['order_number'] ?? 'ORD-XXXX';
        $restaurantName = $order['restaurant_name'] ?? 'FoodHub Kitchen';
        $phone = $order['customer_phone'] ?? '';
        $trackUrl = APP_URL . "/track.php?order_number={$orderNo}";

        $statusMessages = [
            'confirmed' => "✅ *Order Confirmed!*\n{$restaurantName} has accepted your order `{$orderNo}` and the kitchen is getting ready.",
            'preparing' => "🍳 *Cooking in the Kitchen!*\nThe chef is crafting your delicious meal for order `{$orderNo}`.",
            'on_way' => "🛵 *Rider on the Way!*\nYour FoodHub delivery rider has picked up your meal and is speeding to your doorstep! ETA: 10-15 mins.",
            'delivered' => "🎉 *Order Delivered!*\nYour food has arrived! Enjoy your delicious meal! Please rate your experience at {$trackUrl}",
            'cancelled' => "⚠️ *Order Update*\nYour order `{$orderNo}` was cancelled. If you need assistance, please reply directly to this message."
        ];

        $statusText = $statusMessages[$newStatus] ?? "Your order `{$orderNo}` status has been updated to: *" . strtoupper($newStatus) . "*";

        $message = "🐼 *FOODHUB ORDER UPDATE* 🛵\n"
                 . "━━━━━━━━━━━━━━━━━━━━━\n"
                 . "Hi *{$customerName}*,\n\n"
                 . "{$statusText}\n\n"
                 . "📍 *Track Live on Map:*\n{$trackUrl}\n"
                 . "━━━━━━━━━━━━━━━━━━━━━\n"
                 . "FoodHub Express • Fast & Fresh Delivery";

        return self::sendMessage($phone, $message);
    }

    /**
     * Alert Store Owner when a New Order Arrives
     */
    public static function notifyAdminNewOrder(array $order, array $items = []): array {
        $adminPhone = WHATSAPP_ADMIN_PHONE;
        if (empty($adminPhone)) return ['success' => false];

        $orderNo = $order['order_number'] ?? 'ORD-XXXX';
        $customerName = $order['customer_name'] ?? 'Customer';
        $total = number_format((float)($order['total_amount'] ?? 0), 2);
        $restaurant = $order['restaurant_name'] ?? 'Restaurant';
        $address = $order['delivery_address'] ?? 'Address';
        $adminUrl = APP_URL . "/admin/orders.php?search={$orderNo}";

        $message = "🔔 *NEW FOOD ORDER ALERT!* 🔔\n"
                 . "━━━━━━━━━━━━━━━━━━━━━\n"
                 . "An order has just been placed!\n\n"
                 . "📋 *Order:* `{$orderNo}`\n"
                 . "👤 *Customer:* {$customerName} (📞 {$order['customer_phone']})\n"
                 . "🍳 *Kitchen:* {$restaurant}\n"
                 . "📍 *Address:* {$address}\n"
                 . "💰 *Total Amount:* *\$" . $total . "*\n\n"
                 . "👉 *Manage in Admin Dashboard:*\n{$adminUrl}";

        return self::sendMessage($adminPhone, $message);
    }
}
