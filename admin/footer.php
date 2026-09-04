    </main>
</div>

<!-- 1. Add Fresh Dish Modal -->
<div class="modal-backdrop" id="adminAddDishModal">
    <div class="modal-card" style="max-width: 600px;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--secondary);">Add Fresh Dish to Menu</h3>
            <button type="button" class="close-btn" onclick="document.getElementById('adminAddDishModal').classList.remove('active')">&times;</button>
        </div>

        <form id="adminAddDishForm" onsubmit="submitNewDish(event)" style="display: flex; flex-direction: column; flex: 1; overflow-y: auto;">
            <div class="modal-body">
                <!-- Restaurant Selector -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 6px;">Select Restaurant *</label>
                    <select id="dishRestId" required style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                        <option value="1">Panda Burger & Grill</option>
                        <option value="2">Bella Napoli Pizzeria</option>
                        <option value="3">Tokyo Ramen & Dragon Roll</option>
                        <option value="4">Green Garden Superbowls</option>
                        <option value="5">El Fuego Cantina & Tacos</option>
                        <option value="6">Sweet Tooth Artisan Patisserie</option>
                    </select>
                </div>

                <!-- Name & Category -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 6px;">Dish Name *</label>
                        <input type="text" id="dishName" required placeholder="e.g. Crispy Honey Garlic Wings" style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 6px;">Category Name *</label>
                        <input type="text" id="dishCategory" required placeholder="e.g. Chef Specials, Starters" style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                    </div>
                </div>

                <!-- Price & Discount Price -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 6px;">Selling Price ($) *</label>
                        <input type="number" step="0.01" id="dishPrice" required placeholder="9.99" style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 6px;">Original Price (Strikethrough)</label>
                        <input type="number" step="0.01" id="dishOriginalPrice" placeholder="12.00" style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                    </div>
                </div>

                <!-- Image URL -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 6px;">Food Image URL</label>
                    <input type="url" id="dishImageUrl" placeholder="https://images.unsplash.com/..." style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                </div>

                <!-- Description -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 6px;">Dish Description</label>
                    <textarea id="dishDescription" rows="2" placeholder="Describe the ingredients, flavor, and texture..." style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.88rem;"></textarea>
                </div>

                <!-- Dietary Badges -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 8px;">Dietary & Special Tags</label>
                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 600;">
                            <input type="checkbox" id="dishIsPopular" checked> ⭐ Popular
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 600;">
                            <input type="checkbox" id="dishIsVegetarian"> 🥬 Vegetarian
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 600;">
                            <input type="checkbox" id="dishIsSpicy"> 🔥 Spicy
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 600;">
                            <input type="checkbox" id="dishIsGlutenFree"> 🌾 Gluten-Free
                        </label>
                    </div>
                </div>

                <!-- Add-on Customization Options -->
                <div style="border-top: 1px solid var(--border); padding-top: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label style="font-size: 0.88rem; font-weight: 800; color: var(--secondary);">Customization Options (Optional)</label>
                        <button type="button" class="apply-btn" onclick="addOptionRow()" style="padding: 4px 10px; font-size: 0.78rem;">+ Add Option</button>
                    </div>
                    <div id="dishOptionsList"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-action-pill" style="background: #e2e8f0; color: #475569; padding: 10px 18px;" onclick="document.getElementById('adminAddDishModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="checkout-btn" style="width: auto; margin-top: 0; padding: 10px 24px;">Publish Dish ➔</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Order Details Receipt Modal -->
<div class="modal-backdrop" id="adminOrderReceiptModal">
    <div class="modal-card" style="max-width: 520px;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--secondary);" id="receiptModalOrderNo">Order Details</h3>
            <button type="button" class="close-btn" onclick="document.getElementById('adminOrderReceiptModal').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body" id="receiptModalBody">
            <!-- Rendered by JS -->
        </div>
        <div class="modal-footer" style="justify-content: flex-end;">
            <button type="button" class="checkout-btn" style="width: auto; margin-top: 0; padding: 8px 18px;" onclick="document.getElementById('adminOrderReceiptModal').classList.remove('active')">Close</button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container"></div>

