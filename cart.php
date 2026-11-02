<?php
/**
 * Shopping Cart Management
 * Project: Online Bookstore
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Cart Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action: Add Item to Cart
    if ($action === 'add') {
        $bookId = (int)($_POST['book_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        if ($quantity <= 0) $quantity = 1;

        if ($bookId > 0) {
            $stmt = $pdo->prepare("SELECT id, title, price, discount_price, image, stock FROM books WHERE id = ?");
            $stmt->execute([$bookId]);
            $book = $stmt->fetch();

            if ($book && $book['stock'] > 0) {
                if (isset($_SESSION['cart'][$bookId])) {
                    $newQty = $_SESSION['cart'][$bookId]['quantity'] + $quantity;
                    $_SESSION['cart'][$bookId]['quantity'] = min($newQty, $book['stock']);
                } else {
                    $_SESSION['cart'][$bookId] = [
                        'id'             => $book['id'],
                        'title'          => $book['title'],
                        'price'          => $book['price'],
                        'discount_price' => $book['discount_price'],
                        'image'          => $book['image'],
                        'stock'          => $book['stock'],
                        'quantity'       => min($quantity, $book['stock'])
                    ];
                }
                setFlash('success', 'Added "' . $book['title'] . '" to your shopping cart.');
            } else {
                setFlash('danger', 'Sorry, this book is currently out of stock.');
            }
        }
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    // Action: Update Quantity
    if ($action === 'update') {
        $quantities = $_POST['quantities'] ?? [];
        foreach ($quantities as $id => $qty) {
            $id = (int)$id;
            $qty = (int)$qty;
            if (isset($_SESSION['cart'][$id])) {
                if ($qty <= 0) {
                    unset($_SESSION['cart'][$id]);
                } else {
                    $maxStock = $_SESSION['cart'][$id]['stock'] ?? 50;
                    $_SESSION['cart'][$id]['quantity'] = min($qty, $maxStock);
                }
            }
        }
        setFlash('success', 'Shopping cart updated successfully.');
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    // Action: Remove Item
    if ($action === 'remove') {
        $bookId = (int)($_POST['book_id'] ?? 0);
        if (isset($_SESSION['cart'][$bookId])) {
            $removedTitle = $_SESSION['cart'][$bookId]['title'];
            unset($_SESSION['cart'][$bookId]);
            setFlash('info', 'Removed "' . $removedTitle . '" from your cart.');
        }
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    // Action: Clear Cart
    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        setFlash('info', 'Your shopping cart has been cleared.');
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }
}

$pageTitle = 'Shopping Cart';
$activePage = 'cart';
require_once __DIR__ . '/includes/header.php';

$cartItems = $_SESSION['cart'];
$subtotal = getCartSubtotal();
$shippingFee = ($subtotal >= 500 || $subtotal == 0) ? 0.00 : 40.00;
$total = $subtotal + $shippingFee;
?>

<div class="container section">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 800;">Shopping Cart</h1>
        <p style="color: #64748b;">Review your selected books before proceeding to secure checkout</p>
    </div>

    <?php if (!empty($cartItems)): ?>
        <div class="cart-layout">
            <!-- Left: Items Table -->
            <div class="cart-card">
                <form action="<?= BASE_URL; ?>/cart.php" method="POST" id="cartForm">
                    <input type="hidden" name="action" value="update">
                    
                    <div style="overflow-x: auto;">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $id => $item): 
                                    $itemPrice = !empty($item['discount_price']) ? $item['discount_price'] : $item['price'];
                                    $itemTotal = $itemPrice * $item['quantity'];
                                ?>
                                    <tr>
                                        <td>
                                            <div class="cart-book-cell">
                                                <div class="cart-thumb">
                                                    <img src="<?= getBookImage($item['image']); ?>" alt="<?= htmlspecialchars($item['title']); ?>" onerror="this.src='<?= BASE_URL; ?>/assets/images/books/default-book.jpg'">
                                                </div>
                                                <div>
                                                    <a href="<?= BASE_URL; ?>/book-details.php?id=<?= $id; ?>" style="font-weight: 700; color: #0f172a; line-height: 1.3; display: block; margin-bottom: 4px;">
                                                        <?= htmlspecialchars($item['title']); ?>
                                                    </a>
                                                    <span style="font-size: 0.75rem; color: #10b981; font-weight: 600;"><i class="fa-solid fa-circle-check"></i> In Stock</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="font-weight: 600;">
                                            <?= formatPrice($itemPrice); ?>
                                        </td>
                                        <td>
                                            <div class="qty-selector" style="transform: scale(0.9); transform-origin: left;">
                                                <button type="button" class="qty-btn qty-minus">-</button>
                                                <input type="number" name="quantities[<?= $id; ?>]" class="qty-input" value="<?= (int)$item['quantity']; ?>" min="1" max="<?= (int)($item['stock'] ?? 50); ?>">
                                                <button type="button" class="qty-btn qty-plus">+</button>
                                            </div>
                                        </td>
                                        <td style="font-weight: 800; color: #0f172a;">
                                            <?= formatPrice($itemTotal); ?>
                                        </td>
                                        <td>
                                            <button type="button" class="icon-btn" style="color: #ef4444; width: 34px; height: 34px;" title="Remove Item" onclick="removeCartItem(<?= $id; ?>)">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Cart Buttons Bar -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9; flex-wrap: wrap; gap: 1rem;">
                        <a href="<?= BASE_URL; ?>/books.php" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left"></i> Continue Shopping
                        </a>
                        <div style="display: flex; gap: 0.75rem;">
                            <button type="button" class="btn btn-secondary" onclick="clearCart()">
                                <i class="fa-solid fa-trash-arrow-up"></i> Clear Cart
                            </button>
                            <button type="submit" class="btn btn-outline">
                                <i class="fa-solid fa-arrows-rotate"></i> Update Cart
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Hidden single item remove form -->
                <form id="removeItemForm" action="<?= BASE_URL; ?>/cart.php" method="POST" style="display: none;">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="book_id" id="removeBookId" value="">
                </form>

                <!-- Hidden clear cart form -->
                <form id="clearCartForm" action="<?= BASE_URL; ?>/cart.php" method="POST" style="display: none;">
                    <input type="hidden" name="action" value="clear">
                </form>
            </div>

            <!-- Right: Order Summary Card -->
            <div class="order-summary-card">
                <h3 class="summary-title">Order Summary</h3>
                <div class="summary-row">
                    <span>Items Subtotal:</span>
                    <strong style="color: #0f172a;"><?= formatPrice($subtotal); ?></strong>
                </div>
                <div class="summary-row">
                    <span>Shipping Fee:</span>
                    <?php if ($shippingFee == 0): ?>
                        <strong style="color: #10b981;">FREE</strong>
                    <?php else: ?>
                        <strong style="color: #0f172a;"><?= formatPrice($shippingFee); ?></strong>
                    <?php endif; ?>
                </div>

                <?php if ($subtotal < 500): ?>
                    <div style="background: #eff6ff; border-radius: 8px; padding: 0.75rem; font-size: 0.8rem; color: #1e40af; margin-bottom: 1rem;">
                        <i class="fa-solid fa-truck-fast"></i> Add <strong><?= formatPrice(500 - $subtotal); ?></strong> more to get <strong>Free Delivery</strong>!
                    </div>
                <?php endif; ?>

                <div class="summary-row total">
                    <span>Estimated Total:</span>
                    <span><?= formatPrice($total); ?></span>
                </div>

                <div style="margin-top: 1.5rem;">
                    <a href="<?= BASE_URL; ?>/checkout.php" class="btn btn-primary btn-block btn-lg">
                        <i class="fa-solid fa-lock"></i> Proceed to Checkout
                    </a>
                </div>

                <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 1.25rem; font-size: 0.75rem; color: #64748b;">
                    <i class="fa-solid fa-shield-halved text-success"></i> 256-Bit SSL Secured Checkout
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Empty Cart State -->
        <div style="background: #ffffff; border-radius: 16px; padding: 5rem 2rem; text-align: center; border: 1px solid #e2e8f0; max-width: 600px; margin: 0 auto;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 2.25rem; margin: 0 auto 1.5rem;">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem;">Your Cart is Empty</h2>
            <p style="color: #64748b; margin-bottom: 2rem;">Looks like you haven't added any books to your cart yet. Discover your next great read today!</p>
            <a href="<?= BASE_URL; ?>/books.php" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-book-open"></i> Start Exploring Books
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
function removeCartItem(bookId) {
    if (confirm('Remove this book from your cart?')) {
        document.getElementById('removeBookId').value = bookId;
        document.getElementById('removeItemForm').submit();
    }
}
function clearCart() {
    if (confirm('Are you sure you want to empty your entire cart?')) {
        document.getElementById('clearCartForm').submit();
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
