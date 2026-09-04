<?php
require_once __DIR__ . '/data/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top: 28px; padding-bottom: 60px;">
    <!-- Help Center Hero -->
    <div style="background: linear-gradient(135deg, #ff2b85 0%, #700336 100%); color: white; padding: 40px 36px; border-radius: var(--radius-lg); margin-bottom: 36px; text-align: center;">
        <span class="hero-tag" style="background: rgba(255, 255, 255, 0.2); color: white;">💡 Customer Care & Resource Hub</span>
        <h1 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 10px;">How Can We Help You Today?</h1>
        <p style="opacity: 0.95; font-size: 1rem; max-width: 600px; margin: 0 auto 20px;">Find answers regarding delivery tracking, dietary information, refunds, and promo vouchers.</p>
        
        <button type="button" class="checkout-btn" onclick="document.getElementById('floatingSupportBtn').click();" style="width: auto; margin: 0 auto; display: inline-flex; padding: 12px 28px; background: white; color: var(--primary);">
            <span>💬 Chat with 24/7 AI Assistant</span>
        </button>
    </div>

    <!-- Dietary & Allergen Guide Section -->
    <section id="nutrition" style="background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); padding: 32px; margin-bottom: 36px; box-shadow: var(--shadow-sm);">
        <div class="section-header">
            <h2 class="section-title">🥗 Dietary & Allergen Safety Guide</h2>
        </div>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 24px;">We are committed to food safety and transparency. Every menu dish in FoodHub Express is categorized with verified dietary badges:</p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
            <div style="padding: 20px; border-radius: var(--radius-md); background: var(--bg-surface); border-left: 4px solid #059669;">
                <div style="font-size: 1.1rem; font-weight: 800; margin-bottom: 6px; color: #059669;">🌙 100% Halal Certified</div>
                <p style="font-size: 0.88rem; color: var(--text-muted);">Restaurants tagged with Halal source meats and ingredients from certified suppliers adhering to strict standards.</p>
            </div>

            <div style="padding: 20px; border-radius: var(--radius-md); background: var(--bg-surface); border-left: 4px solid #10b981;">
                <div style="font-size: 1.1rem; font-weight: 800; margin-bottom: 6px; color: #10b981;">🥬 Vegetarian & Vegan</div>
                <p style="font-size: 0.88rem; color: var(--text-muted);">Plant-based bowls, dairy-free pizzas, and fresh organic greens prepared without animal broths or gelatin.</p>
            </div>

            <div style="padding: 20px; border-radius: var(--radius-md); background: var(--bg-surface); border-left: 4px solid #3b82f6;">
                <div style="font-size: 1.1rem; font-weight: 800; margin-bottom: 6px; color: #3b82f6;">🌾 Gluten-Free Options</div>
                <p style="font-size: 0.88rem; color: var(--text-muted);">Quinoa bowls and gluten-friendly crusts. Please mention severe celiac allergies in the dish special instructions.</p>
            </div>

            <div style="padding: 20px; border-radius: var(--radius-md); background: var(--bg-surface); border-left: 4px solid #ef4444;">
                <div style="font-size: 1.1rem; font-weight: 800; margin-bottom: 6px; color: #ef4444;">🔥 Spice Level Meters</div>
                <p style="font-size: 0.88rem; color: var(--text-muted);">Customize heat from Mild (🔥) to Inferno (🔥🔥🔥) on spicy items like Nashville chicken and birria tacos.</p>
            </div>
        </div>
    </section>

    <!-- Frequently Asked Questions Accordion -->
    <section style="background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 36px;">
        <div class="section-header">
            <h2 class="section-title">❓ Frequently Asked Questions (FAQ)</h2>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px;">
            <details style="background: var(--bg-surface); padding: 16px 20px; border-radius: var(--radius-md); cursor: pointer;" open>
                <summary style="font-weight: 800; font-size: 1rem; color: var(--secondary);">How does the live GPS motorcycle tracking work?</summary>
                <p style="margin-top: 10px; font-size: 0.9rem; color: var(--text-muted); line-height: 1.5;">
                    Once your order is placed and confirmed by the kitchen, our system assigns a nearby delivery rider. You can track their live progress on the interactive map with real-time countdown minutes directly on the <code>track.php</code> screen.
                </p>
            </details>

            <details style="background: var(--bg-surface); padding: 16px 20px; border-radius: var(--radius-md); cursor: pointer;">
                <summary style="font-weight: 800; font-size: 1rem; color: var(--secondary);">How do I claim a refund if an item is missing or damaged?</summary>
                <p style="margin-top: 10px; font-size: 0.9rem; color: var(--text-muted); line-height: 1.5;">
                    Customer happiness is our top priority! If your dish arrives damaged, cold, or has missing items, simply message our 24/7 AI chat widget or call the rider directly. Instant wallet refunds are processed within 5 minutes.
                </p>
            </details>

            <details style="background: var(--bg-surface); padding: 16px 20px; border-radius: var(--radius-md); cursor: pointer;">
                <summary style="font-weight: 800; font-size: 1rem; color: var(--secondary);">Can I customize dish sizes, spice levels, and add toppings?</summary>
                <p style="margin-top: 10px; font-size: 0.9rem; color: var(--text-muted); line-height: 1.5;">
                    Yes! Click on any dish card in a restaurant menu to open the customization modal. You can choose bun types, extra cheese, boba sweetness/ice levels, and write custom notes for the kitchen staff.
                </p>
            </details>

            <details style="background: var(--bg-surface); padding: 16px 20px; border-radius: var(--radius-md); cursor: pointer;">
                <summary style="font-weight: 800; font-size: 1rem; color: var(--secondary);">What payment methods are supported?</summary>
                <p style="margin-top: 10px; font-size: 0.9rem; color: var(--text-muted); line-height: 1.5;">
                    We support Cash on Delivery (COD) for zero hassle, as well as Credit/Debit cards (Visa, MasterCard) and digital wallets.
                </p>
            </details>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
