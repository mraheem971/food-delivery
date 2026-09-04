<?php
require_once __DIR__ . '/../data/db.php';
require_once __DIR__ . '/../data/whatsapp.php';

$sessionStatus = WhatsAppService::getSessionStatus();
$isConnected = isset($sessionStatus['status']) && $sessionStatus['status'] === 'connected';
$connectedPhone = $sessionStatus['user']['phone'] ?? '923216793596';
$connectedName = $sessionStatus['user']['name'] ?? 'FoodHub WhatsApp Bot';

require_once __DIR__ . '/header.php';
?>

<div style="display: grid; grid-template-columns: 1fr 360px; gap: 28px; align-items: start;">
    <!-- Left Column: Status & Features -->
    <div>
        <!-- Connection Status Card -->
        <div style="background: white; border-radius: var(--radius-md); border: 1px solid var(--border); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 56px; height: 56px; border-radius: 16px; background: #25d366; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; box-shadow: 0 4px 14px rgba(37, 211, 102, 0.35);">
                        💬
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--secondary);">WhatsApp Bot Integration</h3>
                            <span class="badge-tag" style="position: static; <?= $isConnected ? 'background: #dcfce7; color: #15803d;' : 'background: #fee2e2; color: #b91c1c;' ?>">
                                <?= $isConnected ? '🟢 CONNECTED & ACTIVE' : '🔴 OFFLINE' ?>
                            </span>
                        </div>
                        <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 4px;">
                            Connected Number: <strong style="color: var(--secondary);">+<?= htmlspecialchars($connectedPhone) ?></strong> (<?= htmlspecialchars($connectedName) ?>)
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="button" class="apply-btn" onclick="refreshWhatsAppStatus()" style="background: var(--bg-surface); color: var(--secondary); border: 1px solid var(--border);">
                        🔄 Refresh Status
                    </button>
                    <?php if (!$isConnected): ?>
                        <button type="button" class="checkout-btn" onclick="startWhatsAppSession()" style="margin-top: 0; width: auto; background: #25d366;">
                            📱 Scan QR Code
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Automated Triggers & Message Preview -->
        <div class="data-table-card">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--border);">
                <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--secondary);">Automated WhatsApp Triggers</h3>
                <p style="font-size: 0.84rem; color: var(--text-muted);">These notifications are automatically sent to customers and the store owner</p>
            </div>

            <div style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
                <!-- Trigger 1 -->
                <div style="display: flex; gap: 16px; padding: 16px; border-radius: var(--radius-sm); background: var(--bg-surface); border: 1px solid var(--border); align-items: flex-start;">
                    <span style="font-size: 1.5rem;">📥</span>
                    <div style="flex: 1;">
                        <div style="font-weight: 800; font-size: 0.95rem; color: var(--secondary);">1. Customer Order Confirmation & Receipt</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">
                            Fires instantly on checkout. Sends order number, ordered dishes, pricing breakdown, address, and live GPS tracking URL.
                        </div>
                    </div>
                    <span class="badge-tag" style="position: static; background: #ecfdf5; color: #047857;">ENABLED</span>
                </div>

                <!-- Trigger 2 -->
                <div style="display: flex; gap: 16px; padding: 16px; border-radius: var(--radius-sm); background: var(--bg-surface); border: 1px solid var(--border); align-items: flex-start;">
                    <span style="font-size: 1.5rem;">🔔</span>
                    <div style="flex: 1;">
                        <div style="font-weight: 800; font-size: 0.95rem; color: var(--secondary);">2. Store Owner New Order Alert</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">
                            Sends instant order notification to owner WhatsApp (<code>+<?= htmlspecialchars(WHATSAPP_ADMIN_PHONE) ?></code>) with customer details and direct admin link.
                        </div>
                    </div>
                    <span class="badge-tag" style="position: static; background: #ecfdf5; color: #047857;">ENABLED</span>
                </div>

                <!-- Trigger 3 -->
                <div style="display: flex; gap: 16px; padding: 16px; border-radius: var(--radius-sm); background: var(--bg-surface); border: 1px solid var(--border); align-items: flex-start;">
                    <span style="font-size: 1.5rem;">🍳</span>
                    <div style="flex: 1;">
                        <div style="font-weight: 800; font-size: 0.95rem; color: var(--secondary);">3. Kitchen Preparation Status Update</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">
                            Notifies customer on WhatsApp when the chef begins cooking their order.
                        </div>
                    </div>
                    <span class="badge-tag" style="position: static; background: #ecfdf5; color: #047857;">ENABLED</span>
                </div>

                <!-- Trigger 4 -->
                <div style="display: flex; gap: 16px; padding: 16px; border-radius: var(--radius-sm); background: var(--bg-surface); border: 1px solid var(--border); align-items: flex-start;">
                    <span style="font-size: 1.5rem;">🛵</span>
                    <div style="flex: 1;">
                        <div style="font-weight: 800; font-size: 0.95rem; color: var(--secondary);">4. Rider Dispatched & On The Way</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">
                            Alerts customer that the rider is en route with ETA countdown and live map link.
                        </div>
                    </div>
                    <span class="badge-tag" style="position: static; background: #ecfdf5; color: #047857;">ENABLED</span>
                </div>

                <!-- Trigger 5 -->
                <div style="display: flex; gap: 16px; padding: 16px; border-radius: var(--radius-sm); background: var(--bg-surface); border: 1px solid var(--border); align-items: flex-start;">
                    <span style="font-size: 1.5rem;">🎉</span>
                    <div style="flex: 1;">
                        <div style="font-weight: 800; font-size: 0.95rem; color: var(--secondary);">5. Order Delivered & Review Request</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">
                            Celebrates successful delivery and invites customer to rate their meal experience.
                        </div>
                    </div>
                    <span class="badge-tag" style="position: static; background: #ecfdf5; color: #047857;">ENABLED</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Test Message Sender & QR Code Modal -->
    <div>
        <!-- Live Test Message Sender -->
        <div style="background: white; border-radius: var(--radius-md); border: 1px solid var(--border); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                <span style="font-size: 1.2rem;">📱</span>
                <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--secondary);">Test WhatsApp Alert</h3>
            </div>
            <p style="font-size: 0.84rem; color: var(--text-muted); margin-bottom: 16px;">Send an instant test WhatsApp message to verify delivery to any international number.</p>

            <form id="testWhatsAppForm" onsubmit="sendTestWhatsApp(event)">
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 4px;">Phone Number (with country code) *</label>
                    <input type="text" id="testPhone" value="923216793596" placeholder="e.g. 923216793596 or 15551234567" required style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 4px;">Message Text</label>
                    <textarea id="testMsg" rows="3" style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.88rem;">🐼 FoodHub Express: This is a test WhatsApp notification! Your food delivery automation is 100% active and connected.</textarea>
                </div>

                <button type="submit" class="checkout-btn" style="width: 100%; margin-top: 0; background: #25d366; box-shadow: 0 4px 14px rgba(37, 211, 102, 0.35);">
                    Send WhatsApp Message ➔
                </button>
            </form>
        </div>

        <!-- Microservice Config Card -->
        <div style="background: white; border-radius: var(--radius-md); border: 1px solid var(--border); padding: 20px; font-size: 0.85rem; color: var(--text-muted);">
            <div style="font-weight: 800; color: var(--secondary); margin-bottom: 6px;">⚙️ Engine Configuration</div>
            <div>• Microservice URL: <code><?= WHATSAPP_API_URL ?></code></div>
            <div>• Default Session: <code><?= WHATSAPP_DEFAULT_SESSION ?></code></div>
            <div>• Engine: <strong>Baileys Multi-Device (Node.js)</strong></div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal-backdrop" id="qrCodeModal">
    <div class="modal-card" style="max-width: 420px; text-align: center;">
        <div style="padding: 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--secondary);">Link WhatsApp Account</h3>
            <button type="button" class="close-btn" onclick="document.getElementById('qrCodeModal').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body" style="padding: 24px;">
            <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 16px;">Open WhatsApp on your phone ➔ Linked Devices ➔ Scan this QR Code:</p>
            <div id="qrImageContainer" style="display: flex; align-items: center; justify-content: center; min-height: 240px;">
                <div style="font-size: 2rem;">⏳ Generating QR...</div>
            </div>
        </div>
    </div>
