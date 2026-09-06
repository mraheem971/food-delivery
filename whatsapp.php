<?php
require_once __DIR__ . '/data/db.php';
require_once __DIR__ . '/data/whatsapp.php';

$sessionStatus = WhatsAppService::getSessionStatus();
$isConnected = isset($sessionStatus['status']) && $sessionStatus['status'] === 'connected';
$connectedPhone = $sessionStatus['user']['phone'] ?? '923216793596';

require_once __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top: 28px; padding-bottom: 60px;">
    <!-- WhatsApp Hero -->
    <div style="background: linear-gradient(135deg, #128c7e 0%, #075e54 100%); color: white; padding: 40px 36px; border-radius: var(--radius-lg); margin-bottom: 32px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="max-width: 600px;">
            <span class="hero-tag" style="background: rgba(255, 255, 255, 0.2); color: white;">💬 WhatsApp Food Automation</span>
            <h1 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 8px;">Order & Track on WhatsApp</h1>
            <p style="opacity: 0.95; font-size: 1rem;">Get instant live order receipts, kitchen cooking alerts, delivery rider GPS updates, and 24/7 support directly on your WhatsApp.</p>
        </div>
        <div>
            <a href="https://wa.me/<?= htmlspecialchars($connectedPhone) ?>?text=<?= urlencode("Hi FoodHub! I want to check today's special menu deals and place an order.") ?>" target="_blank" class="checkout-btn" style="background: #25d366; color: white; box-shadow: 0 8px 24px rgba(37, 211, 102, 0.4); margin-top: 0; padding: 14px 28px; font-size: 1rem;">
                <span>💬 Chat on WhatsApp Now ➔</span>
            </a>
        </div>
    </div>

    <!-- Quick Services Grid -->
    <div class="section-header">
        <h2 class="section-title">🌟 WhatsApp Features & Services</h2>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div class="resource-card" style="background: white;">
            <div class="resource-icon" style="background: #e8f5e9; color: #2e7d32;">📋</div>
            <h4>Instant Order Receipts</h4>
            <p>Every time you place an order on FoodHub, our WhatsApp bot sends an itemized receipt with order total and delivery details.</p>
        </div>

        <div class="resource-card" style="background: white;">
            <div class="resource-icon" style="background: #e8f5e9; color: #2e7d32;">🛵</div>
            <h4>Live GPS Rider Updates</h4>
            <p>Receive live WhatsApp alerts as soon as your food is cooking in the kitchen and when the rider is speeding to your address.</p>
        </div>

        <div class="resource-card" style="background: white;">
            <div class="resource-icon" style="background: #e8f5e9; color: #2e7d32;">🎁</div>
            <h4>VIP Promo Codes</h4>
            <p>Message our WhatsApp bot with <code>DEALS</code> to receive exclusive weekly discount codes up to 50% off.</p>
        </div>

        <div class="resource-card" style="background: white;">
            <div class="resource-icon" style="background: #e8f5e9; color: #2e7d32;">👑</div>
            <h4>Owner Admin Controls</h4>
            <p>Restaurant owners can manage the WhatsApp Bot, QR code linker, and test sender in the Admin Panel.</p>
            <a href="admin/whatsapp.php" style="color: var(--primary); font-weight: 700; font-size: 0.88rem; margin-top: 8px; display: inline-block;">Go to Admin WhatsApp Hub ➔</a>
        </div>
    </div>

    <!-- Quick Message Tester / Inquiry Box -->
    <section style="background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); padding: 32px; box-shadow: var(--shadow-sm);">
        <div class="section-header">
            <h3 class="section-title">📱 Start a Quick WhatsApp Conversation</h3>
        </div>
        <p style="color: var(--text-muted); font-size: 0.92rem; margin-bottom: 20px;">Choose a quick topic below to connect directly with our automated food assistant or live rider:</p>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="https://wa.me/<?= htmlspecialchars($connectedPhone) ?>?text=<?= urlencode("Hi! What are the best restaurants open near me right now?") ?>" target="_blank" class="filter-chip" style="padding: 10px 18px; font-size: 0.9rem; background: #f0fdf4; border-color: #86efac; color: #166534;">
                🍔 Recommend Top Restaurants
            </a>
            <a href="https://wa.me/<?= htmlspecialchars($connectedPhone) ?>?text=<?= urlencode("Hi, please send me today's active discount coupon codes.") ?>" target="_blank" class="filter-chip" style="padding: 10px 18px; font-size: 0.9rem; background: #f0fdf4; border-color: #86efac; color: #166534;">
                🏷️ Get Today's Promo Vouchers
            </a>
            <a href="https://wa.me/<?= htmlspecialchars($connectedPhone) ?>?text=<?= urlencode("Hi! I need help with my food delivery order.") ?>" target="_blank" class="filter-chip" style="padding: 10px 18px; font-size: 0.9rem; background: #f0fdf4; border-color: #86efac; color: #166534;">
                🛵 Check Order Status
            </a>
            <a href="admin/whatsapp.php" class="filter-chip" style="padding: 10px 18px; font-size: 0.9rem; background: #1e1e2d; color: white;">
                ⚙️ Owner WhatsApp Admin Hub
            </a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
