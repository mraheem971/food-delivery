-- Food Delivery App MySQL Database Schema & Seed Data
-- Database: `food_delivery_db`

CREATE DATABASE IF NOT EXISTS `food_delivery_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `food_delivery_db`;

-- 1. Categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `icon` VARCHAR(50) NOT NULL,
  `image_url` VARCHAR(500) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Restaurants
CREATE TABLE IF NOT EXISTS `restaurants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `tagline` VARCHAR(255) NULL,
  `image_url` VARCHAR(500) NOT NULL,
  `banner_url` VARCHAR(500) NOT NULL,
  `rating` DECIMAL(3,1) DEFAULT 4.5,
  `rating_count` INT DEFAULT 120,
  `delivery_time` VARCHAR(50) DEFAULT '25-35 min',
  `delivery_fee` DECIMAL(5,2) DEFAULT 1.99,
  `min_order` DECIMAL(5,2) DEFAULT 10.00,
  `cuisine_type` VARCHAR(255) NOT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_free_delivery` TINYINT(1) DEFAULT 0,
  `is_halal` TINYINT(1) DEFAULT 1,
  `address` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Menu Items
CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `restaurant_id` INT NOT NULL,
  `category_name` VARCHAR(100) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(6,2) NOT NULL,
  `original_price` DECIMAL(6,2) NULL,
  `image_url` VARCHAR(500) NULL,
  `is_popular` TINYINT(1) DEFAULT 0,
  `is_vegetarian` TINYINT(1) DEFAULT 0,
  `is_vegan` TINYINT(1) DEFAULT 0,
  `is_gluten_free` TINYINT(1) DEFAULT 0,
  `is_spicy` TINYINT(1) DEFAULT 0,
  `calories` INT NULL,
  `prep_time` VARCHAR(50) DEFAULT '15-20 min',
  INDEX (`restaurant_id`),
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Customization Options (Add-ons, Sizes)
CREATE TABLE IF NOT EXISTS `menu_item_options` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `item_id` INT NOT NULL,
  `group_name` VARCHAR(100) NOT NULL,
  `option_name` VARCHAR(150) NOT NULL,
  `additional_price` DECIMAL(5,2) DEFAULT 0.00,
  `is_required` TINYINT(1) DEFAULT 0,
  `max_choices` INT DEFAULT 1,
  INDEX (`item_id`),
  FOREIGN KEY (`item_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Coupons & Deals
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `discount_type` ENUM('percentage', 'fixed') NOT NULL,
  `discount_value` DECIMAL(6,2) NOT NULL,
  `min_order_amount` DECIMAL(6,2) DEFAULT 0.00,
  `max_discount` DECIMAL(6,2) DEFAULT 100.00,
  `expires_at` DATE NULL,
  `badge` VARCHAR(50) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `customer_name` VARCHAR(150) NOT NULL,
  `customer_phone` VARCHAR(50) NOT NULL,
  `customer_email` VARCHAR(150) NULL,
  `delivery_address` TEXT NOT NULL,
  `delivery_instructions` TEXT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `restaurant_id` INT NOT NULL,
  `restaurant_name` VARCHAR(150) NOT NULL,
  `subtotal` DECIMAL(7,2) NOT NULL,
  `delivery_fee` DECIMAL(5,2) NOT NULL,
  `platform_fee` DECIMAL(5,2) DEFAULT 0.50,
  `discount_amount` DECIMAL(6,2) DEFAULT 0.00,
  `driver_tip` DECIMAL(5,2) DEFAULT 0.00,
  `total_amount` DECIMAL(7,2) NOT NULL,
  `applied_coupon` VARCHAR(50) NULL,
  `order_status` VARCHAR(50) DEFAULT 'placed',
  `estimated_delivery_time` VARCHAR(50) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`restaurant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Order Items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `item_name` VARCHAR(150) NOT NULL,
  `item_price` DECIMAL(6,2) NOT NULL,
  `quantity` INT NOT NULL,
  `selected_options` TEXT NULL,
  `special_instructions` TEXT NULL,
  `item_total` DECIMAL(6,2) NOT NULL,
  INDEX (`order_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Reviews
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `restaurant_id` INT NOT NULL,
  `customer_name` VARCHAR(150) NOT NULL,
  `rating` INT NOT NULL,
  `comment` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`restaurant_id`),
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
