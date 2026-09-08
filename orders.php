<?php
require_once __DIR__ . '/data/db.php';
$db = getDB();

$search = trim($_GET['search'] ?? '');
$orders = [];

if (!empty($search)) {
    $stmt = $db->prepare("SELECT * FROM `orders` WHERE `order_number` LIKE ? OR `customer_phone` LIKE ? OR `customer_name` LIKE ? ORDER BY `created_at` DESC LIMIT 20");
    $stmt->execute(["%{$search}%", "%{$search}%", "%{$search}%"]);
    $orders = $stmt->fetchAll();
} else {
    // Default show recent orders
    $orders = $db->query("SELECT * FROM `orders` ORDER BY `created_at` DESC LIMIT 10")->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top: 28px; padding-bottom: 60px;">
    <!-- Orders Banner -->
    <div style="background: linear-gradient(135deg, #1e1e2d 0%, #2b2d42 100%); color: white; padding: 36px; border-radius: var(--radius-lg); margin-bottom: 32px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="max-width: 600px;">
            <span class="hero-tag" style="background: rgba(255, 43, 133, 0.3); color: #ff6b9d;">🛍️ Customer Order Hub</span>
            <h1 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 8px;">Your Orders & Live Tracking</h1>
            <p style="opacity: 0.9; font-size: 1rem;">Track your ongoing deliveries in real-time, view order receipts, or reorder your favorite meals with a single click.</p>
        </div>
        <div>
            <a href="menu.php" class="checkout-btn" style="width: auto; margin-top: 0; background: var(--primary);">
                <span>🍔 Order Fresh Food</span>
            </a>
        </div>
    </div>

    <!-- Search / Lookup Order -->
    <div class="filter-bar" style="margin-bottom: 28px;">
        <form method="GET" action="orders.php" style="display: flex; gap: 12px; flex-wrap: wrap; width: 100%; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; flex: 1;">
                <div style="position: relative; flex: 1; max-width: 420px;">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Order # (e.g. ORD-XXXX) or Phone..." style="width: 100%; padding: 10px 14px 10px 36px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem; outline: none;">
                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;">🔍</span>
                </div>
                <button type="submit" class="apply-btn" style="padding: 10px 20px; background: var(--secondary);">Find Order</button>
            </div>

            <div style="font-size: 0.88rem; color: var(--text-muted); font-weight: 700;">
                Showing <?= count($orders) ?> orders
            </div>
        </form>
    </div>

    <!-- Orders List -->
    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: var(--radius-lg); border: 1px dashed var(--border);">
            <div style="font-size: 3.5rem; margin-bottom: 12px;">🛍️</div>
            <h3 style="font-weight: 800; font-size: 1.3rem; margin-bottom: 6px; color: var(--secondary);">No orders found</h3>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px;">You haven't placed any orders yet, or no orders matched your search.</p>
            <a href="menu.php" class="checkout-btn" style="width: auto; margin: 0 auto; display: inline-flex; padding: 12px 28px;">Browse Menu & Order Now</a>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 18px;">
            <?php foreach ($orders as $ord): ?>
                <?php
                    $statusMap = [
                        'placed' => ['label' => 'Order Placed 📥', 'color' => '#0369a1', 'bg' => '#e0f2fe'],
                        'confirmed' => ['label' => 'Confirmed ✅', 'color' => '#92400e', 'bg' => '#fef3c7'],
                        'preparing' => ['label' => 'Cooking in Kitchen 🍳', 'color' => '#854d0e', 'bg' => '#fef08a'],
                        'on_way' => ['label' => 'Rider on the Way 🛵', 'color' => '#6b21a8', 'bg' => '#ede9fe'],
                        'delivered' => ['label' => 'Delivered 🎉', 'color' => '#166534', 'bg' => '#dcfce7'],
                        'cancelled' => ['label' => 'Cancelled ❌', 'color' => '#991b1b', 'bg' => '#fee2e2']
                    ];
                    $st = $statusMap[$ord['order_status']] ?? ['label' => ucfirst($ord['order_status']), 'color' => '#334155', 'bg' => '#f1f5f9'];
                ?>
                <div style="background: white; border-radius: var(--radius-md); border: 1px solid var(--border); padding: 22px; box-shadow: var(--shadow-sm); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                            <span style="font-family: monospace; font-weight: 800; font-size: 1.15rem; color: var(--primary);">
                                <?= htmlspecialchars($ord['order_number']) ?>
                            </span>
                            <span style="background: <?= $st['bg'] ?>; color: <?= $st['color'] ?>; font-weight: 800; font-size: 0.78rem; padding: 3px 10px; border-radius: var(--radius-full); text-transform: uppercase;">
                                <?= $st['label'] ?>
                            </span>
                        </div>

                        <div style="font-weight: 700; font-size: 1.05rem; color: var(--secondary);">
                            <?= htmlspecialchars($ord['restaurant_name']) ?>
                        </div>
                        <div style="font-size: 0.84rem; color: var(--text-muted); margin-top: 4px;">
                            📍 <?= htmlspecialchars($ord['delivery_address']) ?> • 📅 <?= date('M d, Y • h:i A', strtotime($ord['created_at'])) ?>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                        <div style="text-align: right;">
                            <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Paid</div>
                            <div style="font-size: 1.3rem; font-weight: 800; color: var(--primary);">$<?= number_format($ord['total_amount'], 2) ?></div>
                        </div>

                        <div style="display: flex; gap: 8px;">
                            <a href="track.php?order_number=<?= htmlspecialchars($ord['order_number']) ?>" class="checkout-btn" style="width: auto; margin-top: 0; padding: 10px 18px; font-size: 0.88rem;">
                                <span>🛵 Track Live</span>
                            </a>
                            <a href="https://wa.me/923216793596?text=<?= urlencode("Hi, I am inquiring about my FoodHub Order " . $ord['order_number']) ?>" target="_blank" class="contact-pill-btn" style="background: #25d366; color: white;">
                                💬 WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
