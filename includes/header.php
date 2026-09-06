<?php
require_once __DIR__ . '/../data/config.php';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?> - Delicious Food Delivered Fast</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="navbar">
    <div class="container navbar-container">
        <!-- Logo -->
        <a href="index.php" class="brand-logo">
            <span class="logo-icon">🐼</span>
            <span>Food<span style="color: #1e1e2d;">Hub</span></span>
        </a>

        <!-- Location Picker Button -->
        <button type="button" class="location-picker-btn" id="locationPickerBtn">
            <span class="loc-icon">📍</span>
            <span class="loc-text" id="currentLocationText">Downtown District</span>
            <span style="font-size: 0.75rem; color: #9ca3af;">▼</span>
        </button>

        <!-- Search Bar -->
        <div class="nav-search">
            <span class="nav-search-icon">🔍</span>
            <input type="text" class="nav-search-input" placeholder="Search restaurants, burgers, pizza, sushi..." autocomplete="off">
            <div class="search-results-dropdown" id="searchResultsDropdown"></div>
        </div>

        <!-- Navigation Actions -->
        <div class="nav-actions">
            <a href="menu.php" class="nav-link-btn <?= $currentPage === 'menu' ? 'active' : '' ?>">
                <span>🍽️</span>
                <span>Menu</span>
            </a>
            <a href="deals.php" class="nav-link-btn deals-btn <?= $currentPage === 'deals' ? 'active' : '' ?>">
                <span>🏷️</span>
                <span>Deals</span>
            </a>
            <a href="help.php" class="nav-link-btn <?= $currentPage === 'help' ? 'active' : '' ?>">
                <span>💡</span>
                <span>Help</span>
            </a>
            <a href="admin/index.php" class="nav-link-btn" style="background: #1e1e2d; color: white; padding: 8px 14px; font-size: 0.85rem;" title="Access Owner & Kitchen Management">
                <span>👑</span>
                <span>Admin</span>
            </a>
            <div id="userNavAuthContainer"></div>
            <button type="button" class="cart-trigger-btn" id="openCartDrawerBtn">
                <span>🛍️</span>
                <span>Cart</span>
                <span class="cart-badge" style="display: none;">0</span>
            </button>
        </div>
    </div>
</header>
