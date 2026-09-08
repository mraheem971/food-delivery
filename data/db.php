<?php
/**
 * MySQL PDO Database Connection & Auto-Migrator / Seeder
 */

require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $db = null;
    if ($db === null) {
        $host = DB_HOST;
        $port = DB_PORT;
        $dbname = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;

        try {
            // 1. Connect to MySQL server first
            $initPdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // 2. Ensure database exists
            $initPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // 3. Connect to specific database
            $db = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);

            // 4. Ensure tables exist & seed if empty
            initMySQLSchema($db);

        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Database connection failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    return $db;
}

function initMySQLSchema(PDO $db): void {
    // 1. Categories
    $db->exec("CREATE TABLE IF NOT EXISTS `categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `slug` VARCHAR(100) NOT NULL UNIQUE,
        `icon` VARCHAR(50) NOT NULL,
        `image_url` VARCHAR(500) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Restaurants
    $db->exec("CREATE TABLE IF NOT EXISTS `restaurants` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 3. Menu Items
    $db->exec("CREATE TABLE IF NOT EXISTS `menu_items` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 4. Customization Options (Add-ons, Sizes)
    $db->exec("CREATE TABLE IF NOT EXISTS `menu_item_options` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `item_id` INT NOT NULL,
        `group_name` VARCHAR(100) NOT NULL,
        `option_name` VARCHAR(150) NOT NULL,
        `additional_price` DECIMAL(5,2) DEFAULT 0.00,
        `is_required` TINYINT(1) DEFAULT 0,
        `max_choices` INT DEFAULT 1,
        INDEX (`item_id`),
        FOREIGN KEY (`item_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 5. Coupons & Deals
    $db->exec("CREATE TABLE IF NOT EXISTS `coupons` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 6. Orders
    $db->exec("CREATE TABLE IF NOT EXISTS `orders` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 7. Order Items
    $db->exec("CREATE TABLE IF NOT EXISTS `order_items` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 8. Reviews
    $db->exec("CREATE TABLE IF NOT EXISTS `reviews` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `restaurant_id` INT NOT NULL,
        `customer_name` VARCHAR(150) NOT NULL,
        `rating` INT NOT NULL,
        `comment` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`restaurant_id`),
        FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Check if tables need seeding
    $check = $db->query("SELECT COUNT(*) as cnt FROM `categories`")->fetch();
    if ((int)$check['cnt'] === 0) {
        seedMySQLData($db);
    }
}

function seedMySQLData(PDO $db): void {
    // 1. Categories
    $categories = [
        ['Burgers', 'burgers', '🍔', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&auto=format&fit=crop&q=80'],
        ['Pizza', 'pizza', '🍕', 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=400&auto=format&fit=crop&q=80'],
        ['Asian & Noodles', 'asian', '🍜', 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400&auto=format&fit=crop&q=80'],
        ['Crispy Chicken', 'chicken', '🍗', 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=400&auto=format&fit=crop&q=80'],
        ['Healthy & Bowls', 'healthy', '🥗', 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=400&auto=format&fit=crop&q=80'],
        ['Desserts & Bakery', 'desserts', '🍰', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&auto=format&fit=crop&q=80'],
        ['Beverages & Boba', 'beverages', '🧋', 'https://images.unsplash.com/photo-1558857563-b37cf595c2b0?w=400&auto=format&fit=crop&q=80'],
        ['Mexican & Tacos', 'mexican', '🌮', 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=400&auto=format&fit=crop&q=80']
    ];

    $stmtCat = $db->prepare("INSERT INTO `categories` (`name`, `slug`, `icon`, `image_url`) VALUES (?, ?, ?, ?)");
    foreach ($categories as $cat) {
        $stmtCat->execute($cat);
    }

    // 2. Coupons
    $coupons = [
        ['WELCOME50', '50% OFF First Order', 'Enjoy 50% discount up to $15 on your first culinary adventure!', 'percentage', 50, 15.00, 15.00, '2026-12-31', 'POPULAR'],
        ['FREEDEL', 'Free Delivery Worldwide', 'Get $0 delivery fee on all orders above $20.', 'fixed', 2.99, 20.00, 2.99, '2026-12-31', 'FREE DELIVERY'],
        ['BURGER20', '20% OFF Burger Craze', '20% off all gourmet burger & chicken meals.', 'percentage', 20, 18.00, 8.00, '2026-12-31', 'EXCLUSIVE'],
        ['FEAST10', '$10 Flat Discount', 'Save $10 on family orders above $40.', 'fixed', 10.00, 40.00, 10.00, '2026-12-31', 'FAMILY SAVINGS'],
        ['HEALTHY15', '15% Off Healthy Salads', 'Fuel your day with fresh superbowls and smoothies.', 'percentage', 15, 12.00, 6.00, '2026-12-31', 'WELLNESS']
    ];

    $stmtCpn = $db->prepare("INSERT INTO `coupons` (`code`, `title`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount`, `expires_at`, `badge`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($coupons as $cpn) {
        $stmtCpn->execute($cpn);
    }

    // 3. Restaurants
    $restaurants = [
        [
            'name' => 'Panda Burger & Grill',
            'slug' => 'panda-burger-grill',
            'tagline' => 'Smash burgers, golden brioche & loaded crispy fries',
            'image_url' => 'https://images.unsplash.com/photo-1550547660-d9450f859349?w=600&auto=format&fit=crop&q=80',
            'banner_url' => 'https://images.unsplash.com/photo-1550547660-d9450f859349?w=1200&auto=format&fit=crop&q=80',
            'rating' => 4.9,
            'rating_count' => 640,
            'delivery_time' => '20-30 min',
            'delivery_fee' => 1.49,
            'min_order' => 12.00,
            'cuisine_type' => 'Burgers, American, Fast Food',
            'is_featured' => 1,
            'is_free_delivery' => 0,
            'is_halal' => 1,
            'address' => '422 Flavor Avenue, Downtown Food District'
        ],
        [
            'name' => 'Bella Napoli Pizzeria',
            'slug' => 'bella-napoli-pizzeria',
            'tagline' => 'Authentic wood-fired Neapolitan pizzas & handcrafted pastas',
            'image_url' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=600&auto=format&fit=crop&q=80',
            'banner_url' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=1200&auto=format&fit=crop&q=80',
            'rating' => 4.8,
            'rating_count' => 482,
            'delivery_time' => '25-40 min',
            'delivery_fee' => 0.00,
            'min_order' => 15.00,
            'cuisine_type' => 'Pizza, Italian, Vegetarian',
            'is_featured' => 1,
            'is_free_delivery' => 1,
            'is_halal' => 1,
            'address' => '18 Artisan Street, Little Italy'
        ],
        [
            'name' => 'Tokyo Ramen & Dragon Roll',
            'slug' => 'tokyo-ramen-dragon-roll',
            'tagline' => 'Rich tonkotsu broth, fresh handmade noodles & sushi sets',
            'image_url' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=600&auto=format&fit=crop&q=80',
            'banner_url' => 'https://images.unsplash.com/photo-1552611052-33e04de081de?w=1200&auto=format&fit=crop&q=80',
            'rating' => 4.9,
            'rating_count' => 530,
            'delivery_time' => '15-25 min',
            'delivery_fee' => 1.99,
            'min_order' => 10.00,
            'cuisine_type' => 'Asian, Japanese, Ramen',
            'is_featured' => 1,
            'is_free_delivery' => 0,
            'is_halal' => 0,
            'address' => '88 Sakura Boulevard, East Hub'
        ],
        [
            'name' => 'Green Garden Superbowls',
            'slug' => 'green-garden-superbowls',
            'tagline' => 'Organic keto salads, protein bowls & freshly pressed juices',
            'image_url' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&auto=format&fit=crop&q=80',
            'banner_url' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=1200&auto=format&fit=crop&q=80',
            'rating' => 4.7,
            'rating_count' => 290,
            'delivery_time' => '15-25 min',
            'delivery_fee' => 0.00,
            'min_order' => 12.00,
            'cuisine_type' => 'Healthy, Vegan, Gluten-Free',
            'is_featured' => 0,
            'is_free_delivery' => 1,
            'is_halal' => 1,
            'address' => '104 Wellness Row, Green Valley'
        ],
        [
            'name' => 'El Fuego Cantina & Tacos',
            'slug' => 'el-fuego-cantina-tacos',
            'tagline' => 'Birria tacos with rich consommé, sizzling burritos & nachos',
            'image_url' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=600&auto=format&fit=crop&q=80',
            'banner_url' => 'https://images.unsplash.com/photo-1551504734-5ee1c4a1479b?w=1200&auto=format&fit=crop&q=80',
            'rating' => 4.8,
            'rating_count' => 375,
            'delivery_time' => '25-35 min',
            'delivery_fee' => 1.99,
            'min_order' => 14.00,
            'cuisine_type' => 'Mexican, Tacos, Street Food',
            'is_featured' => 0,
            'is_free_delivery' => 0,
            'is_halal' => 1,
            'address' => '77 Fiesta Plaza, South District'
        ],
        [
            'name' => 'Sweet Tooth Artisan Patisserie',
            'slug' => 'sweet-tooth-artisan-patisserie',
            'tagline' => 'Velvety cheesecake slices, Belgian waffles & specialty bubble tea',
            'image_url' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&auto=format&fit=crop&q=80',
            'banner_url' => 'https://images.unsplash.com/photo-1587314168485-3236d6710814?w=1200&auto=format&fit=crop&q=80',
            'rating' => 4.9,
            'rating_count' => 415,
            'delivery_time' => '15-20 min',
            'delivery_fee' => 1.29,
            'min_order' => 8.00,
            'cuisine_type' => 'Desserts, Bakery, Boba',
            'is_featured' => 1,
            'is_free_delivery' => 0,
            'is_halal' => 1,
            'address' => '50 Sugar Lane, Central Arcade'
        ]
    ];

    $stmtRest = $db->prepare("INSERT INTO `restaurants` (`name`, `slug`, `tagline`, `image_url`, `banner_url`, `rating`, `rating_count`, `delivery_time`, `delivery_fee`, `min_order`, `cuisine_type`, `is_featured`, `is_free_delivery`, `is_halal`, `address`) VALUES (:name, :slug, :tagline, :image_url, :banner_url, :rating, :rating_count, :delivery_time, :delivery_fee, :min_order, :cuisine_type, :is_featured, :is_free_delivery, :is_halal, :address)");

    foreach ($restaurants as $r) {
        $stmtRest->execute($r);
    }

    // 4. Menu Items for Restaurants
    $items = [
        // Panda Burger & Grill (Rest ID 1)
        [
            'restaurant_id' => 1,
            'category_name' => 'Signature Burgers',
            'name' => 'The Double Truffle Panda Burger',
            'description' => 'Two 100% Angus beef patties, melted aged cheddar, caramelized onions, sautéed portobello mushrooms & black truffle aioli in a butter-toasted brioche bun.',
            'price' => 11.99,
            'original_price' => 13.99,
            'image_url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&auto=format&fit=crop&q=80',
            'is_popular' => 1,
            'is_vegetarian' => 0,
            'is_spicy' => 0,
            'calories' => 820,
            'options' => [
                ['group_name' => 'Choose Size', 'option_name' => 'Regular Double Patty', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Choose Size', 'option_name' => 'Triple Beast Patty (+ $3.50)', 'additional_price' => 3.50, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Add Extras', 'option_name' => 'Smoked Beef Bacon', 'additional_price' => 1.50, 'is_required' => 0, 'max_choices' => 3],
                ['group_name' => 'Add Extras', 'option_name' => 'Extra Melted Cheddar', 'additional_price' => 1.00, 'is_required' => 0, 'max_choices' => 3],
                ['group_name' => 'Add Extras', 'option_name' => 'Jalapeño Poppers Side', 'additional_price' => 2.50, 'is_required' => 0, 'max_choices' => 3],
                ['group_name' => 'Beverage Combo', 'option_name' => 'None', 'additional_price' => 0.00, 'is_required' => 0, 'max_choices' => 1],
                ['group_name' => 'Beverage Combo', 'option_name' => 'Fries & Cold Drink Can', 'additional_price' => 3.00, 'is_required' => 0, 'max_choices' => 1]
            ]
        ],
        [
            'restaurant_id' => 1,
            'category_name' => 'Signature Burgers',
            'name' => 'Spicy Nashville Hot Chicken Burger',
            'description' => 'Crispy fried buttermilk chicken breast dipped in Nashville hot spice oil, topped with pickled slaw and house comeback sauce.',
            'price' => 9.99,
            'original_price' => 11.50,
            'image_url' => 'https://images.unsplash.com/photo-1625813506062-0aeb1d7a094b?w=600&auto=format&fit=crop&q=80',
            'is_popular' => 1,
            'is_vegetarian' => 0,
            'is_spicy' => 1,
            'calories' => 740,
            'options' => [
                ['group_name' => 'Spice Level', 'option_name' => 'Mild Heat 🔥', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Spice Level', 'option_name' => 'Medium Kick 🔥🔥', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Spice Level', 'option_name' => 'Inferno Hot 🔥🔥🔥', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Add Extras', 'option_name' => 'Extra Cheese Slice', 'additional_price' => 1.00, 'is_required' => 0, 'max_choices' => 2]
            ]
        ],
        [
            'restaurant_id' => 1,
            'category_name' => 'Sides & Fries',
            'name' => 'Loaded Truffle Parmesan Fries',
            'description' => 'Golden crisp crinkle-cut fries tossed with white truffle oil, shaved parmesan cheese, and fresh parsley with garlic mayo dip.',
            'price' => 5.49,
            'original_price' => null,
            'image_url' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=600&auto=format&fit=crop&q=80',
            'is_popular' => 0,
            'is_vegetarian' => 1,
            'is_spicy' => 0,
            'calories' => 450,
            'options' => [
                ['group_name' => 'Dip Sauce', 'option_name' => 'Garlic Truffle Aioli', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Dip Sauce', 'option_name' => 'Spicy Chipotle Dip', 'additional_price' => 0.50, 'is_required' => 1, 'max_choices' => 1]
            ]
        ],
        [
            'restaurant_id' => 1,
            'category_name' => 'Sides & Fries',
            'name' => 'Smokey BBQ Chicken Wings (6 pcs)',
            'description' => 'Crispy fried wings glazed in sweet hickory barbecue sauce with ranch dipping sauce.',
            'price' => 7.99,
            'original_price' => 8.99,
            'image_url' => 'https://images.unsplash.com/photo-1567620832903-9fc6debc209f?w=600&auto=format&fit=crop&q=80',
            'is_popular' => 1,
            'is_vegetarian' => 0,
            'is_spicy' => 1,
            'calories' => 580,
            'options' => []
        ],

        // Bella Napoli Pizzeria (Rest ID 2)
        [
            'restaurant_id' => 2,
            'category_name' => 'Artisan Pizzas',
            'name' => 'Margherita Di Bufala (12")',
            'description' => 'San Marzano tomato base, creamy buffalo mozzarella, fresh sweet basil leaves, and extra virgin olive oil.',
            'price' => 12.50,
            'original_price' => 14.00,
            'image_url' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=600&auto=format&fit=crop&q=80',
            'is_popular' => 1,
            'is_vegetarian' => 1,
            'is_spicy' => 0,
            'calories' => 690,
            'options' => [
                ['group_name' => 'Crust Style', 'option_name' => 'Traditional Neapolitan', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Crust Style', 'option_name' => 'Cheese Stuffed Crust (+ $2.50)', 'additional_price' => 2.50, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Extra Toppings', 'option_name' => 'Black Olives', 'additional_price' => 1.20, 'is_required' => 0, 'max_choices' => 3],
                ['group_name' => 'Extra Toppings', 'option_name' => 'Fresh Arugula & Sun-dried Tomato', 'additional_price' => 1.80, 'is_required' => 0, 'max_choices' => 3]
            ]
        ],
        [
            'restaurant_id' => 2,
            'category_name' => 'Artisan Pizzas',
            'name' => 'Diavola Spicy Pepperoni (12")',
            'description' => 'Italian spicy salami pepperoni, fresh mozzarella, spicy Calabrian chili flakes, and organic honey drizzle.',
            'price' => 14.99,
            'original_price' => null,
            'image_url' => 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=600&auto=format&fit=crop&q=80',
            'is_popular' => 1,
            'is_vegetarian' => 0,
            'is_spicy' => 1,
            'calories' => 840,
            'options' => [
                ['group_name' => 'Crust Style', 'option_name' => 'Thin & Crispy', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Crust Style', 'option_name' => 'Thick Wood-Fired Fluffy', 'additional_price' => 1.00, 'is_required' => 1, 'max_choices' => 1]
            ]
        ],
        [
            'restaurant_id' => 2,
            'category_name' => 'Pastas & Salads',
            'name' => 'Creamy Truffle Fettuccine Alfredo',
            'description' => 'Fresh egg pasta tossed in a rich parmesan and wild mushroom cream sauce with white truffle butter.',
            'price' => 13.50,
            'original_price' => 15.00,
            'image_url' => 'https://images.unsplash.com/photo-1645112411341-6c4fd023714a?w=600&auto=format&fit=crop&q=80',
            'is_popular' => 0,
            'is_vegetarian' => 1,
            'is_spicy' => 0,
            'calories' => 710,
            'options' => [
                ['group_name' => 'Protein Add-on', 'option_name' => 'Grilled Chicken Breast (+ $3.00)', 'additional_price' => 3.00, 'is_required' => 0, 'max_choices' => 1]
            ]
        ],

        // Tokyo Ramen & Dragon Roll (Rest ID 3)
        [
            'restaurant_id' => 3,
            'category_name' => 'Ramen & Bowls',
            'name' => 'Tokyo Signature Tonkotsu Ramen',
            'description' => '16-hour simmered pork bone broth, springy ramen noodles, tender chashu pork belly slices, marinated soft-boiled egg, nori seaweed, and bamboo shoots.',
            'price' => 13.99,
            'original_price' => 15.50,
            'image_url' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=600&auto=format&fit=crop&q=80',
            'is_popular' => 1,
            'is_vegetarian' => 0,
            'is_spicy' => 0,
            'calories' => 680,
            'options' => [
                ['group_name' => 'Noodle Firmness', 'option_name' => 'Normal Standard', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Noodle Firmness', 'option_name' => 'Firm / Al Dente', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Add-ons', 'option_name' => 'Extra Ajitsuke Tamago Egg', 'additional_price' => 1.50, 'is_required' => 0, 'max_choices' => 3],
                ['group_name' => 'Add-ons', 'option_name' => 'Extra 2x Chashu Slices', 'additional_price' => 3.00, 'is_required' => 0, 'max_choices' => 3]
            ]
        ],
        [
            'restaurant_id' => 3,
            'category_name' => 'Sushi & Rolls',
            'name' => 'Crispy Tempura Dragon Roll (8 pcs)',
            'description' => 'Prawn tempura and cucumber roll wrapped with ripe avocado slices, drizzled with sweet unagi glaze and spicy tobiko mayo.',
            'price' => 12.99,
            'original_price' => null,
            'image_url' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=600&auto=format&fit=crop&q=80',
            'is_popular' => 1,
            'is_vegetarian' => 0,
            'is_spicy' => 1,
            'calories' => 520,
            'options' => []
        ],

        // Green Garden Superbowls (Rest ID 4)
        [
            'restaurant_id' => 4,
            'category_name' => 'Superbowls',
            'name' => 'Avocado Quinoa Protein Harvest Bowl',
            'description' => 'Fresh Hass avocado, organic tricolor quinoa, roasted sweet potatoes, edamame, baby spinach, toasted almonds, and ginger-tahini dressing.',
            'price' => 10.99,
            'original_price' => 12.99,
            'image_url' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=600&auto=format&fit=crop&q=80',
            'is_popular' => 1,
            'is_vegetarian' => 1,
            'is_vegan' => 1,
            'is_gluten_free' => 1,
            'is_spicy' => 0,
            'calories' => 410,
            'options' => [
                ['group_name' => 'Extra Protein', 'option_name' => 'Organic Tofu Cubes', 'additional_price' => 2.00, 'is_required' => 0, 'max_choices' => 1],
                ['group_name' => 'Extra Protein', 'option_name' => 'Herb Grilled Chicken (+ $3.00)', 'additional_price' => 3.00, 'is_required' => 0, 'max_choices' => 1]
            ]
        ],
        [
            'restaurant_id' => 4,
            'category_name' => 'Cold-Pressed Juices',
            'name' => 'Green Glow Vitality Juice (400ml)',
            'description' => 'Cold-pressed green apple, organic kale, cucumber, celery, fresh ginger, and lemon.',
            'price' => 4.99,
            'original_price' => null,
            'image_url' => 'https://images.unsplash.com/photo-1613478223719-2ab802602423?w=600&auto=format&fit=crop&q=80',
            'is_popular' => 0,
            'is_vegetarian' => 1,
            'is_vegan' => 1,
            'is_gluten_free' => 1,
            'is_spicy' => 0,
            'calories' => 120,
            'options' => []
        ],

        // El Fuego Cantina (Rest ID 5)
        [
            'restaurant_id' => 5,
            'category_name' => 'Tacos & Burritos',
            'name' => 'Triple Birria Beef Tacos with Consommé',
            'description' => 'Slow-braised beef brisket wrapped in crisp griddled corn tortillas with melted Oaxaca cheese, cilantro, onions, and rich dipping broth.',
            'price' => 12.99,
            'original_price' => 14.50,
            'image_url' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=600&auto=format&fit=crop&q=80',
            'is_popular' => 1,
            'is_vegetarian' => 0,
            'is_spicy' => 1,
            'calories' => 780,
            'options' => [
                ['group_name' => 'Salsa Choice', 'option_name' => 'Mild Pico de Gallo', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Salsa Choice', 'option_name' => 'Fire Roasted Habanero Salsa 🔥', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Extras', 'option_name' => 'Guacamole & Crispy Tortilla Chips', 'additional_price' => 3.50, 'is_required' => 0, 'max_choices' => 1]
            ]
        ],

        // Sweet Tooth Patisserie (Rest ID 6)
        [
            'restaurant_id' => 6,
            'category_name' => 'Cakes & Pastries',
            'name' => 'Basque Burnt Cheesecake Slice',
            'description' => 'Ultra creamy caramelized cheesecake with a velvety rich center and organic strawberry compote.',
            'price' => 6.99,
            'original_price' => 8.00,
            'image_url' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=600&auto=format&fit=crop&q=80',
            'is_popular' => 1,
            'is_vegetarian' => 1,
            'is_spicy' => 0,
            'calories' => 420,
            'options' => []
        ],
        [
            'restaurant_id' => 6,
            'category_name' => 'Boba & Shakes',
            'name' => 'Brown Sugar Tiger Boba Milk Tea',
            'description' => 'Slow-cooked brown sugar tapioca pearls, fresh whole milk, and fragrant Taiwanese black tea blend.',
            'price' => 5.25,
            'original_price' => null,
            'image_url' => 'https://images.unsplash.com/photo-1558857563-b37cf595c2b0?w=600&auto=format&fit=crop&q=80',
            'is_popular' => 1,
            'is_vegetarian' => 1,
            'is_spicy' => 0,
            'calories' => 340,
            'options' => [
                ['group_name' => 'Sweetness Level', 'option_name' => '100% Regular Sweet', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Sweetness Level', 'option_name' => '50% Half Sweet', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Sweetness Level', 'option_name' => '0% Unsweetened', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Ice Level', 'option_name' => 'Regular Ice', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Ice Level', 'option_name' => 'Less Ice', 'additional_price' => 0.00, 'is_required' => 1, 'max_choices' => 1],
                ['group_name' => 'Extra Toppings', 'option_name' => 'Egg Custard Pudding', 'additional_price' => 0.75, 'is_required' => 0, 'max_choices' => 2],
                ['group_name' => 'Extra Toppings', 'option_name' => 'Cheese Foam Layer', 'additional_price' => 1.00, 'is_required' => 0, 'max_choices' => 2]
            ]
        ]
    ];

    $stmtItem = $db->prepare("INSERT INTO `menu_items` (`restaurant_id`, `category_name`, `name`, `description`, `price`, `original_price`, `image_url`, `is_popular`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `is_spicy`, `calories`) VALUES (:restaurant_id, :category_name, :name, :description, :price, :original_price, :image_url, :is_popular, :is_vegetarian, :is_vegan, :is_gluten_free, :is_spicy, :calories)");

    $stmtOpt = $db->prepare("INSERT INTO `menu_item_options` (`item_id`, `group_name`, `option_name`, `additional_price`, `is_required`, `max_choices`) VALUES (?, ?, ?, ?, ?, ?)");

    foreach ($items as $item) {
        $options = $item['options'] ?? [];
        unset($item['options']);
        $item['is_vegan'] = $item['is_vegan'] ?? 0;
        $item['is_gluten_free'] = $item['is_gluten_free'] ?? 0;

        $stmtItem->execute($item);
        $itemId = $db->lastInsertId();

        foreach ($options as $opt) {
            $stmtOpt->execute([
                $itemId,
                $opt['group_name'],
                $opt['option_name'],
                $opt['additional_price'] ?? 0,
                $opt['is_required'] ?? 0,
                $opt['max_choices'] ?? 1
            ]);
        }
    }

    // 5. Seed initial reviews
    $reviews = [
        [1, 'Alex Mercer', 5, 'Best burger in town! The truffle aioli is heavenly and arrived smoking hot in 20 minutes!'],
        [1, 'Sophia Chen', 5, 'Nashville spicy burger was super crispy and authentic. Fast delivery!'],
        [2, 'Marco Rossi', 5, 'Tastes just like home in Naples. True wood-fired crust!'],
        [3, 'Kenji Sato', 5, 'The Tonkotsu broth is so rich and flavorful. Well packed soup and noodles.']
    ];
    $stmtRev = $db->prepare("INSERT INTO `reviews` (`restaurant_id`, `customer_name`, `rating`, `comment`) VALUES (?, ?, ?, ?)");
    foreach ($reviews as $rev) {
        $stmtRev->execute($rev);
    }
}
