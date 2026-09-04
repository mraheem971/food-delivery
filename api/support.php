<?php
/**
 * Customer Support & Knowledge Assistant API
 * GET /api/support.php?query=...
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$faqs = [
    [
        'category' => 'Orders & Delivery',
        'question' => 'How can I track my food order in real-time?',
        'answer' => 'As soon as your order is confirmed, you will be redirected to the Live Tracking screen where you can watch your rider on the interactive map with estimated countdown minutes.',
        'keywords' => ['track', 'status', 'live', 'where', 'late', 'map', 'rider']
    ],
    [
        'category' => 'Orders & Delivery',
        'question' => 'What is the average delivery time?',
        'answer' => 'Most restaurant orders arrive within 20 to 35 minutes depending on the kitchen preparation time and your distance.',
        'keywords' => ['time', 'how long', 'fast', 'duration', 'minutes']
    ],
    [
        'category' => 'Coupons & Savings',
        'question' => 'How do I apply discount voucher codes?',
        'answer' => 'Open your Cart Drawer, look for the "Apply Voucher" field, type or click any code from the Deals hub (e.g. WELCOME50, FREEDEL, BURGER20) and click Apply!',
        'keywords' => ['coupon', 'promo', 'discount', 'voucher', 'code', 'save', 'welcome50', 'freedel']
    ],
    [
        'category' => 'Dietary & Allergens',
        'question' => 'How do I filter for Vegetarian, Vegan, Halal, or Gluten-Free meals?',
        'answer' => 'Use the dietary filter pill buttons on the homepage or restaurant menu tabs. Each dish displays badge tags like Halal, Vegan, GF, and Spicy levels.',
        'keywords' => ['halal', 'vegan', 'vegetarian', 'gluten', 'allergen', 'diet', 'spicy', 'health']
    ],
    [
        'category' => 'Payments & Refunds',
        'question' => 'What payment methods do you accept?',
        'answer' => 'We accept Cash on Delivery (COD), Credit/Debit Cards (Visa, Mastercard, Amex), and Mobile Wallet checkout.',
        'keywords' => ['payment', 'cash', 'cod', 'card', 'credit', 'wallet', 'pay']
    ],
    [
        'category' => 'Payments & Refunds',
        'question' => 'How do refunds work if an item is missing or incorrect?',
        'answer' => 'If an item is missing or damaged, tap "Need Help with this Order" on your tracking page or message our 24/7 support assistant for an instant credit refund.',
        'keywords' => ['refund', 'missing', 'cancel', 'wrong', 'help', 'problem']
    ]
];

$query = strtolower(trim($_GET['query'] ?? ''));

if (!empty($query)) {
    $results = [];
    foreach ($faqs as $faq) {
        $matched = false;
        if (str_contains(strtolower($faq['question']), $query) || str_contains(strtolower($faq['answer']), $query)) {
            $matched = true;
        } else {
            foreach ($faq['keywords'] as $kw) {
                if (str_contains($query, $kw) || str_contains($kw, $query)) {
                    $matched = true;
                    break;
                }
            }
        }
        if ($matched) {
            $results[] = $faq;
        }
    }

    // Default friendly bot answer if no exact match
    $botReply = null;
    if (!empty($results)) {
        $botReply = $results[0]['answer'];
    } else {
        $botReply = "I'm here to help! You can ask about order tracking, delivery times, payment methods, dietary/allergen info, or vouchers like 'WELCOME50' for 50% off!";
    }

    echo json_encode([
        'success' => true,
        'reply' => $botReply,
        'faqs' => $results
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'faqs' => $faqs
]);
