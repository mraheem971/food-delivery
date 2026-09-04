<?php
require_once __DIR__ . '/../data/db.php';
$db = getDB();

// 1. Calculate KPIs
$totalRevenue = (float)$db->query("SELECT COALESCE(SUM(total_amount), 0) FROM `orders` WHERE `order_status` != 'cancelled'")->fetchColumn();
$totalOrders = (int)$db->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();
$activeOrders = (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE `order_status` IN ('placed', 'confirmed', 'preparing', 'on_way')")->fetchColumn();
$totalDishes = (int)$db->query("SELECT COUNT(*) FROM `menu_items`")->fetchColumn();

// 2. Fetch Recent Orders
$recentOrders = $db->query("SELECT * FROM `orders` ORDER BY `created_at` DESC LIMIT 8")->fetchAll();

require_once __DIR__ . '/header.php';
?>

<!-- KPI Stat Cards -->
<div class="admin-stat-grid">
    <div class="admin-stat-card">
        <div>
            <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Sales Revenue</div>
            <div class="admin-stat-num">$<?= number_format($totalRevenue, 2) ?></div>
        </div>
        <div class="admin-stat-icon">💰</div>
    </div>

    <div class="admin-stat-card">
        <div>
            <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Active Kitchen Orders</div>
            <div class="admin-stat-num" style="color: #ff2b85;"><?= $activeOrders ?></div>
        </div>
        <div class="admin-stat-icon" style="background: #fff0f5;">🔥</div>
    </div>

    <div class="admin-stat-card">
        <div>
            <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Orders Processed</div>
            <div class="admin-stat-num"><?= $totalOrders ?></div>
        </div>
        <div class="admin-stat-icon">🛍️</div>
    </div>

    <div class="admin-stat-card">
        <div>
            <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Fresh Dishes in Menu</div>
            <div class="admin-stat-num"><?= $totalDishes ?></div>
        </div>
        <div class="admin-stat-icon">🍔</div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="data-table-card">
    <div style="padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border);">
        <div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--secondary);">Live Customer Orders</h3>
            <p style="font-size: 0.84rem; color: var(--text-muted);">Real-time kitchen order stream with instant status dispatch</p>
        </div>
        <a href="orders.php" class="btn-action-pill" style="background: var(--bg-surface); color: var(--secondary); border: 1px solid var(--border);">View All Orders ➔</a>
    </div>

    <?php if (empty($recentOrders)): ?>
        <div style="padding: 40px; text-align: center; color: var(--text-muted);">
            No customer orders placed yet. As soon as a customer orders, it will appear here in real-time!
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Restaurant</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Live Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $ord): ?>
                        <tr>
                            <td style="font-weight: 800; font-family: monospace; color: var(--primary);">
                                <?= htmlspecialchars($ord['order_number']) ?>
                            </td>
                            <td>
                                <div style="font-weight: 700;"><?= htmlspecialchars($ord['customer_name']) ?></div>
                                <div style="font-size: 0.78rem; color: var(--text-muted);">📞 <?= htmlspecialchars($ord['customer_phone']) ?></div>
                            </td>
                            <td style="font-weight: 600; color: var(--secondary);">
                                <?= htmlspecialchars($ord['restaurant_name']) ?>
                            </td>
                            <td style="font-weight: 800;">
                                $<?= number_format($ord['total_amount'], 2) ?>
                            </td>
                            <td>
                                <span style="font-size: 0.82rem; font-weight: 600;">
                                    <?= $ord['payment_method'] === 'cash_on_delivery' ? '💵 Cash' : '💳 Card' ?>
                                </span>
                            </td>
                            <td>
                                <select onchange="updateOrderStatus('<?= htmlspecialchars($ord['order_number']) ?>', this.value)" style="padding: 6px 10px; border-radius: 6px; font-weight: 700; font-size: 0.82rem; border: 1.5px solid var(--border);">
                                    <option value="placed" <?= $ord['order_status'] === 'placed' ? 'selected' : '' ?>>Placed 📥</option>
                                    <option value="confirmed" <?= $ord['order_status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed ✅</option>
                                    <option value="preparing" <?= $ord['order_status'] === 'preparing' ? 'selected' : '' ?>>Cooking 🍳</option>
                                    <option value="on_way" <?= $ord['order_status'] === 'on_way' ? 'selected' : '' ?>>On The Way 🛵</option>
                                    <option value="delivered" <?= $ord['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered 🎉</option>
                                    <option value="cancelled" <?= $ord['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled ❌</option>
                                </select>
                            </td>
                            <td>
                                <button type="button" class="btn-action-pill" style="background: var(--primary-light); color: var(--primary);" onclick="viewOrderReceipt('<?= htmlspecialchars($ord['order_number']) ?>')">
                                    View Receipt
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
