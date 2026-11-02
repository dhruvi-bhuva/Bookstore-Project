<?php
/**
 * Admin Dashboard Overview
 * Project: Online Bookstore
 */
$adminTitle = 'Dashboard';
$activeAdminPage = 'dashboard';
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/includes/admin-sidebar.php';

// Fetch Metrics
try {
    $totalSales = (float)$pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid' OR order_status = 'delivered'")->fetchColumn();
    $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $totalBooks = (int)$pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
    $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

    // Recent 5 Orders
    $recentOrders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5")->fetchAll();

    // Low Stock Alert Books (< 15 copies)
    $lowStockBooks = $pdo->query("SELECT * FROM books WHERE stock < 18 ORDER BY stock ASC LIMIT 5")->fetchAll();
} catch (Exception $e) {
    $totalSales = 0;
    $totalOrders = 0;
    $totalBooks = 0;
    $totalUsers = 0;
    $recentOrders = [];
    $lowStockBooks = [];
}
?>

<div class="admin-main">
    <!-- Top Bar -->
    <header class="admin-topbar">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0;">Dashboard Overview</h2>
            <span style="font-size: 0.8rem; color: #64748b;"><?= date('l, F j, Y'); ?></span>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="<?= BASE_URL; ?>/admin/book-add.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i> Add New Book
            </a>
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 600;">
                <i class="fa-solid fa-circle-user" style="font-size: 1.5rem; color: #4f46e5;"></i>
                <span><?= htmlspecialchars($adminUser['name']); ?></span>
            </div>
        </div>
    </header>

    <div class="admin-content">
        <!-- Flash Message -->
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']); ?>">
                <span><?= htmlspecialchars($flash['message']); ?></span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Stat Summary Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-indigo"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                <div class="stat-meta">
                    <h3><?= formatPrice($totalSales); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-emerald"><i class="fa-solid fa-cart-shopping"></i></div>
                <div class="stat-meta">
                    <h3><?= $totalOrders; ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-sky"><i class="fa-solid fa-book"></i></div>
                <div class="stat-meta">
                    <h3><?= $totalBooks; ?></h3>
                    <p>Books in Library</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-amber"><i class="fa-solid fa-users"></i></div>
                <div class="stat-meta">
                    <h3><?= $totalUsers; ?></h3>
                    <p>Registered Readers</p>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1.6fr 1.1fr; gap: 1.5rem;">
            <!-- Recent Orders Table -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Recent Orders</h2>
                    <a href="<?= BASE_URL; ?>/admin/orders.php" style="font-size: 0.85rem; color: #4f46e5; font-weight: 700;">View All &rsaquo;</a>
                </div>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentOrders)): ?>
                                <?php foreach ($recentOrders as $ro): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($ro['order_number']); ?></strong></td>
                                        <td><?= htmlspecialchars($ro['full_name']); ?></td>
                                        <td style="font-weight: 700;"><?= formatPrice($ro['total_amount']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $ro['order_status']; ?>"><?= ucfirst($ro['order_status']); ?></span>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL; ?>/admin/order-detail.php?id=<?= $ro['id']; ?>" class="btn btn-secondary btn-sm" style="padding: 2px 8px; font-size: 0.75rem;">
                                                Manage
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align: center; color: #64748b;">No orders placed yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Inventory Watchlist -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Stock Watchlist</h2>
                    <a href="<?= BASE_URL; ?>/admin/books.php" style="font-size: 0.85rem; color: #4f46e5; font-weight: 700;">Catalog &rsaquo;</a>
                </div>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($lowStockBooks)): ?>
                                <?php foreach ($lowStockBooks as $lb): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 600; line-height: 1.3; max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?= htmlspecialchars($lb['title']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-weight: 800; color: <?= $lb['stock'] < 10 ? '#ef4444' : '#f59e0b'; ?>;">
                                                <?= (int)$lb['stock']; ?> left
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL; ?>/admin/book-edit.php?id=<?= $lb['id']; ?>" class="btn btn-secondary btn-sm" style="padding: 2px 8px; font-size: 0.75rem;">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" style="text-align: center; color: #64748b;">All books well-stocked.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
