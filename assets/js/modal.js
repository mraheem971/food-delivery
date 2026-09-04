/**
 * FoodHub Express - Dish Customizer Modal Manager
 */

class DishModalManager {
    constructor() {
        this.modal = document.getElementById('dishCustomizerModal');
        this.currentDish = null;
        this.restaurantId = null;
        this.restaurantName = null;
        this.currentQty = 1;
        this.selectedOptions = [];
        this.bindEvents();
    }

    bindEvents() {
        if (!this.modal) return;

        const closeBtns = this.modal.querySelectorAll('.modal-close-icon, .close-modal-btn');
        closeBtns.forEach(btn => {
            btn.addEventListener('click', () => this.close());
        });

        // Close on backdrop click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });

        // Quantity controls
        const decBtn = document.getElementById('dishModalQtyDec');
        const incBtn = document.getElementById('dishModalQtyInc');

        if (decBtn) {
            decBtn.addEventListener('click', () => {
                if (this.currentQty > 1) {
                    this.currentQty--;
                    this.updateQuantityDisplay();
                }
            });
        }

        if (incBtn) {
            incBtn.addEventListener('click', () => {
                this.currentQty++;
                this.updateQuantityDisplay();
            });
        }

        // Add to Cart Button inside Modal
        const addCartBtn = document.getElementById('modalAddToCartBtn');
        if (addCartBtn) {
            addCartBtn.addEventListener('click', () => {
                this.addToCart();
            });
        }
    }

    open(dish, restaurantId, restaurantName) {
        this.currentDish = dish;
        this.restaurantId = restaurantId;
        this.restaurantName = restaurantName;
        this.currentQty = 1;
        this.selectedOptions = [];

        // Set Image & Basic Details
        const img = document.getElementById('modalDishImage');
        if (img) img.src = dish.image_url || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600';

        document.getElementById('modalDishTitle').textContent = dish.name;
        document.getElementById('modalDishDesc').textContent = dish.description || 'Delicious freshly prepared dish crafted with premium ingredients.';
        
        const calTag = document.getElementById('modalDishCalories');
        if (calTag) {
            if (dish.calories) {
                calTag.textContent = `🔥 ${dish.calories} kcal`;
                calTag.style.display = 'inline-block';
            } else {
                calTag.style.display = 'none';
            }
        }

        // Render Options & Add-ons
        this.renderOptions(dish.options || []);

        const notesInput = document.getElementById('modalSpecialInstructions');
        if (notesInput) notesInput.value = '';

        this.updateQuantityDisplay();
        this.modal.classList.add('active');
    }

    close() {
        if (this.modal) {
            this.modal.classList.remove('active');
        }
    }

    renderOptions(optionGroups) {
        const container = document.getElementById('modalOptionsContainer');
        if (!container) return;

        if (!optionGroups || optionGroups.length === 0) {
            container.innerHTML = '';
            return;
        }

        container.innerHTML = optionGroups.map((grp, gIdx) => {
            const isRadio = grp.is_required && grp.max_choices === 1;
            const inputType = isRadio ? 'radio' : 'checkbox';
            const reqBadge = grp.is_required ? '<span style="color: #ff2b85; font-size: 0.75rem; font-weight: 800; background: #fff0f5; padding: 2px 8px; border-radius: 4px;">REQUIRED</span>' : '<span style="color: #6b7280; font-size: 0.75rem; font-weight: 700;">OPTIONAL</span>';

            return `
                <div class="option-group-box">
                    <div class="option-group-title">
                        <span>${grp.group_name}</span>
                        ${reqBadge}
                    </div>
                    <div class="option-choices-list">
                        ${grp.options.map((opt, oIdx) => `
                            <label class="option-choice-item" for="opt_${gIdx}_${oIdx}">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <input type="${inputType}" 
                                           id="opt_${gIdx}_${oIdx}" 
                                           name="option_grp_${gIdx}" 
                                           value="${opt.name}" 
                                           data-price="${opt.price}"
                                           data-group="${grp.group_name}"
                                           ${isRadio && oIdx === 0 ? 'checked' : ''}
                                           onchange="window.dishModalManager.recalculateTotal()">
                                    <span style="font-size: 0.9rem; font-weight: 600;">${opt.name}</span>
                                </div>
                                <span style="font-size: 0.88rem; font-weight: 700; color: #ff2b85;">
                                    ${parseFloat(opt.price) > 0 ? `+$${parseFloat(opt.price).toFixed(2)}` : 'Free'}
                                </span>
                            </label>
                        `).join('')}
                    </div>
                </div>
            `;
        }).join('');
    }

    recalculateTotal() {
        if (!this.currentDish) return;

        let basePrice = parseFloat(this.currentDish.price);
        let extraCost = 0.00;
        this.selectedOptions = [];

        const inputs = this.modal.querySelectorAll('#modalOptionsContainer input:checked');
        inputs.forEach(inp => {
            const price = parseFloat(inp.dataset.price || 0);
            extraCost += price;
            this.selectedOptions.push({
                group: inp.dataset.group,
                name: inp.value,
                price: price
            });
        });

        const singleUnitPrice = basePrice + extraCost;
        const total = singleUnitPrice * this.currentQty;

        const totalBtn = document.getElementById('modalAddToCartBtn');
        if (totalBtn) {
            totalBtn.innerHTML = `<span>Add to Cart</span> • <span style="font-weight: 800;">$${total.toFixed(2)}</span>`;
        }
    }

    updateQuantityDisplay() {
        const qtyEl = document.getElementById('modalDishQtyDisplay');
        if (qtyEl) qtyEl.textContent = this.currentQty;
        this.recalculateTotal();
    }

    addToCart() {
        if (!this.currentDish) return;

        let basePrice = parseFloat(this.currentDish.price);
        let extraCost = 0.00;
        this.selectedOptions = [];

        const inputs = this.modal.querySelectorAll('#modalOptionsContainer input:checked');
        inputs.forEach(inp => {
            const price = parseFloat(inp.dataset.price || 0);
            extraCost += price;
            this.selectedOptions.push({
                group: inp.dataset.group,
                name: inp.value,
                price: price
            });
        });

        const singleUnitPrice = basePrice + extraCost;
        const notes = document.getElementById('modalSpecialInstructions')?.value.trim() || '';

        const itemPayload = {
            item_id: this.currentDish.id,
            name: this.currentDish.name,
            image_url: this.currentDish.image_url,
            unit_price: singleUnitPrice,
            quantity: this.currentQty,
            selected_options: this.selectedOptions,
            special_instructions: notes
        };

        const added = window.cartManager.addItem(this.restaurantId, this.restaurantName, itemPayload);
        if (added) {
            this.close();
        }
    }
}

// Global modal instance
window.dishModalManager = new DishModalManager();
