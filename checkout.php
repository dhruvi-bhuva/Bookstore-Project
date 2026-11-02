<?php
/**
 * Checkout & Order Placement Page
 * Project: Online Bookstore
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

// Require user to be logged in to checkout
if (!isLoggedIn()) {
    setFlash('warning', 'Please sign in to complete your checkout.');
    header('Location: ' . BASE_URL . '/login.php?redirect=checkout.php');
    exit;
}

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    setFlash('warning', 'Your shopping cart is currently empty.');
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

$userId = $_SESSION['user_id'];
$cartItems = $_SESSION['cart'];
$subtotal = getCartSubtotal();
$shippingFee = ($subtotal >= 500) ? 0.00 : 40.00;
$totalAmount = $subtotal + $shippingFee;

// Fetch current user details
$uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$uStmt->execute([$userId]);
$user = $uStmt->fetch();

$errors = [];

// Handle Order Placement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $pincode = sanitize($_POST['pincode'] ?? '');
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'cod');
    $notes = sanitize($_POST['notes'] ?? '');

    // Validation
    if (empty($fullName)) $errors[] = 'Full name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (empty($phone)) $errors[] = 'Contact phone number is required.';
    if (empty($address)) $errors[] = 'Delivery address is required.';
    if (empty($city)) $errors[] = 'City is required.';
    if (empty($state)) $errors[] = 'State is required.';
    if (empty($pincode)) $errors[] = 'Pincode / Postal code is required.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $orderNumber = generateOrderNumber();
            $paymentStatus = ($paymentMethod === 'cod') ? 'pending' : 'paid';

            // Insert into orders table
            $insOrder = $pdo->prepare("INSERT INTO orders 
                (user_id, order_number, full_name, email, phone, address, city, state, pincode, payment_method, payment_status, subtotal, shipping_fee, total_amount, order_status, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
            $insOrder->execute([
                $userId, $orderNumber, $fullName, $email, $phone, $address, $city, $state, $pincode, $paymentMethod, $paymentStatus, $subtotal, $shippingFee, $totalAmount, $notes
            ]);
            $orderId = $pdo->lastInsertId();

            // Insert each cart item into order_items and update stock
            $insItem = $pdo->prepare("INSERT INTO order_items (order_id, book_id, title, price, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?)");
            $updStock = $pdo->prepare("UPDATE books SET stock = GREATEST(0, stock - ?) WHERE id = ?");

            foreach ($cartItems as $bookId => $item) {
                $itemPrice = !empty($item['discount_price']) ? $item['discount_price'] : $item['price'];
                $itemTotal = $itemPrice * $item['quantity'];

                $insItem->execute([
                    $orderId, $bookId, $item['title'], $itemPrice, $item['quantity'], $itemTotal
                ]);

                $updStock->execute([$item['quantity'], $bookId]);
            }

            // Also update user profile with latest address info if empty
            $updUser = $pdo->prepare("UPDATE users SET phone = COALESCE(NULLIF(phone, ''), ?), address = COALESCE(NULLIF(address, ''), ?), city = COALESCE(NULLIF(city, ''), ?), state = COALESCE(NULLIF(state, ''), ?), pincode = COALESCE(NULLIF(pincode, ''), ?) WHERE id = ?");
            $updUser->execute([$phone, $address, $city, $state, $pincode, $userId]);

            // Clear Cart
            unset($_SESSION['cart']);

            $pdo->commit();

            setFlash('success', 'Congratulations! Your order has been placed successfully.');
            header('Location: ' . BASE_URL . '/order-success.php?id=' . $orderId);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to place order: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Secure Checkout';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container section">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 800;">Checkout</h1>
        <p style="color: #64748b;">Enter your delivery destination and select your payment preference</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL; ?>/checkout.php" method="POST">
        <div class="cart-layout">
            <!-- Left: Shipping & Payment Form -->
            <div class="cart-card">
                <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-truck-ramp-box text-primary"></i> 1. Shipping Details
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" required class="form-control" value="<?= htmlspecialchars($_POST['full_name'] ?? $user['name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" required class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? $user['email'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number *</label>
                    <input type="tel" name="phone" required class="form-control" placeholder="10-digit mobile number" value="<?= htmlspecialchars($_POST['phone'] ?? $user['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Street / House Address *</label>
                    <textarea name="address" rows="2" required class="form-control" placeholder="House no, Street name, Landmark"><?= htmlspecialchars($_POST['address'] ?? $user['address'] ?? ''); ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">City *</label>
                        <input type="text" name="city" required class="form-control" value="<?= htmlspecialchars($_POST['city'] ?? $user['city'] ?? 'Bangalore'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">State *</label>
                        <input type="text" name="state" required class="form-control" value="<?= htmlspecialchars($_POST['state'] ?? $user['state'] ?? 'Karnataka'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pincode *</label>
                        <input type="text" name="pincode" required class="form-control" value="<?= htmlspecialchars($_POST['pincode'] ?? $user['pincode'] ?? '560001'); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Delivery Instructions (Optional)</label>
                    <input type="text" name="notes" class="form-control" placeholder="e.g. Leave package with security gate" value="<?= htmlspecialchars($_POST['notes'] ?? ''); ?>">
                </div>

                <!-- Payment Selection -->
                <h3 style="font-size: 1.25rem; font-weight: 800; margin: 2rem 0 1.25rem; display: flex; align-items: center; gap: 0.5rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
                    <i class="fa-solid fa-credit-card text-primary"></i> 2. Select Payment Method
                </h3>

                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                    <label style="display: flex; align-items: center; gap: 0.85rem; padding: 1rem; border: 1.5px solid #e2e8f0; border-radius: 10px; cursor: pointer;">
                        <input type="radio" name="payment_method" value="cod" checked>
                        <div>
                            <strong>Cash on Delivery (COD)</strong>
                            <div style="font-size: 0.8rem; color: #64748b;">Pay in cash or UPI upon parcel delivery</div>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; gap: 0.85rem; padding: 1rem; border: 1.5px solid #e2e8f0; border-radius: 10px; cursor: pointer;">
                        <input type="radio" name="payment_method" value="card">
                        <div>
                            <strong>Credit / Debit Card (Demo Gateway)</strong>
                            <div style="font-size: 0.8rem; color: #64748b;">Simulated instant card payment with zero gateway charge</div>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; gap: 0.85rem; padding: 1rem; border: 1.5px solid #e2e8f0; border-radius: 10px; cursor: pointer;">
                        <input type="radio" name="payment_method" value="upi">
                        <div>
                            <strong>UPI / Net Banking (Instant Pay)</strong>
                            <div style="font-size: 0.8rem; color: #64748b;">Google Pay, PhonePe, Paytm, BHIM</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Right: Order Summary -->
            <div class="order-summary-card">
                <h3 class="summary-title">Items in Order (<?= count($cartItems); ?>)</h3>

                <div style="display: flex; flex-direction: column; gap: 0.85rem; max-height: 260px; overflow-y: auto; margin-bottom: 1.25rem;">
                    <?php foreach ($cartItems as $item): 
                        $itemPrice = !empty($item['discount_price']) ? $item['discount_price'] : $item['price'];
                    ?>
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; gap: 0.5rem;">
                            <span style="color: #0f172a; font-weight: 500;">
                                <?= htmlspecialchars($item['title']); ?> <strong style="color: #64748b;">&times; <?= $item['quantity']; ?></strong>
                            </span>
                            <span style="font-weight: 700; white-space: nowrap;"><?= formatPrice($itemPrice * $item['quantity']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row">
                    <span>Subtotal:</span>
                    <strong><?= formatPrice($subtotal); ?></strong>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <strong><?= $shippingFee == 0 ? '<span style="color:#10b981;">FREE</span>' : formatPrice($shippingFee); ?></strong>
                </div>

                <div class="summary-row total">
                    <span>Final Amount:</span>
                    <span><?= formatPrice($totalAmount); ?></span>
                </div>

                <button type="submit" name="place_order" class="btn btn-primary btn-block btn-lg" style="margin-top: 1.5rem;">
                    <i class="fa-solid fa-circle-check"></i> Place Order Now
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
