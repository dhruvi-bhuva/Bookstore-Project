<?php
/**
 * Global Header Template
 * Project: Online Bookstore
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

$cartCount = getCartCount();
$wishlistCount = getWishlistCount($pdo);
$currentUser = currentUser();
$flash = getFlash();

$pageTitle = isset($pageTitle) ? $pageTitle . ' - BookVerse' : 'BookVerse - Online Bookstore';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    
    <!-- Google Fonts & Font Awesome Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Core Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>
<body>

    <!-- Top Announcement Bar -->
    <div class="top-bar">
        <div class="container top-bar-content">
            <div>
                <span class="top-bar-badge">COLLEGE MINI PROJECT</span>
                <span>📚 Over 10,000+ Titles | Fast Shipping & Best Deals</span>
            </div>
            <div class="top-bar-links">
                <?php if (isAdmin()): ?>
                    <a href="<?= BASE_URL; ?>/admin/index.php" style="color: #fbbf24; font-weight: 700;">
                        <i class="fa-solid fa-gauge-high"></i> Admin Portal
                    </a>
                <?php endif; ?>
                <a href="<?= BASE_URL; ?>/contact.php"><i class="fa-regular fa-circle-question"></i> Help & FAQs</a>
                <span><i class="fa-solid fa-phone"></i> +91 98765 43210</span>
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="site-header">
        <div class="container navbar">
            <!-- Brand Logo -->
            <a href="<?= BASE_URL; ?>/index.php" class="brand-logo">
                <img src="<?= BASE_URL; ?>/assets/images/logo.svg" alt="BookVerse Logo" onerror="this.outerHTML='<div class=\'brand-icon\'><i class=\'fa-solid fa-book-bookmark\'></i></div><span>Book<strong>Verse</strong></span>'">
            </a>

            <!-- Search Bar -->
            <div class="header-search">
                <form action="<?= BASE_URL; ?>/books.php" method="GET">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="search" placeholder="Search by book title, author, or ISBN..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </form>
            </div>

            <!-- Navigation Links -->
            <ul class="nav-links">
                <li><a href="<?= BASE_URL; ?>/index.php" class="<?= $activePage === 'home' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="<?= BASE_URL; ?>/books.php" class="<?= $activePage === 'books' ? 'active' : ''; ?>">All Books</a></li>
                <li><a href="<?= BASE_URL; ?>/about.php" class="<?= $activePage === 'about' ? 'active' : ''; ?>">About</a></li>
                <li><a href="<?= BASE_URL; ?>/contact.php" class="<?= $activePage === 'contact' ? 'active' : ''; ?>">Contact</a></li>
            </ul>

            <!-- Header Action Buttons -->
            <div class="header-actions">
                <!-- Wishlist Icon -->
                <a href="<?= BASE_URL; ?>/wishlist.php" class="icon-btn" title="My Wishlist">
                    <i class="fa-regular fa-heart"></i>
                    <?php if ($wishlistCount > 0): ?>
                        <span class="badge-count"><?= $wishlistCount; ?></span>
                    <?php endif; ?>
                </a>

                <!-- Cart Icon -->
                <a href="<?= BASE_URL; ?>/cart.php" class="icon-btn" title="Shopping Cart">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="badge-count"><?= $cartCount; ?></span>
                    <?php endif; ?>
                </a>

                <!-- User Account Dropdown -->
                <?php if (isLoggedIn()): ?>
                    <div class="user-menu-wrapper">
                        <button type="button" class="user-menu-btn">
                            <i class="fa-solid fa-circle-user avatar-icon"></i>
                            <span><?= htmlspecialchars(explode(' ', $currentUser['name'])[0]); ?></span>
                            <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem; color: #94a3b8;"></i>
                        </button>
                        <div class="user-dropdown">
                            <div style="padding: 0.6rem 1.25rem; font-size: 0.8rem; color: #64748b; border-bottom: 1px solid #f1f5f9;">
                                Signed in as<br><strong style="color: #0f172a;"><?= htmlspecialchars($currentUser['email']); ?></strong>
                            </div>
                            <?php if (isAdmin()): ?>
                                <a href="<?= BASE_URL; ?>/admin/index.php" style="color: #4f46e5; font-weight: 700;">
                                    <i class="fa-solid fa-gauge-high"></i> Admin Dashboard
                                </a>
                                <div class="divider"></div>
                            <?php endif; ?>
                            <a href="<?= BASE_URL; ?>/orders.php"><i class="fa-solid fa-box-archive"></i> My Orders</a>
                            <a href="<?= BASE_URL; ?>/wishlist.php"><i class="fa-solid fa-heart"></i> My Wishlist</a>
                            <a href="<?= BASE_URL; ?>/profile.php"><i class="fa-solid fa-user-gear"></i> Account Settings</a>
                            <div class="divider"></div>
                            <a href="<?= BASE_URL; ?>/logout.php" style="color: #ef4444;"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL; ?>/login.php" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In
                    </a>
                <?php endif; ?>

                <!-- Mobile Hamburger Toggle -->
                <button type="button" class="mobile-nav-toggle" aria-label="Toggle navigation">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Global Flash Notification Banner -->
    <?php if ($flash): ?>
        <div class="container flash-container">
            <div class="alert alert-<?= htmlspecialchars($flash['type']); ?>">
                <div>
                    <?php if ($flash['type'] === 'success'): ?>
                        <i class="fa-solid fa-circle-check" style="margin-right: 8px;"></i>
                    <?php elseif ($flash['type'] === 'danger'): ?>
                        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px;"></i>
                    <?php elseif ($flash['type'] === 'warning'): ?>
                        <i class="fa-solid fa-circle-exclamation" style="margin-right: 8px;"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-circle-info" style="margin-right: 8px;"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($flash['message']); ?>
                </div>
                <button type="button" class="alert-close">&times;</button>
            </div>
        </div>
    <?php endif; ?>
