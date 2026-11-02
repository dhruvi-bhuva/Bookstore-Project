<?php
/**
 * Admin: Order Detail & Invoice
 * Project: Online Bookstore
 */
$adminTitle = 'Order Details';
$activeAdminPage = 'orders';
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/includes/admin-sidebar.php';

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('danger', 'Order not found.');
    header('Location: ' . BASE_URL . '/admin/orders.php');
    exit;
}

// Fetch Order Items
$itemStmt = $pdo->prepare("SELECT oi.*, b.image FROM order_items oi LEFT JOIN books b ON oi.book_id = b.id WHERE oi.order_id = ?");
$itemStmt->execute([$orderId]);
$orderItems = $itemStmt->fetchAll();

// Handle Status Change from this page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = sanitize($_POST['order_status']);
    $newPay = sanitize($_POST['payment_status']);
    try {
        $upd = $pdo->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?");
        $upd->execute([$newStatus, $newPay, $orderId]);
        setFlash('success', 'Order status and payment details updated.');
        header('Location: ' . BASE_URL . '/admin/order-detail.php?id=' . $orderId);
        exit;
    } catch (Exception $e) {
        setFlash('danger', 'Error updating order: ' . $e->getMessage());
    }
}
?>

<div class="admin-main">
    <header class="admin-topbar">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0;">Order #<?= htmlspecialchars($order['order_number']); ?></h2>
            <span style="font-size: 0.8rem; color: #64748b;">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])); ?></span>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <button onclick="window.print()" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-print"></i> Print Invoice
            </button>
            <a href="<?= BASE_URL; ?>/admin/orders.php" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Back to Orders
            </a>
        </div>
    </header>

    <div class="admin-content" style="max-width: 900px;">
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']); ?>">
                <span><?= htmlspecialchars($flash['message']); ?></span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Status Management Card -->
        <div class="admin-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
            <form action="<?= BASE_URL; ?>/admin/order-detail.php?id=<?= $orderId; ?>" method="POST" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <input type="hidden" name="update_status" value="1">
                
                <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <label class="form-label" style="margin: 0; white-space: nowrap;">Order Status:</label>
                        <select name="order_status" class="form-control" style="width: auto; padding: 0.4rem 0.8rem;">
                            <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?= $order['order_status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <label class="form-label" style="margin: 0; white-space: nowrap;">Payment Status:</label>
                        <select name="payment_status" class="form-control" style="width: auto; padding: 0.4rem 0.8rem;">
                            <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-floppy-disk"></i> Update Status
                </button>
            </form>
        </div>

        <!-- Invoice Body -->
        <div class="admin-card" style="padding: 2.5rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <h4 style="font-size: 0.85rem; color: #64748b; text-transform: uppercase;">Customer Information</h4>
                    <p style="margin: 0.25rem 0; font-size: 1.05rem; font-weight: 700;"><?= htmlspecialchars($order['full_name']); ?></p>
                    <p style="margin: 0.25rem 0; color: #475569;">Email: <?= htmlspecialchars($order['email']); ?></p>
                    <p style="margin: 0.25rem 0; color: #475569;">Phone: <?= htmlspecialchars($order['phone']); ?></p>
                </div>
                <div>
                    <h4 style="font-size: 0.85rem; color: #64748b; text-transform: uppercase;">Shipping Address</h4>
                    <p style="margin: 0.25rem 0; color: #0f172a; font-weight: 600;"><?= htmlspecialchars($order['address']); ?></p>
                    <p style="margin: 0.25rem 0; color: #475569;"><?= htmlspecialchars($order['city']); ?>, <?= htmlspecialchars($order['state']); ?> - <?= htmlspecialchars($order['pincode']); ?></p>
                    <?php if (!empty($order['notes'])): ?>
                        <p style="margin: 0.5rem 0 0; font-size: 0.85rem; color: #b45309; background: #fffbeb; padding: 4px 8px; border-radius: 4px;">
                            <strong>Instructions:</strong> <?= htmlspecialchars($order['notes']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1rem;">Order Items</h3>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                <div class="cart-book-cell">
                                    <img src="<?= getBookImage($item['image']); ?>" alt="" class="admin-thumb" onerror="this.src='<?= BASE_URL; ?>/assets/images/books/default-book.jpg'">
                                    <strong><?= htmlspecialchars($item['title']); ?></strong>
                                </div>
                            </td>
                            <td><?= formatPrice($item['price']); ?></td>
                            <td><?= (int)$item['quantity']; ?></td>
                            <td style="text-align: right; font-weight: 700;"><?= formatPrice($item['total_price']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="max-width: 320px; margin-left: auto; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <strong><?= formatPrice($order['subtotal']); ?></strong>
                </div>
                <div class="summary-row">
                    <span>Shipping Fee:</span>
                    <strong><?= $order['shipping_fee'] == 0 ? 'FREE' : formatPrice($order['shipping_fee']); ?></strong>
                </div>
                <div class="summary-row total">
                    <span>Total Amount:</span>
                    <span><?= formatPrice($order['total_amount']); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
