/**
 * FoodHub Express - 2-Step Authentication & Phone OTP Verification Engine
 * Step 1: Email Identification
 * Step 2: WhatsApp / Phone OTP Verification
 */

class AuthManager {
    constructor() {
        this.currentUser = null;
        this.currentEmail = '';
        this.currentPhone = '';
        this.otpCountdownTimer = null;
        this.otpCountdownSeconds = 60;
        this.init();
    }

    init() {
        this.fetchCurrentUser();
        this.bindEvents();
    }

    fetchCurrentUser() {
        fetch('api/auth.php?action=get_current_user')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.authenticated && data.user) {
                    this.currentUser = data.user;
                    localStorage.setItem('foodhub_user', JSON.stringify(data.user));
                } else {
                    this.currentUser = null;
                    localStorage.removeItem('foodhub_user');
                }
                this.updateAuthNavUI();
            })
            .catch(() => {
                const saved = localStorage.getItem('foodhub_user');
                if (saved) {
                    try {
                        this.currentUser = JSON.parse(saved);
                    } catch(e) {}
                }
                this.updateAuthNavUI();
            });
    }

    bindEvents() {
        // Auth trigger buttons
        document.querySelectorAll('.open-auth-btn, #navLoginBtn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.openModal('email');
            });
        });

        // Close modal buttons
        document.querySelectorAll('.close-auth-modal-btn, #authModalBackdrop').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (e.target === btn || btn.classList.contains('close-auth-modal-btn')) {
                    this.closeModal();
                }
            });
        });

        // Step 1: Email Form Submit
        const emailForm = document.getElementById('authStep1EmailForm');
        if (emailForm) {
            emailForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleEmailStep();
            });
        }

        // Step 2: Phone OTP Request Form Submit
        const phoneForm = document.getElementById('authStep2PhoneForm');
        if (phoneForm) {
            phoneForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.sendPhoneOTP();
            });
        }

        // Step 3: OTP Code Verify Form Submit
        const otpForm = document.getElementById('authStep3OtpForm');
        if (otpForm) {
            otpForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.verifyOTP();
            });
        }

        // Password Login Form Submit
        const pwdForm = document.getElementById('authPasswordLoginForm');
        if (pwdForm) {
            pwdForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.loginWithPassword();
            });
        }
    }

    openModal(step = 'email') {
        const modal = document.getElementById('authModalBackdrop');
        if (!modal) return;

        this.showStep(step);
        modal.classList.add('active');

        // Focus input
        setTimeout(() => {
            if (step === 'email') document.getElementById('authInputEmail')?.focus();
            if (step === 'phone') document.getElementById('authInputPhone')?.focus();
            if (step === 'otp') document.getElementById('authInputOtpCode')?.focus();
        }, 150);
    }

    closeModal() {
        const modal = document.getElementById('authModalBackdrop');
        if (modal) {
            modal.classList.remove('active');
        }
        clearInterval(this.otpCountdownTimer);
    }

    showStep(stepName) {
        document.querySelectorAll('.auth-step-panel').forEach(panel => panel.style.display = 'none');
        const targetPanel = document.getElementById(`authStep_${stepName}`);
        if (targetPanel) {
            targetPanel.style.display = 'block';
        }
    }

    // Step 1: Process Email
    handleEmailStep() {
        const emailInput = document.getElementById('authInputEmail');
        const email = emailInput?.value.trim().toLowerCase();

        if (!email) {
            showToast('Please enter your email address', 'error');
            return;
        }

        this.currentEmail = email;
        const btn = document.getElementById('btnSubmitEmailStep');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Checking... ⏳';
        }

        fetch('api/auth.php?action=check_email', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Continue with Email ➔';
            }

            if (data.success) {
                document.querySelectorAll('.auth-display-email').forEach(el => el.textContent = email);

                if (data.exists) {
                    // Existing User: Prompt for Password or Phone OTP
                    if (data.user.phone) {
                        this.currentPhone = data.user.phone;
                        document.getElementById('authInputPhone').value = data.user.phone;
                    }
                    if (data.user.has_password) {
                        this.showStep('password');
                    } else {
                        this.showStep('phone');
                    }
                } else {
                    // New User: Proceed to Step 2 (Phone verification)
                    this.showStep('phone');
                }
            } else {
                showToast(data.error || 'Invalid email', 'error');
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Continue with Email ➔';
            }
            showToast('Connection error, please try again', 'error');
            console.error(err);
        });
    }

    // Step 2: Request 6-digit WhatsApp Phone OTP
    sendPhoneOTP() {
        const phoneInput = document.getElementById('authInputPhone');
        const nameInput = document.getElementById('authInputName');
        const phone = phoneInput?.value.trim();
        const name = nameInput?.value.trim() || '';

        if (!phone) {
            showToast('Please enter your phone number', 'error');
            return;
        }

        this.currentPhone = phone;
        const btn = document.getElementById('btnSendPhoneOtp');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Sending Code via WhatsApp... ⏳';
        }

        fetch('api/auth.php?action=send_phone_otp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: this.currentEmail,
                phone: phone,
                name: name
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Send Verification Code ➔';
            }

            if (data.success) {
                document.querySelectorAll('.auth-display-phone').forEach(el => el.textContent = data.phone_masked || phone);
                
                // If OTP preview is provided, fill hint for easy testing
                const hintEl = document.getElementById('authOtpDevHint');
                if (hintEl && data.otp_preview) {
                    hintEl.innerHTML = `🔑 <b>WhatsApp OTP Code:</b> <span style="font-family: monospace; color: #ff2b85; font-size: 1.1rem;">${data.otp_preview}</span>`;
                    hintEl.style.display = 'block';
                }

                showToast(`🔐 Verification code sent to WhatsApp (${data.phone_masked || phone})!`, 'success');
                this.showStep('otp');
                this.startOtpTimer();
            } else {
                showToast(data.error || 'Failed to send OTP code', 'error');
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Send Verification Code ➔';
            }
            showToast('Failed to send verification code', 'error');
            console.error(err);
        });
    }

    // Step 3: Verify OTP Code
    verifyOTP() {
        const otpInput = document.getElementById('authInputOtpCode');
        const otp = otpInput?.value.trim();
        const name = document.getElementById('authInputName')?.value.trim() || '';
        const password = document.getElementById('authInputNewPassword')?.value || '';

        if (!otp || otp.length < 4) {
            showToast('Please enter the 6-digit code sent to your WhatsApp', 'error');
            return;
        }

        const btn = document.getElementById('btnVerifyOtpSubmit');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Verifying... ⏳';
        }

        fetch('api/auth.php?action=verify_phone_otp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: this.currentEmail,
                phone: this.currentPhone,
                otp_code: otp,
                name: name,
                password: password
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Verify & Activate Account 🎉';
            }

            if (data.success) {
                this.currentUser = data.user;
                localStorage.setItem('foodhub_user', JSON.stringify(data.user));
                this.updateAuthNavUI();
                this.closeModal();
                showToast(`🎉 ${data.message}`, 'success');

                // Auto-fill checkout fields if open
                this.autoFillCheckout();
            } else {
                showToast(data.error || 'Invalid OTP verification code', 'error');
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Verify & Activate Account 🎉';
            }
            showToast('Verification failed, please try again', 'error');
            console.error(err);
        });
    }

    // Login with Password
    loginWithPassword() {
        const pwdInput = document.getElementById('authInputLoginPassword');
        const password = pwdInput?.value;

        if (!password) {
            showToast('Please enter your password', 'error');
            return;
        }

        const btn = document.getElementById('btnLoginWithPasswordSubmit');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Logging in... ⏳';
        }

        fetch('api/auth.php?action=login_password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: this.currentEmail,
                password: password
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Sign In ➔';
            }

            if (data.success) {
                this.currentUser = data.user;
                localStorage.setItem('foodhub_user', JSON.stringify(data.user));
                this.updateAuthNavUI();
                this.closeModal();
                showToast(`Welcome back, ${data.user.name}! 🍔`, 'success');
                this.autoFillCheckout();
            } else {
                showToast(data.error || 'Invalid password', 'error');
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Sign In ➔';
            }
            showToast('Login failed', 'error');
            console.error(err);
        });
    }

    logout() {
        fetch('api/auth.php?action=logout')
            .then(() => {
                this.currentUser = null;
                localStorage.removeItem('foodhub_user');
                this.updateAuthNavUI();
                showToast('You have been logged out safely', 'info');
                setTimeout(() => window.location.reload(), 600);
            });
    }

    startOtpTimer() {
        clearInterval(this.otpCountdownTimer);
        this.otpCountdownSeconds = 60;
        const timerEl = document.getElementById('authOtpCountdown');
        const resendBtn = document.getElementById('btnResendOtp');

        if (resendBtn) resendBtn.disabled = true;

        this.otpCountdownTimer = setInterval(() => {
            this.otpCountdownSeconds--;
            if (timerEl) timerEl.textContent = `(${this.otpCountdownSeconds}s)`;

            if (this.otpCountdownSeconds <= 0) {
                clearInterval(this.otpCountdownTimer);
                if (timerEl) timerEl.textContent = '';
                if (resendBtn) resendBtn.disabled = false;
            }
        }, 1000);
    }

    updateAuthNavUI() {
        const navContainer = document.getElementById('userNavAuthContainer');
        if (!navContainer) return;

        if (this.currentUser) {
            navContainer.innerHTML = `
                <div class="user-profile-nav-dropdown" style="position: relative; display: inline-block;">
                    <button type="button" class="nav-link-btn" onclick="toggleUserNavMenu()" style="background: var(--bg-surface); padding: 6px 14px; border: 1px solid var(--border); font-weight: 700;">
                        <span>👤</span>
                        <span style="max-width: 110px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Hi, ${this.currentUser.name}</span>
                        <span style="font-size: 0.75rem;">▼</span>
                    </button>
                    <div id="userNavDropdownMenu" style="position: absolute; right: 0; top: calc(100% + 8px); background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-lg); border: 1px solid var(--border); min-width: 190px; display: none; z-index: 150; overflow: hidden;">
                        <div style="padding: 12px 16px; border-bottom: 1px solid var(--border); background: #f8fafc;">
                            <div style="font-weight: 800; font-size: 0.92rem; color: #1e1e2d;">${this.currentUser.name}</div>
                            <div style="font-size: 0.78rem; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${this.currentUser.email}</div>
                            <div style="font-size: 0.75rem; color: #10b981; font-weight: 700; margin-top: 2px;">🟢 WhatsApp Verified</div>
                        </div>
                        <a href="track.php" style="display: block; padding: 10px 16px; font-size: 0.88rem; font-weight: 600; color: #1e1e2d; border-bottom: 1px solid #f1f5f9;">🛵 My Orders</a>
                        <a href="deals.php" style="display: block; padding: 10px 16px; font-size: 0.88rem; font-weight: 600; color: #1e1e2d; border-bottom: 1px solid #f1f5f9;">🏷️ My Vouchers</a>
                        <button type="button" onclick="window.authManager.logout()" style="width: 100%; text-align: left; padding: 10px 16px; font-size: 0.88rem; font-weight: 700; color: #ef4444; background: none; border: none; cursor: pointer;">
                            🚪 Log Out
                        </button>
                    </div>
                </div>
            `;
        } else {
            navContainer.innerHTML = `
                <button type="button" class="nav-link-btn open-auth-btn" style="background: var(--bg-surface); padding: 8px 14px; font-weight: 700; color: var(--secondary);">
                    <span>👤</span>
                    <span>Sign In</span>
                </button>
            `;
        }
    }

    autoFillCheckout() {
        if (!this.currentUser) return;
        const nameInp = document.getElementById('checkoutCustomerName');
        const phoneInp = document.getElementById('checkoutCustomerPhone');
        const emailInp = document.getElementById('checkoutCustomerEmail');
        const addrInp = document.getElementById('checkoutDeliveryAddress');

        if (nameInp && !nameInp.value) nameInp.value = this.currentUser.name || '';
        if (phoneInp && !phoneInp.value) phoneInp.value = this.currentUser.phone || '';
        if (emailInp && !emailInp.value) emailInp.value = this.currentUser.email || '';
        if (addrInp && !addrInp.value && this.currentUser.default_address) addrInp.value = this.currentUser.default_address;
    }
}

function toggleUserNavMenu() {
    const menu = document.getElementById('userNavDropdownMenu');
    if (menu) {
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('.user-profile-nav-dropdown')) {
        const menu = document.getElementById('userNavDropdownMenu');
        if (menu) menu.style.display = 'none';
    }
});

// Global instance
window.authManager = new AuthManager();
