<?php
require_once __DIR__ . '/data/config.php';
$pageTitle = "Sign In & WhatsApp Verification - FoodHub";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 60px 16px; min-height: 75vh; display: flex; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); border: 1px solid var(--border); max-width: 480px; width: 100%; overflow: hidden;">
        
        <div style="background: linear-gradient(135deg, #ff2b85 0%, #ff5e62 100%); padding: 32px 24px; text-align: center; color: white;">
            <span style="font-size: 3rem; display: inline-block; margin-bottom: 8px;">🐼</span>
            <h2 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 6px;">Welcome to FoodHub</h2>
            <p style="font-size: 0.92rem; opacity: 0.95;">Sign in or create account with 2-Step WhatsApp Verification</p>
        </div>

        <div style="padding: 32px 24px;">
            
            <!-- STEP 1: EMAIL -->
            <div class="auth-step-panel" id="authStep_email">
                <div style="text-align: center; margin-bottom: 24px;">
                    <div style="font-size: 2.2rem; margin-bottom: 6px;">✉️</div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--secondary);">Enter your Email</h3>
                    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 4px;">Step 1: We'll verify your email address first.</p>
                </div>

                <form id="authStep1EmailForm">
                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 6px;">Email Address *</label>
                        <input type="email" id="authInputEmail" required placeholder="name@example.com" style="width: 100%; padding: 12px 16px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.95rem; outline: none;">
                    </div>

                    <button type="submit" id="btnSubmitEmailStep" class="checkout-btn" style="width: 100%; margin-top: 0; padding: 12px; font-size: 1rem;">
                        Continue with Email ➔
                    </button>
                </form>
            </div>

            <!-- STEP 2: PHONE NUMBER ENTRY -->
            <div class="auth-step-panel" id="authStep_phone" style="display: none;">
                <div style="text-align: center; margin-bottom: 24px;">
                    <span class="badge-tag" style="position: static; background: var(--primary-light); color: var(--primary); font-size: 0.75rem; margin-bottom: 8px;">STEP 2 OF 2</span>
                    <div style="font-size: 2.2rem; margin-bottom: 6px;">📱</div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--secondary);">Phone Verification</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
                        We'll send a 6-digit WhatsApp OTP to verify <strong class="auth-display-email" style="color: var(--secondary);"></strong>
                    </p>
                </div>

                <form id="authStep2PhoneForm">
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 6px;">Your Full Name</label>
                        <input type="text" id="authInputName" placeholder="e.g. Sarah Jenkins" style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem; outline: none;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 6px;">WhatsApp Phone Number *</label>
                        <input type="tel" id="authInputPhone" required placeholder="e.g. 923216793596" style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.95rem; font-weight: 700; outline: none;">
                        <div style="font-size: 0.78rem; color: #10b981; margin-top: 4px; font-weight: 600;">💬 Code delivered directly via WhatsApp Bot</div>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 6px;">Create Password (Optional)</label>
                        <input type="password" id="authInputNewPassword" placeholder="••••••••" style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem; outline: none;">
                    </div>

                    <button type="submit" id="btnSendPhoneOtp" class="checkout-btn" style="width: 100%; margin-top: 0; padding: 12px; background: #25d366; box-shadow: 0 4px 14px rgba(37, 211, 102, 0.35);">
                        Send Code via WhatsApp ➔
                    </button>

                    <button type="button" onclick="window.authManager.showStep('email')" class="btn-action-pill" style="width: 100%; margin-top: 10px; background: #f1f5f9; color: var(--text-muted); text-align: center; padding: 8px;">
                        ← Change Email
                    </button>
                </form>
            </div>

            <!-- STEP 3: OTP VERIFICATION -->
            <div class="auth-step-panel" id="authStep_otp" style="display: none;">
                <div style="text-align: center; margin-bottom: 24px;">
                    <div style="font-size: 2.2rem; margin-bottom: 6px;">🔐</div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--secondary);">Enter 6-Digit Code</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
                        We sent a verification code to WhatsApp: <strong class="auth-display-phone" style="color: var(--secondary);"></strong>
                    </p>
                </div>

                <div id="authOtpDevHint" style="display: none; background: #fff0f5; padding: 10px 14px; border-radius: var(--radius-sm); border: 1px dashed #fbcfe8; margin-bottom: 16px; text-align: center; font-size: 0.88rem;"></div>

                <form id="authStep3OtpForm">
                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 6px;">Verification Code (OTP) *</label>
                        <input type="text" id="authInputOtpCode" maxlength="6" required placeholder="123456" style="width: 100%; padding: 12px; border: 2px solid var(--primary); border-radius: var(--radius-sm); font-size: 1.4rem; font-weight: 800; text-align: center; letter-spacing: 6px; outline: none; font-family: monospace;">
                    </div>

                    <button type="submit" id="btnVerifyOtpSubmit" class="checkout-btn" style="width: 100%; margin-top: 0; padding: 12px;">
                        Verify & Activate Account 🎉
                    </button>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; font-size: 0.85rem;">
                        <button type="button" onclick="window.authManager.showStep('phone')" style="color: var(--text-muted); font-weight: 600; background: none; border: none; cursor: pointer;">
                            ← Change Number
                        </button>
                        <button type="button" id="btnResendOtp" onclick="window.authManager.sendPhoneOTP()" style="color: var(--primary); font-weight: 700; background: none; border: none; cursor: pointer;">
                            Resend Code <span id="authOtpCountdown"></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- PASSWORD LOGIN FOR RETURNING USERS -->
            <div class="auth-step-panel" id="authStep_password" style="display: none;">
                <div style="text-align: center; margin-bottom: 24px;">
                    <div style="font-size: 2.2rem; margin-bottom: 6px;">👋</div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--secondary);">Welcome Back!</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
                        Enter password for <strong class="auth-display-email" style="color: var(--secondary);"></strong>
                    </p>
                </div>

                <form id="authPasswordLoginForm">
                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 6px;">Password *</label>
                        <input type="password" id="authInputLoginPassword" required placeholder="••••••••" style="width: 100%; padding: 12px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.95rem; outline: none;">
                    </div>

                    <button type="submit" id="btnLoginWithPasswordSubmit" class="checkout-btn" style="width: 100%; margin-top: 0; padding: 12px;">
                        Sign In ➔
                    </button>

                    <div style="text-align: center; margin-top: 14px;">
                        <button type="button" onclick="window.authManager.showStep('phone')" style="font-size: 0.85rem; color: #25d366; font-weight: 700; background: none; border: none; cursor: pointer;">
                            💬 Sign In with WhatsApp OTP instead
                        </button>
                    </div>

                    <button type="button" onclick="window.authManager.showStep('email')" class="btn-action-pill" style="width: 100%; margin-top: 10px; background: #f1f5f9; color: var(--text-muted); text-align: center; padding: 8px;">
                        ← Use Different Email
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
