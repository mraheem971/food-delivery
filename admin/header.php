<?php
require_once __DIR__ . '/../data/config.php';
$currentAdminPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner & Kitchen Admin - <?= htmlspecialchars(APP_NAME) ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Main Style -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Admin Specific Styles */
        .admin-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
            background: #f8fafc;
        }

        @media (max-width: 900px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }
            .admin-sidebar {
                display: none;
            }
        }

        .admin-sidebar {
            background: #111827;
            color: #f3f4f6;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #1f2937;
        }

        .admin-sidebar-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 10px 24px;
            border-bottom: 1px solid #1f2937;
            margin-bottom: 20px;
        }

        .admin-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            color: #9ca3af;
            font-weight: 600;
            font-size: 0.92rem;
            margin-bottom: 6px;
            transition: var(--transition);
        }

        .admin-nav-item:hover,
        .admin-nav-item.active {
            background: #1f2937;
            color: #ff2b85;
            font-weight: 700;
        }

        .admin-main {
            padding: 28px 36px;
            overflow-y: auto;
        }

        .admin-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 12px;
        }

        .admin-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .admin-stat-card {
            background: white;
            padding: 22px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .admin-stat-num {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--secondary);
            margin-top: 4px;
        }

        .admin-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .data-table-card {
            background: white;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-bottom: 32px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }

        .data-table th {
            background: #f8fafc;
            padding: 14px 18px;
            font-weight: 800;
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border);
        }

        .data-table td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            color: var(--text-main);
            vertical-align: middle;
        }

        .data-table tr:hover {
            background: #fdf2f8;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: var(--radius-full);
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .status-placed { background: #e0f2fe; color: #0369a1; }
        .status-confirmed { background: #fef3c7; color: #92400e; }
        .status-preparing { background: #fef08a; color: #854d0e; }
        .status-on_way { background: #ede9fe; color: #6b21a8; }
        .status-delivered { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        .btn-action-pill {
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
        }
    </style>
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <span class="logo-icon" style="width: 36px; height: 36px; font-size: 1.1rem;">🐼</span>
            <div>
                <div style="font-weight: 800; font-size: 1.1rem; color: white;">FoodHub <span style="color: #ff2b85;">Admin</span></div>
                <div style="font-size: 0.75rem; color: #9ca3af;">Store & Kitchen Portal</div>
            </div>
        </div>

        <nav style="flex: 1;">
            <a href="index.php" class="admin-nav-item <?= $currentAdminPage === 'index' ? 'active' : '' ?>">
                <span>📊</span>
                <span>Dashboard Overview</span>
            </a>
            <a href="orders.php" class="admin-nav-item <?= $currentAdminPage === 'orders' ? 'active' : '' ?>">
                <span>🛍️</span>
                <span>Customer Orders</span>
            </a>
            <a href="menu.php" class="admin-nav-item <?= $currentAdminPage === 'menu' ? 'active' : '' ?>">
                <span>🍔</span>
                <span>Fresh Dishes & Menu</span>
            </a>
            <a href="coupons.php" class="admin-nav-item <?= $currentAdminPage === 'coupons' ? 'active' : '' ?>">
                <span>🏷️</span>
                <span>Discounts & Vouchers</span>
            </a>
            <a href="whatsapp.php" class="admin-nav-item <?= $currentAdminPage === 'whatsapp' ? 'active' : '' ?>">
                <span>💬</span>
                <span>WhatsApp Bot Hub</span>
            </a>
        </nav>

        <div style="padding-top: 16px; border-top: 1px solid #1f2937;">
            <a href="../index.php" target="_blank" class="admin-nav-item" style="color: #10b981; background: rgba(16, 185, 129, 0.1);">
                <span>🌐</span>
                <span>View Customer App ➔</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Panel -->
    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: uppercase;">Owner Operations Hub</div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--secondary);">
                    <?= $currentAdminPage === 'orders' ? 'Customer Orders Management' : ($currentAdminPage === 'menu' ? 'Manage Fresh Dishes & Menu' : ($currentAdminPage === 'coupons' ? 'Promo Vouchers & Campaigns' : 'Admin Control Center')) ?>
                </h1>
            </div>

            <div style="display: flex; align-items: center; gap: 12px;">
                <button type="button" class="checkout-btn" onclick="openAddDishModal()" style="width: auto; padding: 10px 20px; font-size: 0.88rem; margin-top: 0;">
                    <span>➕ Add Fresh Dish</span>
                </button>
                <a href="../index.php" class="location-picker-btn" target="_blank">
                    <span>📱 Live Customer View</span>
                </a>
            </div>
        </div>
