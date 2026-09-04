<?php
/**
 * Reviews Submission API
 * POST /api/reviews.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../data/db.php';

try {
    $db = getDB();

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $restaurantId = (int)($input['restaurant_id'] ?? 0);
    $customerName = trim($input['customer_name'] ?? 'Happy Foodie');
    $rating = max(1, min(5, (int)($input['rating'] ?? 5)));
    $comment = trim($input['comment'] ?? '');

    if ($restaurantId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Restaurant ID is required']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO `reviews` (`restaurant_id`, `customer_name`, `rating`, `comment`) VALUES (?, ?, ?, ?)");
    $stmt->execute([$restaurantId, $customerName, $rating, $comment]);

    // Recalculate restaurant rating average
    $stmtAvg = $db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM `reviews` WHERE `restaurant_id` = ?");
    $stmtAvg->execute([$restaurantId]);
    $stats = $stmtAvg->fetch();

    $newAvg = round((float)$stats['avg_rating'], 1);
    $totalCount = (int)$stats['total_reviews'];

    $stmtUpd = $db->prepare("UPDATE `restaurants` SET `rating` = ?, `rating_count` = `rating_count` + 1 WHERE `id` = ?");
    $stmtUpd->execute([$newAvg, $restaurantId]);

    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your rating!',
        'new_rating' => $newAvg,
        'total_reviews' => $totalCount
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
