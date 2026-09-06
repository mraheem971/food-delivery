/**
 * FoodHub Express - Cart & Checkout Engine
 */

class CartManager {
    constructor() {
        this.cart = JSON.parse(localStorage.getItem('foodhub_cart') || '{"restaurant_id": null, "restaurant_name": "", "items": [], "applied_coupon": null, "discount": 0}');
        this.deliveryFee = 1.99;
        this.platformFee = 0.50;
        this.driverTip = 0.00;
        this.init();
    }

    init() {
        this.updateBadge();
        this.bindEvents();
    }

    bindEvents() {
        // Open Cart Drawer
        const cartTriggers = document.querySelectorAll('.cart-trigger-btn, #openCartDrawerBtn');
        const cartOverlay = document.getElementById('cartOverlay');
        const cartDrawer = document.getElementById('cartDrawer');
        const closeCartBtns = document.querySelectorAll('.close-cart-btn, #cartOverlay');

        cartTriggers.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.renderDrawer();
                if (cartOverlay) cartOverlay.classList.add('active');
                if (cartDrawer) cartDrawer.classList.add('active');
            });
        });

        closeCartBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                if (cartOverlay) cartOverlay.classList.remove('active');
                if (cartDrawer) cartDrawer.classList.remove('active');
            });
        });

        // Apply Coupon in Drawer
        const applyCouponBtn = document.getElementById('applyCouponBtn');
        const couponInput = document.getElementById('cartCouponInput');

        if (applyCouponBtn && couponInput) {
            applyCouponBtn.addEventListener('click', () => {
                const code = couponInput.value.trim();
                if (code) {
                    this.applyCoupon(code);
                }
            });
        }

        // Checkout Button in Drawer
        const proceedCheckoutBtn = document.getElementById('proceedCheckoutBtn');
        const checkoutModal = document.getElementById('checkoutModal');

        if (proceedCheckoutBtn) {
            proceedCheckoutBtn.addEventListener('click', () => {
                if (this.cart.items.length === 0) {
                    showToast('Your cart is empty! Add some delicious food first.', 'error');
                    return;
                }
                // Close drawer & open checkout modal
                if (cartOverlay) cartOverlay.classList.remove('active');
                if (cartDrawer) cartDrawer.classList.remove('active');
                if (checkoutModal) {
                    this.populateCheckoutModal();
                    checkoutModal.classList.add('active');
                }
            });
        }

        // Place Order Form Submission
        const checkoutForm = document.getElementById('checkoutOrderForm');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitOrder(checkoutForm);
            });
        }
    }

    saveCart() {
        localStorage.setItem('foodhub_cart', JSON.stringify(this.cart));
        this.updateBadge();
    }

    updateBadge() {
        const totalCount = this.cart.items.reduce((sum, item) => sum + item.quantity, 0);
        document.querySelectorAll('.cart-badge').forEach(badge => {
            badge.textContent = totalCount;
            badge.style.display = totalCount > 0 ? 'inline-block' : 'none';
        });
    }

    addItem(restaurantId, restaurantName, item) {
        // If cart has items from different restaurant, confirm reset
        if (this.cart.restaurant_id && this.cart.restaurant_id !== restaurantId && this.cart.items.length > 0) {
            if (!confirm(`Your cart already contains dishes from ${this.cart.restaurant_name}. Do you want to start a fresh order from ${restaurantName}?`)) {
                return false;
            }
            this.clearCart();
        }

        this.cart.restaurant_id = restaurantId;
        this.cart.restaurant_name = restaurantName;

        // Generate unique signature for item + options combination
        const optionsSignature = JSON.stringify(item.selected_options || []);
        const existingIndex = this.cart.items.findIndex(
            it => it.item_id === item.item_id && JSON.stringify(it.selected_options || []) === optionsSignature
        );

        if (existingIndex > -1) {
            this.cart.items[existingIndex].quantity += item.quantity;
            this.cart.items[existingIndex].total = this.cart.items[existingIndex].quantity * this.cart.items[existingIndex].unit_price;
        } else {
            this.cart.items.push({
                item_id: item.item_id,
                name: item.name,
                image_url: item.image_url,
                unit_price: item.unit_price,
                quantity: item.quantity,
                selected_options: item.selected_options || [],
                special_instructions: item.special_instructions || '',
                total: item.quantity * item.unit_price
            });
        }

        this.saveCart();
        showToast(`Added ${item.name} to cart! 🛍️`, 'success');
        this.renderDrawer();
        return true;
    }

    updateQuantity(index, delta) {
        if (!this.cart.items[index]) return;

        this.cart.items[index].quantity += delta;

        if (this.cart.items[index].quantity <= 0) {
            this.cart.items.splice(index, 1);
        } else {
            this.cart.items[index].total = this.cart.items[index].quantity * this.cart.items[index].unit_price;
        }

        if (this.cart.items.length === 0) {
            this.clearCart();
        } else {
            this.saveCart();
        }

        this.renderDrawer();
    }

    clearCart() {
        this.cart = {
            restaurant_id: null,
            restaurant_name: '',
            items: [],
            applied_coupon: null,
            discount: 0
        };
        this.saveCart();
        this.renderDrawer();
    }

    getSubtotal() {
        return this.cart.items.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
    }

    applyCoupon(code) {
        const subtotal = this.getSubtotal();
        if (subtotal <= 0) {
            showToast('Add items before applying vouchers', 'error');
            return;
        }

        fetch('api/coupons.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code: code, subtotal: subtotal })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.cart.applied_coupon = data.coupon.code;
                this.cart.discount = data.discount;
                this.saveCart();
                this.renderDrawer();
                showToast(`🎉 ${data.message} Saved $${data.discount.toFixed(2)}`, 'success');
            } else {
                showToast(data.error || 'Invalid voucher code', 'error');
            }
        })
        .catch(err => {
            showToast('Failed to apply coupon', 'error');
            console.error(err);
        });
    }

    renderDrawer() {
        const cartBody = document.getElementById('cartDrawerBody');
        const cartFooter = document.getElementById('cartDrawerFooter');
        const restaurantHeader = document.getElementById('cartRestaurantTitle');

        if (!cartBody) return;

        if (this.cart.items.length === 0) {
            if (restaurantHeader) restaurantHeader.textContent = 'Your Cart is Empty';
            cartBody.innerHTML = `
                <div class="cart-empty-state">
                    <div class="cart-empty-icon">🛍️</div>
                    <h4 style="font-weight: 800; font-size: 1.15rem; margin-bottom: 6px; color: #1e1e2d;">Hungry? Let's order!</h4>
                    <p style="font-size: 0.88rem; color: #6b7280;">Explore top restaurants nearby and add your favorite dishes.</p>
                </div>
            `;
            if (cartFooter) cartFooter.style.display = 'none';
            return;
        }

        if (restaurantHeader) {
            restaurantHeader.innerHTML = `Ordering from <span style="color: #ff2b85;">${this.cart.restaurant_name}</span>`;
        }

        cartBody.innerHTML = this.cart.items.map((it, idx) => {
            const optionsSummary = it.selected_options.map(opt => `+ ${opt.name}`).join(', ');
            return `
                <div class="cart-item-card">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${it.name}</div>
                        ${optionsSummary ? `<div class="cart-item-options">${optionsSummary}</div>` : ''}
                        ${it.special_instructions ? `<div class="cart-item-options" style="color: #ff2b85;">Note: ${it.special_instructions}</div>` : ''}
                        <div class="cart-item-price">$${(it.unit_price * it.quantity).toFixed(2)}</div>
                    </div>
                    <div class="qty-control">
                        <button class="qty-btn" onclick="window.cartManager.updateQuantity(${idx}, -1)">-</button>
                        <span class="qty-count">${it.quantity}</span>
                        <button class="qty-btn" onclick="window.cartManager.updateQuantity(${idx}, 1)">+</button>
                    </div>
                </div>
            `;
        }).join('');

        if (cartFooter) {
            cartFooter.style.display = 'block';
            const subtotal = this.getSubtotal();
            const discount = this.cart.discount || 0.00;
            const deliveryFee = this.deliveryFee;
            const platformFee = this.platformFee;
            const total = Math.max(0, subtotal + deliveryFee + platformFee - discount);

            document.getElementById('cartSubtotalValue').textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById('cartDeliveryFeeValue').textContent = `$${deliveryFee.toFixed(2)}`;
            document.getElementById('cartPlatformFeeValue').textContent = `$${platformFee.toFixed(2)}`;
            
            const discountRow = document.getElementById('cartDiscountRow');
            if (discountRow) {
                if (discount > 0) {
                    discountRow.style.display = 'flex';
                    document.getElementById('cartDiscountValue').textContent = `-$${discount.toFixed(2)} (${this.cart.applied_coupon})`;
                } else {
                    discountRow.style.display = 'none';
                }
            }

            document.getElementById('cartTotalValue').textContent = `$${total.toFixed(2)}`;
        }
    }

    populateCheckoutModal() {
        const modalSubtotal = this.getSubtotal();
        const discount = this.cart.discount || 0;
        const total = Math.max(0, modalSubtotal + this.deliveryFee + this.platformFee + this.driverTip - discount);

        const summaryBox = document.getElementById('checkoutSummaryItems');
        if (summaryBox) {
            summaryBox.innerHTML = this.cart.items.map(it => `
                <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 6px;">
                    <span>${it.quantity}x ${it.name}</span>
                    <span style="font-weight: 700;">$${(it.unit_price * it.quantity).toFixed(2)}</span>
                </div>
            `).join('');
        }

        const totalDisplay = document.getElementById('checkoutModalTotal');
        if (totalDisplay) totalDisplay.textContent = `$${total.toFixed(2)}`;

        const addrInput = document.getElementById('checkoutDeliveryAddress');
        if (addrInput && !addrInput.value) {
            addrInput.value = localStorage.getItem('foodhub_address') || 'Downtown, Flavor Ave 42, Apt 4B';
        }

        if (window.authManager && typeof window.authManager.autoFillCheckout === 'function') {
            window.authManager.autoFillCheckout();
        }
    }

    submitOrder(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>⏳ Placing Order...</span>';
        }

        const orderData = {
            restaurant_id: this.cart.restaurant_id,
            customer_name: form.querySelector('#checkoutCustomerName').value.trim(),
            customer_phone: form.querySelector('#checkoutCustomerPhone').value.trim(),
            customer_email: form.querySelector('#checkoutCustomerEmail')?.value.trim() || '',
            delivery_address: form.querySelector('#checkoutDeliveryAddress').value.trim(),
            delivery_instructions: form.querySelector('#checkoutInstructions')?.value.trim() || '',
            payment_method: form.querySelector('input[name="payment_method"]:checked')?.value || 'cash_on_delivery',
            applied_coupon: this.cart.applied_coupon,
            driver_tip: this.driverTip,
            items: this.cart.items
        };

        fetch('api/orders.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Clear cart
                this.clearCart();
                showToast('🎉 Order placed successfully! Redirecting to tracker...', 'success');
                setTimeout(() => {
                    window.location.href = data.track_url || `track.php?order_number=${data.order_number}`;
                }, 1200);
            } else {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Place Order';
                }
                showToast(data.error || 'Failed to place order', 'error');
            }
        })
        .catch(err => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Place Order';
            }
            showToast('Network error while placing order', 'error');
            console.error(err);
        });
    }
}

// Global instance
window.cartManager = new CartManager();
