<?php
/**
 * Order Success & Receipt Page
 * Project: Online Bookstore
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

requireLogin();

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = $_SESSION['user_id'];

// Fetch order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND (user_id = ? OR ? = 'admin')");
$stmt->execute([$orderId, $userId, $_SESSION['user_role'] ?? '']);
$order = $stmt->fetch();

if (!$order) {
    setFlash('danger', 'Order not found.');
    header('Location: ' . BASE_URL . '/orders.php');
    exit;
}

// Fetch order items
$itemStmt = $pdo->prepare("SELECT oi.*, b.image FROM order_items oi LEFT JOIN books b ON oi.book_id = b.id WHERE oi.order_id = ?");
$itemStmt->execute([$orderId]);
$orderItems = $itemStmt->fetchAll();

$pageTitle = 'Order Confirmed #' . $order['order_number'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container section" style="max-width: 800px;">
    <!-- Confirmation Banner -->
    <div style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 2.5rem; text-align: center; box-shadow: var(--shadow-sm); margin-bottom: 2rem;">
        <div style="width: 72px; height: 72px; border-radius: 50%; background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 2.25rem; margin: 0 auto 1.25rem;">
            <i class="fa-solid fa-check"></i>
        </div>
        <h1 style="font-size: 1.85rem; font-weight: 800; margin-bottom: 0.5rem;">Thank You for Your Order!</h1>
        <p style="color: #64748b; font-size: 1rem;">
            Your order <strong><?= htmlspecialchars($order['order_number']); ?></strong> has been placed and is currently being processed.
        </p>
        
        <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap;">
            <button onclick="window.print()" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-print"></i> Print Receipt
            </button>
            <a href="<?= BASE_URL; ?>/orders.php" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-box-archive"></i> View Order History
            </a>
            <a href="<?= BASE_URL; ?>/books.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-book"></i> Keep Browsing
            </a>
        </div>
    </div>

    <!-- Order Summary Card -->
    <div style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 2rem; box-shadow: var(--shadow-sm);">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <h4 style="font-size: 0.85rem; color: #64748b; text-transform: uppercase;">Order Information</h4>
                <p style="margin: 0.25rem 0;"><strong>Date:</strong> <?= date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
                <p style="margin: 0.25rem 0;"><strong>Payment Method:</strong> <?= strtoupper($order['payment_method']); ?></p>
                <p style="margin: 0.25rem 0;">
                    <strong>Status:</strong> 
                    <span class="status-badge status-<?= $order['order_status']; ?>"><?= ucfirst($order['order_status']); ?></span>
                </p>
            </div>
            <div>
                <h4 style="font-size: 0.85rem; color: #64748b; text-transform: uppercase;">Delivery Address</h4>
                <p style="margin: 0.25rem 0; font-weight: 700;"><?= htmlspecialchars($order['full_name']); ?></p>
                <p style="margin: 0.25rem 0; color: #475569;"><?= htmlspecialchars($order['address']); ?>, <?= htmlspecialchars($order['city']); ?>, <?= htmlspecialchars($order['state']); ?> - <?= htmlspecialchars($order['pincode']); ?></p>
                <p style="margin: 0.25rem 0; color: #475569;">Phone: <?= htmlspecialchars($order['phone']); ?></p>
            </div>
        </div>

        <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1rem;">Items Ordered</h3>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td>
                            <div class="cart-book-cell">
                                <div class="cart-thumb" style="width: 45px; height: 60px;">
                                    <img src="<?= getBookImage($item['image']); ?>" alt="" onerror="this.src='<?= BASE_URL; ?>/assets/images/books/default-book.jpg'">
                                </div>
                                <span style="font-weight: 600;"><?= htmlspecialchars($item['title']); ?></span>
                            </div>
                        </td>
                        <td><?= formatPrice($item['price']); ?></td>
                        <td><?= (int)$item['quantity']; ?></td>
                        <td style="text-align: right; font-weight: 700;"><?= formatPrice($item['total_price']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="max-width: 300px; margin-left: auto; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
            <div class="summary-row">
                <span>Subtotal:</span>
                <strong><?= formatPrice($order['subtotal']); ?></strong>
            </div>
            <div class="summary-row">
                <span>Shipping:</span>
                <strong><?= $order['shipping_fee'] == 0 ? 'FREE' : formatPrice($order['shipping_fee']); ?></strong>
            </div>
            <div class="summary-row total">
                <span>Total Paid:</span>
                <span><?= formatPrice($order['total_amount']); ?></span>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
