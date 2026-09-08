<?php
/**
 * Application & MySQL Database Configuration
 */

if (!defined('DB_HOST')) {
    define('DB_HOST', '127.0.0.1');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', '3306');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'food_delivery_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}

// App Settings
if (!defined('APP_NAME')) {
    define('APP_NAME', 'FoodHub Express');
}
if (!defined('APP_PORT')) {
    define('APP_PORT', 8000);
}
if (!defined('APP_URL')) {
    $host = $_SERVER['HTTP_HOST'] ?? ('localhost:' . APP_PORT);
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    define('APP_URL', $protocol . $host);
}
if (!defined('APP_CURRENCY')) {
    define('APP_CURRENCY', '$');
}

// WhatsApp Bot Settings
if (!defined('WHATSAPP_API_URL')) {
    define('WHATSAPP_API_URL', 'http://127.0.0.1:3000');
}
if (!defined('WHATSAPP_DEFAULT_SESSION')) {
    define('WHATSAPP_DEFAULT_SESSION', 'wa_1788497395_gb6iZITG');
}
if (!defined('WHATSAPP_ADMIN_PHONE')) {
    define('WHATSAPP_ADMIN_PHONE', '923216793596');
}
if (!defined('WHATSAPP_ENABLED')) {
    define('WHATSAPP_ENABLED', true);
}