</div>

<script>
function sendTestWhatsApp(e) {
    e.preventDefault();
    const phone = document.getElementById('testPhone').value.trim();
    const msg = document.getElementById('testMsg').value.trim();

    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Sending... ⏳';

    fetch('api.php?action=send_whatsapp_test', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ phone: phone, message: msg })
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Send WhatsApp Message ➔';
        if (data.success) {
            showAdminToast('WhatsApp message delivered successfully! 💬', 'success');
        } else {
            showAdminToast(data.error || 'Failed to send WhatsApp message', 'error');
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Send WhatsApp Message ➔';
        showAdminToast('Network error connecting to Baileys service', 'error');
    });
}

function refreshWhatsAppStatus() {
    fetch('api.php?action=get_whatsapp_status')
        .then(res => res.json())
        .then(data => {
            showAdminToast('WhatsApp status refreshed!', 'success');
            setTimeout(() => window.location.reload(), 500);
        });
}

function startWhatsAppSession() {
    document.getElementById('qrCodeModal').classList.add('active');
    fetch('api.php?action=start_whatsapp_session')
        .then(res => res.json())
        .then(data => {
            if (data.qrImage) {
                document.getElementById('qrImageContainer').innerHTML = `<img src="${data.qrImage}" style="width: 240px; height: 240px; border-radius: 12px; border: 1px solid #cbd5e1;">`;
            } else if (data.status === 'connected') {
                document.getElementById('qrImageContainer').innerHTML = `<div style="color: #10b981; font-weight: 800;">✅ Already Connected!</div>`;
                setTimeout(() => window.location.reload(), 1000);
            }
        });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
