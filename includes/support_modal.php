<!-- Location Picker Modal -->
<div class="modal-backdrop" id="locationModal">
    <div class="modal-card" style="max-width: 440px;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--secondary);">📍 Choose Delivery Location</h3>
            <button type="button" class="close-btn" onclick="document.getElementById('locationModal').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body">
            <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 14px;">Enter your street address or neighborhood to view nearby restaurants and fast delivery times.</p>
            <input type="text" id="userAddressInput" placeholder="e.g. Downtown, 42 Flavor Avenue" style="width: 100%; padding: 12px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.92rem; outline: none; margin-bottom: 12px;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button type="button" class="filter-chip" onclick="document.getElementById('userAddressInput').value = 'Downtown Central District'">Downtown</button>
                <button type="button" class="filter-chip" onclick="document.getElementById('userAddressInput').value = 'Little Italy Culinary Row'">Little Italy</button>
                <button type="button" class="filter-chip" onclick="document.getElementById('userAddressInput').value = 'East University Campus'">University Campus</button>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="checkout-btn" id="saveLocationBtn" style="margin-top: 0; width: 100%;">Confirm Location</button>
        </div>
    </div>
</div>

<!-- Floating Customer Support Widget -->
<button type="button" class="floating-support-btn" id="floatingSupportBtn" title="Need help? Ask our 24/7 Assistant">
    💬
</button>

<div class="support-widget-panel" id="supportWidgetPanel">
    <div class="support-panel-header">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 1.2rem;">🐼</span>
            <div>
                <div style="font-weight: 800; font-size: 0.95rem;">FoodHub Support</div>
                <div style="font-size: 0.75rem; opacity: 0.9;">Online • Instant Help</div>
            </div>
        </div>
        <button type="button" id="closeSupportWidgetBtn" style="color: white; font-size: 1.4rem; padding: 0 4px;">&times;</button>
    </div>

    <div class="support-messages-container" id="supportMessagesContainer">
        <div class="bot-msg">
            Hi there! 👋 How can I help you today? You can ask about order tracking, active vouchers (e.g. <b>WELCOME50</b>), dietary guides, or refund policies!
        </div>
    </div>

    <form class="support-input-area" id="supportChatForm">
        <input type="text" id="supportChatInput" placeholder="Type your question..." autocomplete="off">
        <button type="submit" class="apply-btn" style="padding: 6px 14px; font-size: 0.85rem;">Send</button>
    </form>
</div>
