<?php
require_once __DIR__ . '/data/db.php';
$db = getDB();

$coupons = $db->query("SELECT * FROM `coupons` ORDER BY `discount_value` DESC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top: 28px; padding-bottom: 60px;">
    <!-- Deals Hero -->
    <div style="background: linear-gradient(135deg, #1e1e2d 0%, #2b2d42 100%); color: white; padding: 40px 36px; border-radius: var(--radius-lg); margin-bottom: 32px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="max-width: 600px;">
            <span class="hero-tag" style="background: rgba(255, 43, 133, 0.3); color: #ff6b9d;">🎁 Customer Savings Hub</span>
            <h1 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 8px;">Exclusive Food Vouchers & Promo Codes</h1>
            <p style="opacity: 0.9; font-size: 1rem;">Copy any voucher below and apply it in your Cart Drawer to save on your meals every single day.</p>
        </div>
        <div>
            <div style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); padding: 18px 24px; border-radius: var(--radius-md); border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                <div style="font-size: 0.85rem; text-transform: uppercase; font-weight: 700; color: #ff6b9d;">Average Customer Savings</div>
                <div style="font-size: 2.2rem; font-weight: 800; color: white;">$12.50</div>
                <div style="font-size: 0.78rem; opacity: 0.8;">per order with active coupons</div>
            </div>
        </div>
    </div>

    <!-- Interactive Voucher Cards Grid -->
    <div class="section-header">
        <h2 class="section-title">Active Promo Vouchers (<?= count($coupons) ?>)</h2>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-bottom: 48px;">
        <?php foreach ($coupons as $cpn): ?>
            <div style="background: white; border-radius: var(--radius-md); border: 1px solid var(--border); padding: 22px; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: 0; width: 6px; height: 100%; background: var(--primary);"></div>
                
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <span class="badge-tag" style="position: static; background: var(--primary-light); color: var(--primary); font-weight: 800;"><?= htmlspecialchars($cpn['badge'] ?: 'DEAL') ?></span>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">Valid till <?= htmlspecialchars($cpn['expires_at'] ?: '2026-12-31') ?></span>
                    </div>
                    <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--secondary); margin-bottom: 6px;"><?= htmlspecialchars($cpn['title']) ?></h3>
                    <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.45; margin-bottom: 16px;"><?= htmlspecialchars($cpn['description']) ?></p>
                </div>

                <div style="background: var(--bg-surface); padding: 10px 14px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;">PROMO CODE</div>
                        <div style="font-family: monospace; font-size: 1.1rem; font-weight: 800; color: var(--primary);"><?= htmlspecialchars($cpn['code']) ?></div>
                    </div>
                    <button type="button" class="apply-btn copy-coupon-btn" data-code="<?= htmlspecialchars($cpn['code']) ?>" style="background: var(--primary); font-size: 0.82rem; padding: 6px 14px;">
                        Copy Code
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Interactive Savings Calculator Widget -->
    <section style="background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); padding: 32px; box-shadow: var(--shadow-sm);">
        <div class="section-header">
            <h3 class="section-title">🧮 Interactive Cart Savings Calculator</h3>
        </div>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px;">Enter your estimated cart subtotal to find the best voucher and calculate your immediate savings.</p>

        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 28px; align-items: center;">
            <div>
                <label style="display: block; font-size: 0.9rem; font-weight: 700; margin-bottom: 8px;">Estimated Order Subtotal ($)</label>
                <input type="number" id="calcSubtotalInput" value="35.00" min="5" step="1" style="width: 100%; padding: 12px 16px; border: 2px solid var(--primary); border-radius: var(--radius-sm); font-size: 1.2rem; font-weight: 800; outline: none;">
                <div style="display: flex; gap: 8px; margin-top: 10px;">
                    <button type="button" class="filter-chip" onclick="setCalcSubtotal(20)">$20</button>
                    <button type="button" class="filter-chip" onclick="setCalcSubtotal(35)">$35</button>
                    <button type="button" class="filter-chip" onclick="setCalcSubtotal(50)">$50</button>
                    <button type="button" class="filter-chip" onclick="setCalcSubtotal(75)">$75</button>
                </div>
            </div>

            <div id="calcResultsBox" style="background: var(--bg-surface); padding: 20px 24px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <div style="font-size: 0.82rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Best Recommended Deal</div>
                <div id="calcBestDealTitle" style="font-size: 1.2rem; font-weight: 800; color: var(--primary); margin: 4px 0;">50% OFF First Order (WELCOME50)</div>
                <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 0.95rem;">
                    <span>Original Subtotal:</span>
                    <span id="calcOriginalDisplay" style="font-weight: 700;">$35.00</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 4px; font-size: 0.95rem; color: var(--success); font-weight: 700;">
                    <span>Estimated Discount:</span>
                    <span id="calcDiscountDisplay">-$15.00</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 8px; padding-top: 8px; border-top: 1px dashed var(--border); font-size: 1.15rem; font-weight: 800; color: var(--secondary);">
                    <span>Discounted Total:</span>
                    <span id="calcFinalDisplay">$20.00</span>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
function setCalcSubtotal(val) {
    document.getElementById('calcSubtotalInput').value = val.toFixed(2);
    recalculateSavings();
}

function recalculateSavings() {
    const subtotal = parseFloat(document.getElementById('calcSubtotalInput').value) || 0;
    const originalEl = document.getElementById('calcOriginalDisplay');
    const discountEl = document.getElementById('calcDiscountDisplay');
    const finalEl = document.getElementById('calcFinalDisplay');
    const titleEl = document.getElementById('calcBestDealTitle');

    originalEl.textContent = `$${subtotal.toFixed(2)}`;

    let bestDiscount = 0;
    let bestCoupon = 'WELCOME50';

    // Check Welcome50
    let w50 = subtotal * 0.5;
    if (w50 > 15) w50 = 15;
    if (subtotal >= 15 && w50 > bestDiscount) {
        bestDiscount = w50;
        bestCoupon = 'WELCOME50 (50% OFF up to $15)';
    }

    // Check Feast10
    if (subtotal >= 40 && 10 > bestDiscount) {
        bestDiscount = 10;
        bestCoupon = 'FEAST10 ($10 Flat Discount)';
    }

    titleEl.textContent = bestCoupon;
    discountEl.textContent = `-$${bestDiscount.toFixed(2)}`;
    finalEl.textContent = `$${Math.max(0, subtotal - bestDiscount).toFixed(2)}`;
}

document.getElementById('calcSubtotalInput').addEventListener('input', recalculateSavings);
recalculateSavings();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
