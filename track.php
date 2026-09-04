<?php
require_once __DIR__ . '/data/db.php';
$db = getDB();

$orderNumber = trim($_GET['order_number'] ?? '');

// If empty, get the latest placed order as default or show lookup
if (empty($orderNumber)) {
    $latest = $db->query("SELECT `order_number` FROM `orders` ORDER BY `id` DESC LIMIT 1")->fetch();
    if ($latest) {
        $orderNumber = $latest['order_number'];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="container tracking-wrapper">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <span style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: uppercase;">Live GPS Order Tracking</span>
            <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--secondary);">Track Order: <span id="trackOrderNumber"><?= htmlspecialchars($orderNumber ?: 'ORD-SAMPLE') ?></span></h1>
        </div>
        
        <!-- Demo Status Controls for testing -->
        <div style="display: flex; gap: 10px;">
            <button type="button" id="demoAdvanceStatusBtn" class="contact-pill-btn" style="background: var(--primary-light); color: var(--primary); border-color: #fbcfe8;">
                ⚡ Demo: Advance Status Next Step
            </button>
        </div>
    </div>

    <?php if (empty($orderNumber)): ?>
        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: var(--radius-lg); border: 1px solid var(--border);">
            <div style="font-size: 3.5rem; margin-bottom: 12px;">🔍</div>
            <h2 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 8px;">No active order selected</h2>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px;">Enter your Order Number to check real-time rider location and kitchen status.</p>
            <form action="track.php" method="GET" style="display: flex; max-width: 380px; margin: 0 auto; gap: 8px;">
                <input type="text" name="order_number" placeholder="e.g. ORD-A1B2C3" required style="flex: 1; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem; text-transform: uppercase;">
                <button type="submit" class="apply-btn" style="background: var(--primary);">Track</button>
            </form>
        </div>
    <?php else: ?>
        <div class="track-grid">
            <!-- Left: Timeline, Map & Rider -->
            <div>
                <!-- Status & ETA Banner -->
                <div class="tracking-card">
                    <div class="track-status-header">
                        <div>
                            <h2 id="trackStatusTitle" style="font-size: 1.5rem; font-weight: 800; color: var(--secondary);">Order Confirmed!</h2>
                            <p id="trackStatusDesc" style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">The restaurant accepted your order and the kitchen is getting ready.</p>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.78rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Estimated Arrival</div>
                            <div class="eta-counter" id="trackEtaTimer">25-30 min</div>
                        </div>
                    </div>

                    <!-- 5-Step Progress Timeline -->
                    <div class="timeline-progress">
                        <div class="timeline-line-bg"></div>
                        <div class="timeline-line-fill" id="timelineProgressFill" style="width: 25%;"></div>

                        <div class="timeline-step completed" data-step="placed">
                            <div class="step-icon-circle">✓</div>
                            <span class="step-label">Placed</span>
                        </div>
                        <div class="timeline-step active" data-step="confirmed">
                            <div class="step-icon-circle">2</div>
                            <span class="step-label">Confirmed</span>
                        </div>
                        <div class="timeline-step" data-step="preparing">
                            <div class="step-icon-circle">3</div>
                            <span class="step-label">Cooking</span>
                        </div>
                        <div class="timeline-step" data-step="on_way">
                            <div class="step-icon-circle">4</div>
                            <span class="step-label">On Way</span>
                        </div>
                        <div class="timeline-step" data-step="delivered">
                            <div class="step-icon-circle">5</div>
                            <span class="step-label">Delivered</span>
                        </div>
                    </div>

                    <!-- Animated Delivery Map Canvas -->
                    <div class="map-canvas-container">
                        <canvas id="deliveryCanvas"></canvas>
                    </div>

                    <!-- Rider Info Card -->
                    <div class="rider-card">
                        <div class="rider-info">
                            <div class="rider-avatar">🛵</div>
                            <div>
                                <div style="font-weight: 800; font-size: 1rem; color: var(--secondary);">Rashid K. (Panda Rider)</div>
                                <div style="font-size: 0.82rem; color: var(--text-muted);">★ 4.9 Rating • 1,420 Deliveries • Honda 125</div>
                            </div>
                        </div>
                        <div class="rider-contact-btns">
                            <button type="button" class="contact-pill-btn" id="callRiderBtn">📞 Call Rider</button>
                            <a href="https://wa.me/923216793596?text=<?= urlencode("Hi Rider Rashid, I am inquiring about my FoodHub Order " . ($orderNumber ?: '')) ?>" target="_blank" class="contact-pill-btn" style="background: #25d366; color: white;">
                                💬 WhatsApp Rider
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Order Items & Delivery Summary -->
            <div>
                <div class="tracking-card">
                    <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 16px; color: var(--secondary);">Order Receipt</h3>

                    <div style="margin-bottom: 14px;">
                        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Restaurant</div>
                        <div id="trackRestaurantName" style="font-weight: 800; font-size: 1rem; color: var(--primary);">Panda Burger & Grill</div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Delivery To</div>
                        <div id="trackDeliveryAddress" style="font-weight: 600; font-size: 0.9rem; color: var(--text-main);">422 Flavor Avenue, Downtown</div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Payment</div>
                        <div id="trackPaymentMethod" style="font-weight: 600; font-size: 0.9rem; color: var(--text-main);">Cash on Delivery</div>
                    </div>

                    <div style="border-top: 1px solid var(--border); padding-top: 14px; margin-bottom: 14px;">
                        <div style="font-size: 0.82rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px;">ORDERED ITEMS</div>
                        <div id="trackOrderItemsList">
                            <!-- Items injected dynamically -->
                        </div>
                    </div>

                    <div style="border-top: 1px dashed var(--border); padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 800; font-size: 1.05rem;">Paid Total:</span>
                        <span id="trackTotalAmount" style="font-weight: 800; font-size: 1.25rem; color: var(--primary);">$0.00</span>
                    </div>

                    <div style="margin-top: 20px; padding: 12px; background: var(--bg-surface); border-radius: var(--radius-sm); font-size: 0.82rem; color: var(--text-muted); line-height: 1.4;">
                        Need help with this order? <a href="#" onclick="event.preventDefault(); document.getElementById('floatingSupportBtn').click();" style="color: var(--primary); font-weight: 700;">Chat with Support</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<script src="assets/js/tracker.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const orderNo = "<?= htmlspecialchars($orderNumber) ?>";
    if (orderNo) {
        window.activeTracker = new OrderTracker(orderNo);
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
