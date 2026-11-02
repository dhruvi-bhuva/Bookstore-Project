<?php
/**
 * Admin Sidebar Navigation
 */
// Count pending orders and unread messages for badges
try {
    $pendingOrdersCount = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn();
    $unreadMsgCount = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();
} catch (Exception $e) {
    $pendingOrdersCount = 0;
    $unreadMsgCount = 0;
}
?>
<!-- Sidebar Navigation -->
<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon" style="width: 34px; height: 34px; font-size: 1rem;"><i class="fa-solid fa-book-bookmark"></i></div>
        <div>
            <h3>BookVerse</h3>
            <span>ADMIN PANEL</span>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="<?= BASE_URL; ?>/admin/index.php" class="<?= $activeAdminPage === 'dashboard' ? 'active' : ''; ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL; ?>/admin/books.php" class="<?= $activeAdminPage === 'books' ? 'active' : ''; ?>">
                <i class="fa-solid fa-book"></i>
                <span>Manage Books</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL; ?>/admin/categories.php" class="<?= $activeAdminPage === 'categories' ? 'active' : ''; ?>">
                <i class="fa-solid fa-layer-group"></i>
                <span>Categories</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL; ?>/admin/orders.php" class="<?= $activeAdminPage === 'orders' ? 'active' : ''; ?>">
                <i class="fa-solid fa-cart-flatbed"></i>
                <span>Orders</span>
                <?php if ($pendingOrdersCount > 0): ?>
                    <span style="background: #ef4444; color: #fff; font-size: 0.7rem; padding: 2px 6px; border-radius: 10px; margin-left: auto;"><?= $pendingOrdersCount; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL; ?>/admin/messages.php" class="<?= $activeAdminPage === 'messages' ? 'active' : ''; ?>">
                <i class="fa-solid fa-envelope"></i>
                <span>Inquiries</span>
                <?php if ($unreadMsgCount > 0): ?>
                    <span style="background: #3b82f6; color: #fff; font-size: 0.7rem; padding: 2px 6px; border-radius: 10px; margin-left: auto;"><?= $unreadMsgCount; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL; ?>/admin/users.php" class="<?= $activeAdminPage === 'users' ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i>
                <span>Customers</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="<?= BASE_URL; ?>/index.php" style="display: flex; align-items: center; gap: 0.75rem; color: #cbd5e1; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.75rem;">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Visit Storefront
        </a>
        <a href="<?= BASE_URL; ?>/logout.php" style="display: flex; align-items: center; gap: 0.75rem; color: #f87171; font-size: 0.85rem; font-weight: 600;">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
        </a>
    </div>
</aside>
