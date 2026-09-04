<?php
require_once __DIR__ . '/../data/db.php';
$db = getDB();

$statusFilter = trim($_GET['status'] ?? 'all');
$search = trim($_GET['search'] ?? '');

$where = [];
$params = [];

if ($statusFilter !== 'all' && !empty($statusFilter)) {
    $where[] = "`order_status` = :status";
    $params[':status'] = $statusFilter;
}

if (!empty($search)) {
    $where[] = "(`order_number` LIKE :s OR `customer_name` LIKE :s2 OR `customer_phone` LIKE :s3 OR `restaurant_name` LIKE :s4)";
    $params[':s'] = "%{$search}%";
    $params[':s2'] = "%{$search}%";
    $params[':s3'] = "%{$search}%";
    $params[':s4'] = "%{$search}%";
}

$sql = "SELECT * FROM `orders`";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY `created_at` DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Counts for tabs
$counts = [
    'all' => (int)$db->query("SELECT COUNT(*) FROM `orders`")->fetchColumn(),
    'placed' => (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE `order_status` = 'placed'")->fetchColumn(),
    'confirmed' => (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE `order_status` = 'confirmed'")->fetchColumn(),
    'preparing' => (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE `order_status` = 'preparing'")->fetchColumn(),
    'on_way' => (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE `order_status` = 'on_way'")->fetchColumn(),
    'delivered' => (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE `order_status` = 'delivered'")->fetchColumn()
];

require_once __DIR__ . '/header.php';
?>

<!-- Filter & Search Bar -->
<div style="background: white; padding: 18px 24px; border-radius: var(--radius-md); border: 1px solid var(--border); margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <!-- Status Tabs -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="orders.php?status=all" class="filter-chip <?= $statusFilter === 'all' ? 'active' : '' ?>">All (<?= $counts['all'] ?>)</a>
            <a href="orders.php?status=placed" class="filter-chip <?= $statusFilter === 'placed' ? 'active' : '' ?>">Placed (<?= $counts['placed'] ?>)</a>
            <a href="orders.php?status=confirmed" class="filter-chip <?= $statusFilter === 'confirmed' ? 'active' : '' ?>">Confirmed (<?= $counts['confirmed'] ?>)</a>
            <a href="orders.php?status=preparing" class="filter-chip <?= $statusFilter === 'preparing' ? 'active' : '' ?>">🍳 Cooking (<?= $counts['preparing'] ?>)</a>
            <a href="orders.php?status=on_way" class="filter-chip <?= $statusFilter === 'on_way' ? 'active' : '' ?>">🛵 On Way (<?= $counts['on_way'] ?>)</a>
            <a href="orders.php?status=delivered" class="filter-chip <?= $statusFilter === 'delivered' ? 'active' : '' ?>">🎉 Delivered (<?= $counts['delivered'] ?>)</a>
        </div>

        <!-- Search input -->
        <form method="GET" action="orders.php" style="display: flex; gap: 8px;">
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search Order # or Name..." style="padding: 8px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.88rem; outline: none;">
            <button type="submit" class="apply-btn" style="padding: 8px 16px; background: var(--secondary);">Search</button>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="data-table-card">
    <div style="padding: 16px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--secondary);">Customer Orders (<?= count($orders) ?>)</h3>
        <span style="font-size: 0.85rem; color: var(--text-muted);">Changes instantly update customer tracking in real-time</span>
    </div>

    <?php if (empty($orders)): ?>
        <div style="padding: 48px; text-align: center; color: var(--text-muted);">
            <div style="font-size: 3rem; margin-bottom: 8px;">📋</div>
            <h4 style="font-size: 1.2rem; font-weight: 800; color: var(--secondary);">No orders found matching criteria</h4>
            <p style="font-size: 0.88rem; margin-top: 4px;">Try selecting a different status filter or clear your search.</p>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Placed Date</th>
                        <th>Customer</th>
                        <th>Delivery Address</th>
                        <th>Restaurant</th>
                        <th>Total</th>
                        <th>Status Control</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td style="font-weight: 800; font-family: monospace; color: var(--primary);">
                                <?= htmlspecialchars($ord['order_number']) ?>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--text-muted);">
                                <?= date('M d, Y h:i A', strtotime($ord['created_at'])) ?>
                            </td>
                            <td>
                                <div style="font-weight: 700;"><?= htmlspecialchars($ord['customer_name']) ?></div>
                                <div style="font-size: 0.78rem; color: var(--text-muted);">📞 <?= htmlspecialchars($ord['customer_phone']) ?></div>
                            </td>
                            <td style="max-width: 200px; font-size: 0.84rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($ord['delivery_address']) ?>">
                                📍 <?= htmlspecialchars($ord['delivery_address']) ?>
                            </td>
                            <td style="font-weight: 600; color: var(--secondary);">
                                <?= htmlspecialchars($ord['restaurant_name']) ?>
                            </td>
                            <td style="font-weight: 800; font-size: 0.95rem; color: var(--primary);">
                                $<?= number_format($ord['total_amount'], 2) ?>
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
                                <div style="display: flex; gap: 6px;">
                                    <button type="button" class="btn-action-pill" style="background: var(--primary-light); color: var(--primary);" onclick="viewOrderReceipt('<?= htmlspecialchars($ord['order_number']) ?>')">
                                        Receipt
                                    </button>
                                    <a href="../track.php?order_number=<?= htmlspecialchars($ord['order_number']) ?>" target="_blank" class="btn-action-pill" style="background: #f1f5f9; color: var(--secondary);">
                                        GPS Map ➔
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
