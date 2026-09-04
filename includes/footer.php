<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="brand-logo" style="margin-bottom: 12px; color: white;">
                    <span class="logo-icon">🐼</span>
                    <span>Food<span style="color: #ff2b85;">Hub</span></span>
                </div>
                <p style="font-size: 0.88rem; color: #94a3b8; line-height: 1.5; margin-bottom: 16px;">
                    Your favorite local restaurants, gourmet kitchens, and fresh groceries delivered to your doorstep in minutes with lightning-fast live tracking.
                </p>
                <div style="display: flex; gap: 10px;">
                    <a href="deals.php" class="voucher-chip" style="font-size: 0.82rem; padding: 6px 12px; background: #334155; color: white;">
                        <span>🎁 50% Off First Order</span>
                    </a>
                </div>
            </div>

            <div class="footer-col">
                <h5>Discover</h5>
                <ul class="footer-links">
                    <li><a href="index.php">All Restaurants</a></li>
                    <li><a href="deals.php">Deals & Promo Codes</a></li>
                    <li><a href="help.php">Dietary & Allergen Guide</a></li>
                    <li><a href="index.php?category=burgers">Burgers & Fast Food</a></li>
                    <li><a href="index.php?category=pizza">Artisan Pizzas</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h5>Customer Resources</h5>
                <ul class="footer-links">
                    <li><a href="help.php">Help Center & FAQ</a></li>
                    <li><a href="help.php#refunds">Refund & Return Policy</a></li>
                    <li><a href="track.php">Track Existing Order</a></li>
                    <li><a href="help.php#nutrition">Calorie & Nutrition Info</a></li>
                    <li><a href="help.php#safety">Food Safety Standards</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h5>Get Exclusive Offers</h5>
                <p style="font-size: 0.86rem; color: #94a3b8; margin-bottom: 12px;">Sign up to get weekly secret discounts and chef specials.</p>
                <form onsubmit="event.preventDefault(); showToast('Subscribed to VIP culinary deals! 💌', 'success');" style="display: flex; gap: 6px;">
                    <input type="email" placeholder="Your email address" required style="padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid #475569; background: #0f172a; color: white; font-size: 0.88rem; flex: 1; outline: none;">
                    <button type="submit" class="apply-btn" style="background: var(--primary);">Join</button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <div>© <?= date('Y') ?> FoodHub Express Inc. All rights reserved. Inspired by modern food delivery experiences.</div>
            <div style="display: flex; gap: 16px;">
                <a href="#" style="color: #64748b;">Privacy Policy</a>
                <a href="#" style="color: #64748b;">Terms of Service</a>
                <a href="#" style="color: #64748b;">Security</a>
            </div>
        </div>
    </div>
</footer>

<!-- Include Modals & Drawers -->
<?php require_once __DIR__ . '/cart_drawer.php'; ?>
<?php require_once __DIR__ . '/dish_modal.php'; ?>
<?php require_once __DIR__ . '/support_modal.php'; ?>

<!-- Toast Container -->
<div class="toast-container"></div>

<!-- JavaScript Files -->
<script src="assets/js/app.js"></script>
<script src="assets/js/cart.js"></script>
<script src="assets/js/modal.js"></script>
<script src="assets/js/support.js"></script>

</body>
</html>
