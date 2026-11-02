<?php
/**
 * Customer Order History
 * Project: Online Bookstore
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];

// Fetch all orders for this user
$stmt = $pdo->prepare("SELECT o.*, COUNT(oi.id) as total_items 
                      FROM orders o 
                      LEFT JOIN order_items oi ON o.id = oi.order_id 
                      WHERE o.user_id = ? 
                      GROUP BY o.id 
                      ORDER BY o.id DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

$pageTitle = 'My Order History';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container section">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 800;">My Orders</h1>
        <p style="color: #64748b;">Track and view past book purchases and shipping updates</p>
    </div>

    <?php if (!empty($orders)): ?>
        <div style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: var(--shadow-sm);">
            <div style="overflow-x: auto;">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Total Items</th>
                            <th>Total Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>
                                    <strong style="color: #4f46e5;"><?= htmlspecialchars($o['order_number']); ?></strong>
                                </td>
                                <td style="color: #64748b; font-size: 0.85rem;">
                                    <?= date('d M Y', strtotime($o['created_at'])); ?>
                                </td>
                                <td>
                                    <?= (int)$o['total_items']; ?> <?= (int)$o['total_items'] === 1 ? 'Book' : 'Books'; ?>
                                </td>
                                <td style="font-weight: 800; color: #0f172a;">
                                    <?= formatPrice($o['total_amount']); ?>
                                </td>
                                <td>
                                    <span style="font-size: 0.8rem; text-transform: uppercase; font-weight: 600; color: #475569;">
                                        <?= htmlspecialchars($o['payment_method']); ?> (<?= $o['payment_status']; ?>)
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $o['order_status']; ?>">
                                        <?= ucfirst($o['order_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL; ?>/order-success.php?id=<?= $o['id']; ?>" class="btn btn-secondary btn-sm">
                                        <i class="fa-solid fa-receipt"></i> Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div style="background: #ffffff; border-radius: 16px; padding: 5rem 2rem; text-align: center; border: 1px solid #e2e8f0; max-width: 600px; margin: 0 auto;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 2.25rem; margin: 0 auto 1.5rem;">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem;">No Orders Found</h2>
            <p style="color: #64748b; margin-bottom: 2rem;">You haven't placed any orders yet. Browse our library and pick your favorite books!</p>
            <a href="<?= BASE_URL; ?>/books.php" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-cart-shopping"></i> Shop Now
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
