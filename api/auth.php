<?php
/**
 * User Authentication & 2-Step Phone Verification API
 * Step 1: Email Verification / Identification
 * Step 2: Phone Number OTP Verification via WhatsApp / SMS
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../data/db.php';
require_once __DIR__ . '/../data/whatsapp.php';

try {
    $db = getDB();
    $action = $_GET['action'] ?? '';

    // 1. STEP 1: CHECK / VALIDATE EMAIL
    if ($action === 'check_email') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $email = strtolower(trim($input['email'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Please enter a valid email address']);
            exit;
        }

        $stmt = $db->prepare("SELECT id, name, email, phone, is_phone_verified, default_address, avatar_url, (password_hash IS NOT NULL AND password_hash != '') as has_password FROM `users` WHERE `email` = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            echo json_encode([
                'success' => true,
                'exists' => true,
                'message' => 'Existing account found. You can log in with password or verify via WhatsApp phone OTP.',
                'user' => [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'is_phone_verified' => (bool)$user['is_phone_verified'],
                    'has_password' => (bool)$user['has_password']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'exists' => false,
                'message' => 'New user! Please proceed to phone verification to secure your account.'
            ]);
        }
        exit;
    }

    // 2. STEP 2: SEND PHONE VERIFICATION OTP (WhatsApp + Fallback)
    if ($action === 'send_phone_otp') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $email = strtolower(trim($input['email'] ?? ''));
        $phone = trim($input['phone'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Valid email is required']);
            exit;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($cleanPhone) || strlen($cleanPhone) < 7) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Please enter a valid phone number with country code (e.g. 923216793596)']);
            exit;
        }

        // Generate secure 6-digit OTP
        $otp = (string)random_int(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Store OTP in database
        $stmt = $db->prepare("INSERT INTO `phone_verifications` (`email`, `phone`, `otp_code`, `expires_at`, `is_used`) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$email, $cleanPhone, $otp, $expiresAt]);

        // Mask phone for privacy in UI
        $len = strlen($cleanPhone);
        $maskedPhone = substr($cleanPhone, 0, 4) . str_repeat('*', max(0, $len - 7)) . substr($cleanPhone, -3);

        // Send OTP via WhatsApp Bot
        $waMessage = "🐼 *FOODHUB VERIFICATION CODE* 🔐\n"
                   . "━━━━━━━━━━━━━━━━━━━━━\n"
                   . "Your login/registration verification code is:\n\n"
                   . "👉 *{$otp}*\n\n"
                   . "⏱️ This code will expire in *10 minutes*.\n"
                   . "Do not share this OTP with anyone for your account security.\n"
                   . "━━━━━━━━━━━━━━━━━━━━━\n"
                   . "FoodHub Express • Delicious Food Delivered Fast";

        $waResult = WhatsAppService::sendMessage($cleanPhone, $waMessage);

        echo json_encode([
            'success' => true,
            'message' => "Verification code sent to WhatsApp (+{$maskedPhone})",
            'phone_masked' => "+{$maskedPhone}",
            'otp_preview' => $otp, // preview code for rapid testing/development
            'whatsapp_sent' => !empty($waResult['success'])
        ]);
        exit;
    }

    // 3. STEP 3: VERIFY PHONE OTP & ACTIVATE / LOGIN ACCOUNT
    if ($action === 'verify_phone_otp') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $email = strtolower(trim($input['email'] ?? ''));
        $phone = trim($input['phone'] ?? '');
        $otpCode = trim($input['otp_code'] ?? '');
        $name = trim($input['name'] ?? '');
        $password = trim($input['password'] ?? '');

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        if (empty($email) || empty($cleanPhone) || empty($otpCode)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Email, phone, and OTP code are required']);
            exit;
        }

        // Verify OTP from database
        $stmt = $db->prepare("SELECT * FROM `phone_verifications` 
            WHERE `email` = ? AND `phone` = ? AND `otp_code` = ? AND `is_used` = 0 AND `expires_at` >= NOW() 
            ORDER BY `id` DESC LIMIT 1");
        $stmt->execute([$email, $cleanPhone, $otpCode]);
        $verification = $stmt->fetch();

        if (!$verification) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid or expired verification code. Please check or request a new code.']);
            exit;
        }

        // Mark OTP as used
        $stmtUse = $db->prepare("UPDATE `phone_verifications` SET `is_used` = 1 WHERE `id` = ?");
        $stmtUse->execute([$verification['id']]);

        // Check if user exists by email first (since auth is email-first)
        $stmtUser = $db->prepare("SELECT * FROM `users` WHERE `email` = ?");
        $stmtUser->execute([$email]);
        $user = $stmtUser->fetch();

        if (!$user) {
            // Check if user exists by phone
            $stmtPhone = $db->prepare("SELECT * FROM `users` WHERE `phone` = ?");
            $stmtPhone->execute([$cleanPhone]);
            $user = $stmtPhone->fetch();
        }

        if ($user) {
            // Update existing user verified status and update name / password / email if supplied
            $updates = ["`is_phone_verified` = 1", "`phone` = ?", "`email` = ?", "`last_login` = CURRENT_TIMESTAMP"];
            $params = [$cleanPhone, $email];
            if (!empty($name)) {
                $updates[] = "`name` = ?";
                $params[] = $name;
                $user['name'] = $name;
            }
            if (!empty($password)) {
                $updates[] = "`password_hash` = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
            $params[] = $user['id'];
            $stmtUpd = $db->prepare("UPDATE `users` SET " . implode(", ", $updates) . " WHERE `id` = ?");
            $stmtUpd->execute($params);
            $user['is_phone_verified'] = 1;
            $user['phone'] = $cleanPhone;
            $user['email'] = $email;
        } else {
            // Register new user
            $displayName = !empty($name) ? $name : explode('@', $email)[0];
            $pwdHash = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

            $stmtNew = $db->prepare("INSERT INTO `users` (`name`, `email`, `phone`, `password_hash`, `is_phone_verified`, `is_email_verified`, `last_login`) VALUES (?, ?, ?, ?, 1, 1, CURRENT_TIMESTAMP)");
            $stmtNew->execute([$displayName, $email, $cleanPhone, $pwdHash]);
            $userId = $db->lastInsertId();

            $stmtGet = $db->prepare("SELECT * FROM `users` WHERE `id` = ?");
            $stmtGet->execute([$userId]);
            $user = $stmtGet->fetch();
        }

        unset($user['password_hash']);
        $_SESSION['user'] = $user;

        echo json_encode([
            'success' => true,
            'message' => "Phone number verified successfully! Welcome, {$user['name']}! 🎉",
            'user' => $user
        ]);
        exit;
    }

    // 4. LOGIN WITH PASSWORD (FOR RETURNING USERS)
    if ($action === 'login_password') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $email = strtolower(trim($input['email'] ?? ''));
        $password = trim($input['password'] ?? '');

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Email and password are required']);
            exit;
        }

        $stmt = $db->prepare("SELECT * FROM `users` WHERE `email` = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
            exit;
        }

        $db->prepare("UPDATE `users` SET `last_login` = CURRENT_TIMESTAMP WHERE `id` = ?")->execute([$user['id']]);

        unset($user['password_hash']);
        $_SESSION['user'] = $user;

        echo json_encode([
            'success' => true,
            'message' => "Welcome back, {$user['name']}! 🍔",
            'user' => $user
        ]);
        exit;
    }

    // 5. GET CURRENT AUTHENTICATED USER
    if ($action === 'get_current_user') {
        if (!empty($_SESSION['user'])) {
            $stmt = $db->prepare("SELECT id, name, email, phone, is_phone_verified, default_address, avatar_url, created_at FROM `users` WHERE `id` = ?");
            $stmt->execute([$_SESSION['user']['id']]);
            $freshUser = $stmt->fetch();
            if ($freshUser) {
                $_SESSION['user'] = $freshUser;
                echo json_encode(['success' => true, 'authenticated' => true, 'user' => $freshUser]);
                exit;
            }
        }

        echo json_encode(['success' => true, 'authenticated' => false, 'user' => null]);
        exit;
    }

    // 6. LOGOUT
    if ($action === 'logout') {
        unset($_SESSION['user']);
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
        exit;
    }

    // 7. UPDATE PROFILE & ADDRESS
    if ($action === 'update_profile') {
        if (empty($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Please log in first']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $name = trim($input['name'] ?? $_SESSION['user']['name']);
        $address = trim($input['default_address'] ?? '');

        $stmt = $db->prepare("UPDATE `users` SET `name` = ?, `default_address` = ? WHERE `id` = ?");
        $stmt->execute([$name, $address, $_SESSION['user']['id']]);

        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['default_address'] = $address;

        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'user' => $_SESSION['user']
        ]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid authentication action']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
