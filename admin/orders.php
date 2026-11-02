<?php
/**
 * Admin: Manage Customer Orders
 * Project: Online Bookstore
 */
$adminTitle = 'Manage Orders';
$activeAdminPage = 'orders';
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/includes/admin-sidebar.php';

// Handle Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = sanitize($_POST['order_status']);
    $allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    if (in_array($newStatus, $allowed)) {
        try {
            $upd = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
            $upd->execute([$newStatus, $orderId]);
            setFlash('success', 'Order status updated to ' . ucfirst($newStatus) . '.');
        } catch (Exception $e) {
            setFlash('danger', 'Error updating order: ' . $e->getMessage());
        }
    }
    header('Location: ' . BASE_URL . '/admin/orders.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
    exit;
}

// Filter by Status
$statusFilter = sanitize($_GET['status'] ?? '');
$sql = "SELECT o.*, COUNT(oi.id) as item_count 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id";
$params = [];

if (!empty($statusFilter)) {
    $sql .= " WHERE o.order_status = ?";
    $params[] = $statusFilter;
}
$sql .= " GROUP BY o.id ORDER BY o.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<div class="admin-main">
    <header class="admin-topbar">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0;">Customer Orders</h2>
            <span style="font-size: 0.8rem; color: #64748b;">Manage fulfillment, dispatch, and order lifecycle</span>
        </div>
    </header>

    <div class="admin-content">
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']); ?>">
                <span><?= htmlspecialchars($flash['message']); ?></span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Status Filter Tabs -->
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
            <a href="<?= BASE_URL; ?>/admin/orders.php" class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-secondary'; ?>">All Orders</a>
            <a href="<?= BASE_URL; ?>/admin/orders.php?status=pending" class="btn btn-sm <?= $statusFilter === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">Pending</a>
            <a href="<?= BASE_URL; ?>/admin/orders.php?status=processing" class="btn btn-sm <?= $statusFilter === 'processing' ? 'btn-primary' : 'btn-secondary'; ?>">Processing</a>
            <a href="<?= BASE_URL; ?>/admin/orders.php?status=shipped" class="btn btn-sm <?= $statusFilter === 'shipped' ? 'btn-primary' : 'btn-secondary'; ?>">Shipped</a>
            <a href="<?= BASE_URL; ?>/admin/orders.php?status=delivered" class="btn btn-sm <?= $statusFilter === 'delivered' ? 'btn-primary' : 'btn-secondary'; ?>">Delivered</a>
            <a href="<?= BASE_URL; ?>/admin/orders.php?status=cancelled" class="btn btn-sm <?= $statusFilter === 'cancelled' ? 'btn-primary' : 'btn-secondary'; ?>">Cancelled</a>
        </div>

        <div class="admin-card">
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order Details</th>
                            <th>Customer Info</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Fulfillment Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td>
                                        <strong style="color: #4f46e5; display: block;"><?= htmlspecialchars($o['order_number']); ?></strong>
                                        <span style="font-size: 0.75rem; color: #64748b;"><?= date('d M Y, h:i A', strtotime($o['created_at'])); ?></span>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($o['full_name']); ?></strong>
                                        <div style="font-size: 0.775rem; color: #64748b;"><?= htmlspecialchars($o['email']); ?></div>
                                        <div style="font-size: 0.775rem; color: #64748b;"><?= htmlspecialchars($o['phone']); ?></div>
                                    </td>
                                    <td><?= (int)$o['item_count']; ?> Book(s)</td>
                                    <td><strong><?= formatPrice($o['total_amount']); ?></strong></td>
                                    <td>
                                        <span style="font-size: 0.8rem; text-transform: uppercase; font-weight: 600;">
                                            <?= htmlspecialchars($o['payment_method']); ?>
                                        </span>
                                        <div style="font-size: 0.75rem; color: <?= $o['payment_status'] === 'paid' ? '#059669' : '#b45309'; ?>;">
                                            (<?= ucfirst($o['payment_status']); ?>)
                                        </div>
                                    </td>
                                    <td>
                                        <!-- Inline Status Updater -->
                                        <form action="<?= BASE_URL; ?>/admin/orders.php<?= !empty($statusFilter) ? '?status=' . urlencode($statusFilter) : ''; ?>" method="POST" style="margin: 0; display: flex; align-items: center; gap: 0.35rem;">
                                            <input type="hidden" name="update_status" value="1">
                                            <input type="hidden" name="order_id" value="<?= $o['id']; ?>">
                                            <select name="order_status" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; border: 1px solid #cbd5e1; background: #fff;">
                                                <option value="pending" <?= $o['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?= $o['order_status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="shipped" <?= $o['order_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?= $o['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?= $o['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="<?= BASE_URL; ?>/admin/order-detail.php?id=<?= $o['id']; ?>" class="btn btn-secondary btn-sm" title="View Full Order Invoice">
                                            <i class="fa-solid fa-file-invoice"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align: center; color: #64748b; padding: 3rem 1rem;">No orders found in this category.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
