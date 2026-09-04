/**
 * FoodHub Express - Live Order Tracker & Animated Motorbike Route
 */

class OrderTracker {
    constructor(orderNumber) {
        this.orderNumber = orderNumber;
        this.orderData = null;
        this.statusSteps = ['placed', 'confirmed', 'preparing', 'on_way', 'delivered'];
        this.currentStatusIndex = 0;
        this.canvas = document.getElementById('deliveryCanvas');
        this.ctx = this.canvas ? this.canvas.getContext('2d') : null;
        this.bikeProgress = 0.15; // 0 to 1 along the path
        this.animationFrame = null;
        this.init();
    }

    init() {
        this.fetchOrder();
        this.setupCanvas();
        this.bindEvents();
    }

    fetchOrder() {
        if (!this.orderNumber) return;

        fetch(`api/orders.php?order_number=${this.orderNumber}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.orderData = data;
                    this.renderOrderDetails(data);
                    this.updateStatus(data.order.order_status);
                } else {
                    showToast('Order not found', 'error');
                }
            })
            .catch(err => console.error(err));
    }

    renderOrderDetails(data) {
        const order = data.order;
        const items = data.items;

        document.getElementById('trackOrderNumber').textContent = order.order_number;
        document.getElementById('trackRestaurantName').textContent = order.restaurant_name;
        document.getElementById('trackDeliveryAddress').textContent = order.delivery_address;
        document.getElementById('trackTotalAmount').textContent = `$${parseFloat(order.total_amount).toFixed(2)}`;
        document.getElementById('trackPaymentMethod').textContent = order.payment_method === 'cash_on_delivery' ? 'Cash on Delivery (COD)' : 'Card / Online Wallet';

        const itemsContainer = document.getElementById('trackOrderItemsList');
        if (itemsContainer) {
            itemsContainer.innerHTML = items.map(it => {
                const opts = it.selected_options ? it.selected_options.map(o => `+ ${o.name}`).join(', ') : '';
                return `
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem;">
                        <div>
                            <div style="font-weight: 700; color: #1e1e2d;">${it.quantity}x ${it.item_name}</div>
                            ${opts ? `<div style="font-size: 0.78rem; color: #6b7280;">${opts}</div>` : ''}
                        </div>
                        <div style="font-weight: 800; color: #ff2b85;">$${parseFloat(it.item_total).toFixed(2)}</div>
                    </div>
                `;
            }).join('');
        }
    }

    updateStatus(status) {
        const idx = this.statusSteps.indexOf(status);
        if (idx === -1) return;

        this.currentStatusIndex = idx;

        // Update progress bar line
        const lineFill = document.getElementById('timelineProgressFill');
        if (lineFill) {
            const percent = (idx / (this.statusSteps.length - 1)) * 100;
            lineFill.style.width = `${percent}%`;
        }

        // Update step circles
        const stepEls = document.querySelectorAll('.timeline-step');
        stepEls.forEach((el, sIdx) => {
            el.classList.remove('active', 'completed');
            if (sIdx < idx) {
                el.classList.add('completed');
            } else if (sIdx === idx) {
                el.classList.add('active');
            }
        });

        // Status Headlines
        const headlines = {
            'placed': { title: 'Order Placed!', desc: 'We received your order and sent it to the kitchen.', eta: '30-35 min' },
            'confirmed': { title: 'Order Confirmed!', desc: 'The restaurant accepted your order.', eta: '25-30 min' },
            'preparing': { title: 'Cooking in the Kitchen 🍳', desc: 'The chef is preparing your delicious meal.', eta: '18-22 min' },
            'on_way': { title: 'Rider is on the Way! 🛵', desc: 'Your rider picked up your food and is heading to you.', eta: '8-12 min' },
            'delivered': { title: 'Order Delivered! 🎉', desc: 'Enjoy your meal! Please rate your experience.', eta: 'Delivered' }
        };

        const currentInfo = headlines[status] || headlines['placed'];
        document.getElementById('trackStatusTitle').textContent = currentInfo.title;
        document.getElementById('trackStatusDesc').textContent = currentInfo.desc;
        document.getElementById('trackEtaTimer').textContent = currentInfo.eta;

        // Set motorcycle progress target
        if (status === 'placed') this.bikeProgress = 0.05;
        if (status === 'confirmed') this.bikeProgress = 0.20;
        if (status === 'preparing') this.bikeProgress = 0.40;
        if (status === 'on_way') this.bikeProgress = 0.75;
        if (status === 'delivered') this.bikeProgress = 1.00;

        this.startCanvasAnimation();
    }

    setupCanvas() {
        if (!this.canvas) return;

        const resize = () => {
            const rect = this.canvas.getBoundingClientRect();
            this.canvas.width = rect.width;
            this.canvas.height = rect.height;
        };
        resize();
        window.addEventListener('resize', resize);
        this.startCanvasAnimation();
    }

    startCanvasAnimation() {
        if (!this.ctx) return;
        cancelAnimationFrame(this.animationFrame);

        let currentProgress = 0.1;

        const animate = () => {
            if (currentProgress < this.bikeProgress) {
                currentProgress += 0.005;
            } else if (currentProgress > this.bikeProgress) {
                currentProgress -= 0.005;
            }

            this.drawMap(currentProgress);
            this.animationFrame = requestAnimationFrame(animate);
        };
        animate();
    }

    drawMap(progress) {
        const ctx = this.ctx;
        const w = this.canvas.width;
        const h = this.canvas.height;

        ctx.clearRect(0, 0, w, h);

        // Background City Blocks
        ctx.fillStyle = '#f1f5f9';
        ctx.fillRect(0, 0, w, h);

        // Draw City Grid Roads
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 14;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        // Secondary roads
        ctx.beginPath();
        ctx.moveTo(40, h / 2);
        ctx.lineTo(w - 40, h / 2);
        ctx.moveTo(w / 3, 20);
        ctx.lineTo(w / 3, h - 20);
        ctx.moveTo((w * 2) / 3, 20);
        ctx.lineTo((w * 2) / 3, h - 20);
        ctx.stroke();

        // Main Delivery Route Path
        const startX = 60;
        const startY = h - 60;
        const midX1 = w / 3;
        const midY1 = startY;
        const midX2 = w / 3;
        const midY2 = 60;
        const endX = w - 60;
        const endY = 60;

        // Path Outline
        ctx.strokeStyle = '#ff2b85';
        ctx.lineWidth = 6;
        ctx.setLineDash([8, 6]);
        ctx.beginPath();
        ctx.moveTo(startX, startY);
        ctx.lineTo(midX1, midY1);
        ctx.lineTo(midX2, midY2);
        ctx.lineTo(endX, endY);
        ctx.stroke();
        ctx.setLineDash([]); // Reset dash

        // 1. Restaurant Pin (Start)
        ctx.fillStyle = '#1e1e2d';
        ctx.beginPath();
        ctx.arc(startX, startY, 14, 0, Math.PI * 2);
        ctx.fill();
        ctx.fillStyle = '#ffffff';
        ctx.font = '12px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('🍳', startX, startY);

        // 2. Customer Home Pin (End)
        ctx.fillStyle = '#10b981';
        ctx.beginPath();
        ctx.arc(endX, endY, 14, 0, Math.PI * 2);
        ctx.fill();
        ctx.fillStyle = '#ffffff';
        ctx.fillText('🏠', endX, endY);

        // 3. Calculate Motorbike Position along Multi-segment Path
        let bx = startX;
        let by = startY;

        if (progress <= 0.33) {
            const p = progress / 0.33;
            bx = startX + (midX1 - startX) * p;
            by = startY;
        } else if (progress <= 0.66) {
            const p = (progress - 0.33) / 0.33;
            bx = midX1;
            by = midY1 + (midY2 - midY1) * p;
        } else {
            const p = (progress - 0.66) / 0.34;
            bx = midX2 + (endX - midX2) * p;
            by = midY2;
        }

        // Pulse Ring around Rider
        ctx.fillStyle = 'rgba(255, 43, 133, 0.2)';
        ctx.beginPath();
        ctx.arc(bx, by, 22 + Math.sin(Date.now() / 200) * 4, 0, Math.PI * 2);
        ctx.fill();

        // Rider Icon
        ctx.fillStyle = '#ff2b85';
        ctx.beginPath();
        ctx.arc(bx, by, 16, 0, Math.PI * 2);
        ctx.fill();
        ctx.fillStyle = '#ffffff';
        ctx.font = '14px sans-serif';
        ctx.fillText('🛵', bx, by);
    }

    bindEvents() {
        // Step advance button for live demo testing
        const advanceBtn = document.getElementById('demoAdvanceStatusBtn');
        if (advanceBtn) {
            advanceBtn.addEventListener('click', () => {
                let nextIdx = (this.currentStatusIndex + 1) % this.statusSteps.length;
                const nextStatus = this.statusSteps[nextIdx];

                fetch(`api/orders.php?action=advance_status`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        order_number: this.orderNumber,
                        status: nextStatus
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.updateStatus(nextStatus);
                        showToast(`Status updated to: ${nextStatus.toUpperCase()}`, 'success');
                    }
                });
            });
        }

        // Rider Call Simulation
        const callRiderBtn = document.getElementById('callRiderBtn');
        if (callRiderBtn) {
            callRiderBtn.addEventListener('click', () => {
                alert("📞 Calling Rider Rashid (+1 555-019-2834)...\n'Hello! I am on my way with your hot meal!'");
            });
        }

        // Rider Chat Simulation
        const chatRiderBtn = document.getElementById('chatRiderBtn');
        if (chatRiderBtn) {
            chatRiderBtn.addEventListener('click', () => {
                const msg = prompt("Send a quick message to your delivery rider:", "Please leave at door / ring bell");
                if (msg) {
                    showToast(`Message sent to rider: "${msg}"`, 'success');
                }
            });
        }
    }
}

window.OrderTracker = OrderTracker;
