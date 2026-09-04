/**
 * FoodHub Express - Main Application JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    initApp();
});

function initApp() {
    initSearchAutocomplete();
    initCategoryFilter();
    initDietaryFilter();
    initLocationModal();
    initFavorites();
    initDealsCopy();
}

// 1. Toast Notification System
function showToast(message, type = 'info') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icon = type === 'success' ? '✅' : type === 'error' ? '⚠️' : 'ℹ️';
    toast.innerHTML = `<span>${icon}</span><span>${message}</span>`;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3200);
}

// 2. Search Autocomplete Dropdown
function initSearchAutocomplete() {
    const searchInputs = document.querySelectorAll('.nav-search-input, #mainSearchInput');
    const dropdown = document.getElementById('searchResultsDropdown');

    if (!dropdown) return;

    let debounceTimer = null;

    searchInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            clearTimeout(debounceTimer);

            if (query.length < 2) {
                dropdown.classList.remove('active');
                dropdown.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`api/restaurants.php?search=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.restaurants.length > 0) {
                            dropdown.innerHTML = data.restaurants.map(r => `
                                <div class="search-result-item" onclick="window.location.href='restaurant.php?id=${r.id}'">
                                    <img src="${r.image_url}" alt="${r.name}">
                                    <div>
                                        <div style="font-weight: 700; font-size: 0.95rem; color: #1e1e2d;">${r.name}</div>
                                        <div style="font-size: 0.8rem; color: #6b7280;">${r.cuisine_type} • ⏱️ ${r.delivery_time}</div>
                                    </div>
                                </div>
                            `).join('');
                            dropdown.classList.add('active');
                        } else {
                            dropdown.innerHTML = `<div style="padding: 16px; text-align: center; color: #6b7280; font-size: 0.88rem;">No restaurants or dishes found matching "${query}"</div>`;
                            dropdown.classList.add('active');
                        }
                    })
                    .catch(err => console.error(err));
            }, 250);
        });
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.nav-search') && !e.target.closest('.search-results-dropdown')) {
            dropdown.classList.remove('active');
        }
    });
}

// 3. Category Filter
function initCategoryFilter() {
    const catPills = document.querySelectorAll('.cat-pill');
    catPills.forEach(pill => {
        pill.addEventListener('click', () => {
            catPills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            const category = pill.dataset.category;
            filterRestaurants({ category });
        });
    });
}

// 4. Dietary & Chip Filters
function initDietaryFilter() {
    const filterChips = document.querySelectorAll('.filter-chip');
    filterChips.forEach(chip => {
        chip.addEventListener('click', () => {
            const filterType = chip.dataset.filter;
            chip.classList.toggle('active');
            
            const activeCategory = document.querySelector('.cat-pill.active')?.dataset.category || 'all';
            const isFreeDel = document.querySelector('.filter-chip[data-filter="free_delivery"]')?.classList.contains('active') ? 1 : 0;
            const isHalal = document.querySelector('.filter-chip[data-filter="halal"]')?.classList.contains('active') ? 1 : 0;
            const isDietary = document.querySelector('.filter-chip[data-filter="vegetarian"]')?.classList.contains('active') ? 'vegetarian' : '';

            filterRestaurants({
                category: activeCategory,
                free_delivery: isFreeDel,
                halal: isHalal,
                dietary: isDietary
            });
        });
    });

    const sortSelect = document.getElementById('restaurantSortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            filterRestaurants({ sort: sortSelect.value });
        });
    }
}

function filterRestaurants(params = {}) {
    const grid = document.getElementById('restaurantGrid');
    if (!grid) return;

    grid.style.opacity = '0.5';
    
    const queryParams = new URLSearchParams(params).toString();
    fetch(`api/restaurants.php?${queryParams}`)
        .then(res => res.json())
        .then(data => {
            grid.style.opacity = '1';
            if (data.success && data.restaurants.length > 0) {
                grid.innerHTML = data.restaurants.map(r => renderRestaurantCard(r)).join('');
            } else {
                grid.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 48px 20px; background: white; border-radius: 16px; border: 1px dashed #cbd5e1;">
                        <div style="font-size: 3rem; margin-bottom: 8px;">🍽️</div>
                        <h3 style="font-weight: 800; font-size: 1.25rem; margin-bottom: 6px;">No restaurants found</h3>
                        <p style="color: #6b7280; font-size: 0.9rem;">Try selecting a different category or clearing your active filters.</p>
                    </div>
                `;
            }
        })
        .catch(err => {
            grid.style.opacity = '1';
            console.error(err);
        });
}

function renderRestaurantCard(r) {
    const feeText = parseFloat(r.delivery_fee) === 0 ? 'Free Delivery' : `$${parseFloat(r.delivery_fee).toFixed(2)} Delivery`;
    return `
        <div class="restaurant-card">
            <div class="rest-img-wrapper" onclick="window.location.href='restaurant.php?id=${r.id}'" style="cursor: pointer;">
                <img src="${r.image_url}" alt="${r.name}" loading="lazy">
                ${r.is_featured == 1 ? '<span class="badge-tag badge-featured">Featured</span>' : ''}
                ${r.is_free_delivery == 1 ? '<span class="badge-tag badge-free" style="left: ${r.is_featured == 1 ? '96px' : '12px'};">Free Del</span>' : ''}
                <button class="fav-btn" onclick="event.stopPropagation(); toggleFavorite(${r.id}, this)">❤️</button>
            </div>
            <div class="rest-info" onclick="window.location.href='restaurant.php?id=${r.id}'" style="cursor: pointer;">
                <div class="rest-header">
                    <h3 class="rest-name">${r.name}</h3>
                    <div class="rating-chip">★ ${parseFloat(r.rating).toFixed(1)}</div>
                </div>
                <div class="rest-cuisine">${r.cuisine_type}</div>
                <div class="rest-footer">
                    <span class="rest-meta-item">⏱️ ${r.delivery_time}</span>
                    <span class="rest-meta-item">🛵 ${feeText}</span>
                    <span class="rest-meta-item">Min $${parseFloat(r.min_order).toFixed(0)}</span>
                </div>
            </div>
        </div>
    `;
}

// 5. Favorites Local Storage
function initFavorites() {
    const favs = JSON.parse(localStorage.getItem('foodhub_favs') || '[]');
    document.querySelectorAll('.fav-btn').forEach(btn => {
        const id = parseInt(btn.dataset.id);
        if (favs.includes(id)) {
            btn.classList.add('active');
        }
    });
}

function toggleFavorite(id, btn) {
    let favs = JSON.parse(localStorage.getItem('foodhub_favs') || '[]');
    if (favs.includes(id)) {
        favs = favs.filter(item => item !== id);
        btn.classList.remove('active');
        showToast('Removed from saved favorites', 'info');
    } else {
        favs.push(id);
        btn.classList.add('active');
        showToast('Added to saved favorites! ❤️', 'success');
    }
    localStorage.setItem('foodhub_favs', JSON.stringify(favs));
}

// 6. Location Address Modal
function initLocationModal() {
    const locBtn = document.getElementById('locationPickerBtn');
    const locModal = document.getElementById('locationModal');
    const saveLocBtn = document.getElementById('saveLocationBtn');
    const locInput = document.getElementById('userAddressInput');
    const currentLocText = document.getElementById('currentLocationText');

    const savedAddress = localStorage.getItem('foodhub_address') || 'Downtown Food District';
    if (currentLocText) {
        currentLocText.textContent = savedAddress;
    }

    if (locBtn && locModal) {
        locBtn.addEventListener('click', () => {
            if (locInput) locInput.value = localStorage.getItem('foodhub_address') || 'Downtown, Flavor Ave 42';
            locModal.classList.add('active');
        });
    }

    if (saveLocBtn && locInput && locModal) {
        saveLocBtn.addEventListener('click', () => {
            const val = locInput.value.trim();
            if (val) {
                localStorage.setItem('foodhub_address', val);
                if (currentLocText) currentLocText.textContent = val;
                locModal.classList.remove('active');
                showToast(`Address updated to: ${val}`, 'success');
            }
        });
    }
}

// 7. Click to Copy Deals
function initDealsCopy() {
    document.querySelectorAll('.voucher-chip, .copy-coupon-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const code = btn.dataset.code || btn.querySelector('.v-code')?.textContent.trim();
            if (code) {
                navigator.clipboard.writeText(code).then(() => {
                    showToast(`Copied code "${code}"! Paste it at checkout to save!`, 'success');
                });
            }
        });
    });
}
