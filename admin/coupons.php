<?php
require_once __DIR__ . '/../data/db.php';
$db = getDB();

$coupons = $db->query("SELECT * FROM `coupons` ORDER BY `id` DESC")->fetchAll();

require_once __DIR__ . '/header.php';
?>

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 28px; align-items: start;">
    <!-- Coupons List -->
    <div class="data-table-card">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--border);">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--secondary);">Active Promotional Vouchers (<?= count($coupons) ?>)</h3>
            <p style="font-size: 0.84rem; color: var(--text-muted);">Manage customer discount campaigns and coupon rules</p>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Title & Description</th>
                        <th>Discount Value</th>
                        <th>Min Order</th>
                        <th>Badge</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $c): ?>
                        <tr>
                            <td style="font-family: monospace; font-weight: 800; font-size: 1rem; color: var(--primary);">
                                <?= htmlspecialchars($c['code']) ?>
                            </td>
                            <td>
                                <div style="font-weight: 700;"><?= htmlspecialchars($c['title']) ?></div>
                                <div style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($c['description']) ?></div>
                            </td>
                            <td style="font-weight: 800; color: #059669;">
                                <?= $c['discount_type'] === 'percentage' ? (int)$c['discount_value'] . '% OFF' : '$' . number_format($c['discount_value'], 2) . ' FLAT' ?>
                            </td>
                            <td style="font-weight: 600;">
                                $<?= number_format($c['min_order_amount'], 2) ?>
                            </td>
                            <td>
                                <span class="badge-tag" style="position: static; background: var(--primary-light); color: var(--primary); font-size: 0.72rem;">
                                    <?= htmlspecialchars($c['badge'] ?: 'DEAL') ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn-action-pill" style="background: #fee2e2; color: #b91c1c;" onclick="deleteCoupon(<?= $c['id'] ?>)">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Coupon Card -->
    <div style="background: white; border-radius: var(--radius-md); border: 1px solid var(--border); padding: 24px; box-shadow: var(--shadow-sm);">
        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--secondary); margin-bottom: 14px;">➕ Create New Voucher</h3>
        <form id="createCouponForm" onsubmit="submitCoupon(event)">
            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 4px;">Promo Code *</label>
                <input type="text" id="cpnCode" required placeholder="e.g. FLASH30" style="width: 100%; padding: 8px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.88rem; text-transform: uppercase; font-weight: 800;">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 4px;">Title *</label>
                <input type="text" id="cpnTitle" required placeholder="e.g. 30% Flash Weekend Discount" style="width: 100%; padding: 8px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.88rem;">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 4px;">Description</label>
                <textarea id="cpnDesc" rows="2" placeholder="Save 30% up to $10 on gourmet meals..." style="width: 100%; padding: 8px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.85rem;"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 4px;">Type</label>
                    <select id="cpnType" style="width: 100%; padding: 8px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.88rem; font-weight: 700;">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed ($)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 4px;">Discount Value *</label>
                    <input type="number" step="0.5" id="cpnValue" required placeholder="30" style="width: 100%; padding: 8px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.88rem;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 4px;">Min Order ($)</label>
                    <input type="number" step="1" id="cpnMinOrder" placeholder="15" style="width: 100%; padding: 8px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.88rem;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 4px;">Badge</label>
                    <input type="text" id="cpnBadge" placeholder="FLASH DEAL" style="width: 100%; padding: 8px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.88rem;">
                </div>
            </div>

            <button type="submit" class="checkout-btn" style="width: 100%; margin-top: 0; padding: 10px;">
                Create Voucher ➔
            </button>
        </form>
    </div>
</div>

<script>
function submitCoupon(e) {
    e.preventDefault();
    const payload = {
        code: document.getElementById('cpnCode').value.trim(),
        title: document.getElementById('cpnTitle').value.trim(),
        description: document.getElementById('cpnDesc').value.trim(),
        discount_type: document.getElementById('cpnType').value,
        discount_value: parseFloat(document.getElementById('cpnValue').value),
        min_order_amount: parseFloat(document.getElementById('cpnMinOrder').value) || 0,
        badge: document.getElementById('cpnBadge').value.trim() || 'SPECIAL'
    };

    fetch('api.php?action=add_coupon', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAdminToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 800);
        } else {
            showAdminToast(data.error || 'Failed to create coupon', 'error');
        }
    });
}

function deleteCoupon(id) {
    if (confirm('Delete this coupon code?')) {
        fetch('api.php?action=delete_coupon', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAdminToast('Coupon deleted', 'success');
                setTimeout(() => window.location.reload(), 800);
            }
        });
    }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
