<!-- Dish Customizer Modal -->
<div class="modal-backdrop" id="dishCustomizerModal">
    <div class="modal-card">
        <div class="modal-img-header">
            <img id="modalDishImage" src="" alt="Dish">
            <button type="button" class="modal-close-icon">&times;</button>
        </div>

        <div class="modal-body">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
                <h3 id="modalDishTitle" style="font-size: 1.3rem; font-weight: 800; color: var(--secondary);">Dish Name</h3>
                <span id="modalDishCalories" class="meta-pill" style="background: #fef2f2; color: #b91c1c; font-size: 0.75rem;">🔥 0 kcal</span>
            </div>
            <p id="modalDishDesc" style="font-size: 0.88rem; color: var(--text-muted); margin-top: 6px; line-height: 1.45;">Dish Description</p>

            <!-- Dynamic Options Container (Sizes, Sauces, Extras) -->
            <div id="modalOptionsContainer"></div>

            <!-- Special Instructions Note -->
            <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border);">
                <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 6px;">Special Instructions for Chef</label>
                <input type="text" id="modalSpecialInstructions" placeholder="e.g. Extra napkins, sauce on the side, no cutlery..." style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.88rem;">
            </div>
        </div>

        <div class="modal-footer">
            <!-- Quantity selector -->
            <div class="qty-control" style="padding: 6px 12px;">
                <button type="button" class="qty-btn" id="dishModalQtyDec" style="width: 28px; height: 28px;">-</button>
                <span class="qty-count" id="modalDishQtyDisplay" style="font-size: 1rem; min-width: 24px;">1</span>
                <button type="button" class="qty-btn" id="dishModalQtyInc" style="width: 28px; height: 28px;">+</button>
            </div>

            <!-- Add to cart button with live dynamic price -->
            <button type="button" class="checkout-btn" id="modalAddToCartBtn" style="margin-top: 0; flex: 1;">
                <span>Add to Cart</span> • <span>$0.00</span>
            </button>
        </div>
    </div>
</div>
