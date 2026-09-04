<!-- Slide-over Cart Overlay -->
<div class="cart-overlay" id="cartOverlay"></div>

<!-- Slide-over Cart Drawer -->
<aside class="cart-drawer" id="cartDrawer">
    <div class="cart-header">
        <div>
            <h3>Your Food Cart</h3>
            <div id="cartRestaurantTitle" style="font-size: 0.84rem; color: var(--text-muted); font-weight: 600;"></div>
        </div>
        <button type="button" class="close-btn close-cart-btn">&times;</button>
    </div>

    <div class="cart-body" id="cartDrawerBody">
        <!-- Rendered dynamically by cart.js -->
    </div>

    <div class="cart-footer" id="cartDrawerFooter" style="display: none;">
        <!-- Voucher Input -->
        <div class="voucher-input-group">
            <input type="text" id="cartCouponInput" placeholder="Promo Code (e.g. WELCOME50)">
            <button type="button" class="apply-btn" id="applyCouponBtn">Apply</button>
        </div>

        <!-- Bill Breakdown -->
        <div class="bill-row">
            <span>Subtotal</span>
            <span id="cartSubtotalValue">$0.00</span>
        </div>
        <div class="bill-row">
            <span>Standard Delivery</span>
            <span id="cartDeliveryFeeValue">$1.99</span>
        </div>
        <div class="bill-row">
            <span>Service & Platform Fee</span>
            <span id="cartPlatformFeeValue">$0.50</span>
        </div>
        <div class="bill-row discount-row" id="cartDiscountRow" style="display: none;">
            <span>Voucher Discount</span>
            <span id="cartDiscountValue">-$0.00</span>
        </div>
        <div class="bill-row total-row">
            <span>Total to Pay</span>
            <span id="cartTotalValue">$0.00</span>
        </div>

        <button type="button" class="checkout-btn" id="proceedCheckoutBtn">
            <span>Proceed to Checkout</span>
            <span>➔</span>
        </button>
    </div>
</aside>

<!-- Checkout Modal -->
<div class="modal-backdrop" id="checkoutModal">
    <div class="modal-card">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--secondary);">Complete Your Order</h3>
            <button type="button" class="close-btn" onclick="document.getElementById('checkoutModal').classList.remove('active')">&times;</button>
        </div>

        <form id="checkoutOrderForm" style="display: flex; flex-direction: column; flex: 1; overflow-y: auto;">
            <div class="modal-body">
                <!-- Contact Details -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 6px;">Your Name *</label>
                    <input type="text" id="checkoutCustomerName" required placeholder="e.g. Sarah Jenkins" style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 6px;">Phone Number *</label>
                        <input type="tel" id="checkoutCustomerPhone" required placeholder="+1 555-0192" style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 6px;">Email (Receipt)</label>
                        <input type="email" id="checkoutCustomerEmail" placeholder="sarah@example.com" style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                    </div>
                </div>

                <!-- Delivery Address -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 6px;">Delivery Street Address *</label>
                    <input type="text" id="checkoutDeliveryAddress" required placeholder="422 Maple Street, Floor 3, Apt 12" style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                </div>

                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 6px;">Rider Instructions (Optional)</label>
                    <input type="text" id="checkoutInstructions" placeholder="e.g. Please leave at door, don't ring bell" style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                </div>

                <!-- Payment Method -->
                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 8px;">Payment Method</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <label style="border: 1.5px solid var(--border); padding: 10px 14px; border-radius: var(--radius-sm); display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="payment_method" value="cash_on_delivery" checked>
                            <span style="font-size: 0.88rem; font-weight: 700;">💵 Cash on Del</span>
                        </label>
                        <label style="border: 1.5px solid var(--border); padding: 10px 14px; border-radius: var(--radius-sm); display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="payment_method" value="card_online">
                            <span style="font-size: 0.88rem; font-weight: 700;">💳 Card / Wallet</span>
                        </label>
                    </div>
                </div>

                <!-- Order Summary Items preview -->
                <div style="background: var(--bg-surface); padding: 14px; border-radius: var(--radius-sm); margin-bottom: 12px;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">Order Summary</div>
                    <div id="checkoutSummaryItems"></div>
                </div>
            </div>

            <div class="modal-footer">
                <div style="flex: 1;">
                    <div style="font-size: 0.82rem; color: var(--text-muted);">Total Amount</div>
                    <div id="checkoutModalTotal" style="font-size: 1.35rem; font-weight: 800; color: var(--primary);">$0.00</div>
                </div>
                <button type="submit" class="checkout-btn" style="width: auto; padding: 12px 28px; margin-top: 0;">
                    Place Order Now ➔
                </button>
            </div>
        </form>
    </div>
</div>
