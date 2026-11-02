<?php
/**
 * Wishlist Page
 * Project: Online Bookstore
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

// Handle Wishlist Actions
$action = $_GET['action'] ?? '';
$bookId = (int)($_GET['id'] ?? 0);

if ($bookId > 0 && !empty($action)) {
    if (!isLoggedIn()) {
        // Guest session fallback
        if (!isset($_SESSION['wishlist'])) $_SESSION['wishlist'] = [];
        
        if ($action === 'toggle' || $action === 'add') {
            if (!in_array($bookId, $_SESSION['wishlist'])) {
                $_SESSION['wishlist'][] = $bookId;
                setFlash('success', 'Book added to your wishlist!');
            } else {
                $_SESSION['wishlist'] = array_diff($_SESSION['wishlist'], [$bookId]);
                setFlash('info', 'Book removed from your wishlist.');
            }
        } elseif ($action === 'remove') {
            $_SESSION['wishlist'] = array_diff($_SESSION['wishlist'], [$bookId]);
            setFlash('info', 'Book removed from wishlist.');
        }
    } else {
        // Authenticated user: DB persistence
        $userId = $_SESSION['user_id'];
        if ($action === 'toggle' || $action === 'add') {
            $chk = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND book_id = ?");
            $chk->execute([$userId, $bookId]);
            if ($row = $chk->fetch()) {
                $del = $pdo->prepare("DELETE FROM wishlist WHERE id = ?");
                $del->execute([$row['id']]);
                setFlash('info', 'Book removed from your wishlist.');
            } else {
                $ins = $pdo->prepare("INSERT INTO wishlist (user_id, book_id) VALUES (?, ?)");
                $ins->execute([$userId, $bookId]);
                setFlash('success', 'Book saved to your wishlist!');
            }
        } elseif ($action === 'remove') {
            $del = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND book_id = ?");
            $del->execute([$userId, $bookId]);
            setFlash('info', 'Book removed from wishlist.');
        } elseif ($action === 'move_to_cart') {
            // Remove from wishlist and add to cart
            $del = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND book_id = ?");
            $del->execute([$userId, $bookId]);
            
            // Fetch book to add to cart
            $bStmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
            $bStmt->execute([$bookId]);
            if ($b = $bStmt->fetch()) {
                if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
                $_SESSION['cart'][$bookId] = [
                    'id'             => $b['id'],
                    'title'          => $b['title'],
                    'price'          => $b['price'],
                    'discount_price' => $b['discount_price'],
                    'image'          => $b['image'],
                    'stock'          => $b['stock'],
                    'quantity'       => 1
                ];
                setFlash('success', 'Moved "' . $b['title'] . '" to your shopping cart.');
            }
            header('Location: ' . BASE_URL . '/cart.php');
            exit;
        }
    }

    $redirect = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/wishlist.php');
    header('Location: ' . $redirect);
    exit;
}

// Fetch Wishlist Items
$wishlistBooks = [];
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT b.*, c.name as category_name 
                          FROM wishlist w 
                          JOIN books b ON w.book_id = b.id 
                          LEFT JOIN categories c ON b.category_id = c.id 
                          WHERE w.user_id = ? 
                          ORDER BY w.id DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $wishlistBooks = $stmt->fetchAll();
} else {
    $sessionIds = $_SESSION['wishlist'] ?? [];
    if (!empty($sessionIds)) {
        $in = str_repeat('?,', count($sessionIds) - 1) . '?';
        $stmt = $pdo->prepare("SELECT b.*, c.name as category_name 
                              FROM books b 
                              LEFT JOIN categories c ON b.category_id = c.id 
                              WHERE b.id IN ($in)");
        $stmt->execute(array_values($sessionIds));
        $wishlistBooks = $stmt->fetchAll();
    }
}

$pageTitle = 'My Wishlist';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container section">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 800;">My Wishlist</h1>
        <p style="color: #64748b;">Saved books you want to read or purchase later</p>
    </div>

    <?php if (!empty($wishlistBooks)): ?>
        <div class="books-grid">
            <?php foreach ($wishlistBooks as $book): 
                $displayPrice = !empty($book['discount_price']) ? $book['discount_price'] : $book['price'];
            ?>
                <div class="book-card">
                    <div class="book-cover-wrap">
                        <a href="<?= BASE_URL; ?>/book-details.php?id=<?= $book['id']; ?>">
                            <img src="<?= getBookImage($book['image']); ?>" alt="<?= htmlspecialchars($book['title']); ?>" onerror="this.src='<?= BASE_URL; ?>/assets/images/books/default-book.jpg'">
                        </a>
                    </div>
                    <div class="book-card-body">
                        <div class="book-category"><?= htmlspecialchars($book['category_name'] ?? 'General'); ?></div>
                        <h3 class="book-title">
                            <a href="<?= BASE_URL; ?>/book-details.php?id=<?= $book['id']; ?>"><?= htmlspecialchars($book['title']); ?></a>
                        </h3>
                        <div class="book-author">by <?= htmlspecialchars($book['author']); ?></div>
                        
                        <div style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0.5rem 0;">
                            <?= formatPrice($displayPrice); ?>
                        </div>

                        <div style="display: flex; gap: 0.5rem; margin-top: auto; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                            <form action="<?= BASE_URL; ?>/cart.php" method="POST" style="flex: 1; margin: 0;">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="book_id" value="<?= $book['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-primary btn-sm btn-block">
                                    <i class="fa-solid fa-cart-plus"></i> Move to Cart
                                </button>
                            </form>
                            <a href="<?= BASE_URL; ?>/wishlist.php?action=remove&id=<?= $book['id']; ?>" class="icon-btn" style="color: #ef4444;" title="Remove">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="background: #ffffff; border-radius: 16px; padding: 5rem 2rem; text-align: center; border: 1px solid #e2e8f0; max-width: 600px; margin: 0 auto;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 2.25rem; margin: 0 auto 1.5rem;">
                <i class="fa-regular fa-heart"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem;">Your Wishlist is Empty</h2>
            <p style="color: #64748b; margin-bottom: 2rem;">Save books you want to purchase in the future by clicking the heart icon on any book.</p>
            <a href="<?= BASE_URL; ?>/books.php" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-compass"></i> Discover Books
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
