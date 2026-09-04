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
if (!defined('APP_CURRENCY')) {
    define('APP_CURRENCY', '$');
}