<!-- Main Admin JavaScript -->
<script>
function showAdminToast(msg, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<span>${type === 'success' ? '✅' : '⚠️'}</span><span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function openAddDishModal() {
    document.getElementById('adminAddDishModal').classList.add('active');
}

function addOptionRow() {
    const list = document.getElementById('dishOptionsList');
    const row = document.createElement('div');
    row.className = 'opt-row';
    row.style = 'display: grid; grid-template-columns: 1fr 1fr 100px 30px; gap: 8px; margin-bottom: 8px; align-items: center;';
    row.innerHTML = `
        <input type="text" placeholder="Group (e.g. Choose Size)" class="opt-grp" style="padding: 6px 10px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem;">
        <input type="text" placeholder="Option (e.g. Large)" class="opt-name" style="padding: 6px 10px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem;">
        <input type="number" step="0.01" placeholder="Extra $" class="opt-price" style="padding: 6px 10px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem;">
        <button type="button" onclick="this.parentElement.remove()" style="color: #ef4444; font-weight: 800; font-size: 1.1rem;">&times;</button>
    `;
    list.appendChild(row);
}

function submitNewDish(e) {
    e.preventDefault();
    const restId = document.getElementById('dishRestId').value;
    const name = document.getElementById('dishName').value.trim();
    const cat = document.getElementById('dishCategory').value.trim();
    const price = parseFloat(document.getElementById('dishPrice').value);
    const origPrice = parseFloat(document.getElementById('dishOriginalPrice').value) || null;
    const img = document.getElementById('dishImageUrl').value.trim();
    const desc = document.getElementById('dishDescription').value.trim();
    const isPop = document.getElementById('dishIsPopular').checked ? 1 : 0;
    const isVeg = document.getElementById('dishIsVegetarian').checked ? 1 : 0;
    const isSpicy = document.getElementById('dishIsSpicy').checked ? 1 : 0;
    const isGf = document.getElementById('dishIsGlutenFree').checked ? 1 : 0;

    // Options
    const optRows = document.querySelectorAll('.opt-row');
    const options = [];
    optRows.forEach(r => {
        const grp = r.querySelector('.opt-grp').value.trim();
        const optName = r.querySelector('.opt-name').value.trim();
        const optPrice = parseFloat(r.querySelector('.opt-price').value) || 0;
        if (optName) {
            options.push({ group_name: grp, option_name: optName, additional_price: optPrice });
        }
    });

    const payload = {
        restaurant_id: restId,
        name: name,
        category_name: cat,
        price: price,
        original_price: origPrice,
        image_url: img,
        description: desc,
        is_popular: isPop,
        is_vegetarian: isVeg,
        is_spicy: isSpicy,
        is_gluten_free: isGf,
        options: options
    };

    fetch('api.php?action=add_dish', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAdminToast(data.message, 'success');
            document.getElementById('adminAddDishModal').classList.remove('active');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showAdminToast(data.error || 'Failed to add dish', 'error');
        }
    })
    .catch(err => console.error(err));
}

function updateOrderStatus(orderNumber, newStatus) {
    fetch('api.php?action=update_order_status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_number: orderNumber, status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAdminToast(`Order ${orderNumber} set to ${newStatus.toUpperCase()}`, 'success');
        } else {
            showAdminToast(data.error || 'Failed to update order', 'error');
        }
    })
    .catch(err => console.error(err));
}

function viewOrderReceipt(orderNumber) {
    fetch(`../api/orders.php?order_number=${orderNumber}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const ord = data.order;
                const items = data.items;
                document.getElementById('receiptModalOrderNo').textContent = `Order: ${ord.order_number}`;

                let itemsHtml = items.map(it => `
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-size: 0.9rem;">
                        <div>
                            <div style="font-weight: 700;">${it.quantity}x ${it.item_name}</div>
                            ${it.special_instructions ? `<div style="font-size: 0.78rem; color: #ff2b85;">Note: ${it.special_instructions}</div>` : ''}
                        </div>
                        <div style="font-weight: 800; color: #ff2b85;">$${parseFloat(it.item_total).toFixed(2)}</div>
                    </div>
                `).join('');

                document.getElementById('receiptModalBody').innerHTML = `
                    <div style="margin-bottom: 12px;">
                        <div style="font-weight: 800; font-size: 1rem; color: #1e1e2d;">${ord.customer_name}</div>
                        <div style="font-size: 0.85rem; color: #6b7280;">📞 ${ord.customer_phone} • ✉️ ${ord.customer_email || 'N/A'}</div>
                        <div style="font-size: 0.85rem; color: #6b7280; margin-top: 4px;">📍 ${ord.delivery_address}</div>
                    </div>
                    <div style="background: #f8fafc; padding: 12px; border-radius: 8px; margin-bottom: 14px;">
                        <div style="font-size: 0.78rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Items Ordered</div>
                        ${itemsHtml}
                    </div>
                    <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 1.15rem; color: #1e1e2d;">
                        <span>Total Paid:</span>
                        <span style="color: #ff2b85;">$${parseFloat(ord.total_amount).toFixed(2)}</span>
                    </div>
                `;

                document.getElementById('adminOrderReceiptModal').classList.add('active');
            }
        });
}

function deleteDish(itemId) {
    if (confirm('Are you sure you want to remove this fresh dish from the menu?')) {
        fetch('api.php?action=delete_dish', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ item_id: itemId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAdminToast('Dish removed from menu', 'success');
                setTimeout(() => window.location.reload(), 800);
            }
        });
    }
}
</script>

</body>
</html>
