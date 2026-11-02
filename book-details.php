<?php
/**
 * Single Book Details Page
 * Project: Online Bookstore
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($bookId <= 0) {
    header('Location: ' . BASE_URL . '/books.php');
    exit;
}

// Handle new review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        setFlash('warning', 'Please login to write a review.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
    $rating = (int)($_POST['rating'] ?? 5);
    $reviewText = sanitize($_POST['review_text'] ?? '');
    
    if ($rating >= 1 && $rating <= 5 && !empty($reviewText)) {
        try {
            $insRev = $pdo->prepare("INSERT INTO reviews (book_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)");
            $insRev->execute([$bookId, $_SESSION['user_id'], $rating, $reviewText]);
            setFlash('success', 'Thank you! Your book review has been submitted.');
        } catch (Exception $e) {
            setFlash('danger', 'Error submitting review. Please try again.');
        }
    }
    header('Location: ' . BASE_URL . '/book-details.php?id=' . $bookId);
    exit;
}

// Fetch book details
try {
    $stmt = $pdo->prepare("SELECT b.*, c.name as category_name, c.slug as category_slug 
                          FROM books b 
                          LEFT JOIN categories c ON b.category_id = c.id 
                          WHERE b.id = ?");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();
    if (!$book) {
        setFlash('danger', 'Book not found.');
        header('Location: ' . BASE_URL . '/books.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: ' . BASE_URL . '/books.php');
    exit;
}

// Fetch reviews for this book
try {
    $revStmt = $pdo->prepare("SELECT r.*, u.name as user_name 
                             FROM reviews r 
                             JOIN users u ON r.user_id = u.id 
                             WHERE r.book_id = ? 
                             ORDER BY r.id DESC");
    $revStmt->execute([$bookId]);
    $bookReviews = $revStmt->fetchAll();
} catch (Exception $e) {
    $bookReviews = [];
}

// Fetch related books in the same category
try {
    $relStmt = $pdo->prepare("SELECT b.*, c.name as category_name 
                             FROM books b 
                             LEFT JOIN categories c ON b.category_id = c.id 
                             WHERE b.category_id = ? AND b.id != ? 
                             ORDER BY b.rating DESC LIMIT 4");
    $relStmt->execute([$book['category_id'], $bookId]);
    $relatedBooks = $relStmt->fetchAll();
} catch (Exception $e) {
    $relatedBooks = [];
}

$pageTitle = $book['title'] . ' - Book Details';
require_once __DIR__ . '/includes/header.php';

$discountPercent = 0;
if (!empty($book['discount_price']) && $book['discount_price'] < $book['price']) {
    $discountPercent = round((($book['price'] - $book['discount_price']) / $book['price']) * 100);
}
$displayPrice = !empty($book['discount_price']) ? $book['discount_price'] : $book['price'];
?>

<div class="container section">
    <!-- Breadcrumbs -->
    <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 2rem;">
        <a href="<?= BASE_URL; ?>/index.php">Home</a> &rsaquo; 
        <a href="<?= BASE_URL; ?>/books.php">Books</a> &rsaquo; 
        <a href="<?= BASE_URL; ?>/books.php?category=<?= urlencode($book['category_slug']); ?>"><?= htmlspecialchars($book['category_name']); ?></a> &rsaquo; 
        <span style="color: #0f172a; font-weight: 600;"><?= htmlspecialchars($book['title']); ?></span>
    </div>

    <!-- Book Details Card -->
    <div class="book-details-layout">
        <!-- Book Cover Column -->
        <div>
            <div class="book-detail-cover">
                <img src="<?= getBookImage($book['image']); ?>" alt="<?= htmlspecialchars($book['title']); ?>" onerror="this.src='<?= BASE_URL; ?>/assets/images/books/default-book.jpg'">
            </div>
            
            <div style="margin-top: 1.5rem; text-align: center;">
                <a href="<?= BASE_URL; ?>/wishlist.php?action=toggle&id=<?= $book['id']; ?>" class="btn btn-outline btn-block" style="gap: 0.5rem;">
                    <i class="fa-regular fa-heart"></i> Add to Wishlist
                </a>
            </div>
        </div>

        <!-- Book Info Column -->
        <div class="book-detail-info">
            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                <span class="badge-tag badge-category"><?= htmlspecialchars($book['category_name']); ?></span>
                <?php if (!empty($book['is_bestseller'])): ?>
                    <span class="badge-tag badge-bestseller"><i class="fa-solid fa-fire"></i> Bestseller</span>
                <?php endif; ?>
                <?php if ($book['stock'] > 0): ?>
                    <span class="badge-tag" style="background: #ecfdf5; color: #065f46;"><i class="fa-solid fa-check"></i> In Stock (<?= (int)$book['stock']; ?> copies)</span>
                <?php else: ?>
                    <span class="badge-tag" style="background: #fee2e2; color: #991b1b;"><i class="fa-solid fa-xmark"></i> Out of Stock</span>
                <?php endif; ?>
            </div>

            <h1><?= htmlspecialchars($book['title']); ?></h1>
            <div class="book-author-meta">By <strong><?= htmlspecialchars($book['author']); ?></strong></div>

            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem;">
                <?= renderStarRating($book['rating']); ?>
                <span style="font-size: 0.85rem; color: #64748b;">(<?= count($bookReviews); ?> reader reviews)</span>
            </div>

            <!-- Price Breakdown -->
            <div class="detail-price-box">
                <span class="detail-price"><?= formatPrice($displayPrice); ?></span>
                <?php if ($discountPercent > 0): ?>
                    <span class="detail-old-price"><?= formatPrice($book['price']); ?></span>
                    <span class="detail-discount-tag">Save <?= $discountPercent; ?>%</span>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div style="margin-bottom: 1.75rem;">
                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.5rem;">Overview</h3>
                <p style="color: #475569; line-height: 1.7; font-size: 0.95rem;">
                    <?= nl2br(htmlspecialchars($book['description'])); ?>
                </p>
            </div>

            <!-- Specifications Table -->
            <div class="specs-grid">
                <div class="spec-item">
                    <span class="spec-label">ISBN-13</span>
                    <span class="spec-value"><?= htmlspecialchars($book['isbn'] ?: 'N/A'); ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Publisher</span>
                    <span class="spec-value"><?= htmlspecialchars($book['publisher'] ?: 'Standard Editions'); ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Total Pages</span>
                    <span class="spec-value"><?= (int)$book['pages']; ?> Pages</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Language</span>
                    <span class="spec-value"><?= htmlspecialchars($book['language'] ?: 'English'); ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Publication Year</span>
                    <span class="spec-value"><?= (int)$book['publication_year']; ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Shipping</span>
                    <span class="spec-value">Dispatched within 24h</span>
                </div>
            </div>

            <!-- Add to Cart Form -->
            <?php if ($book['stock'] > 0): ?>
                <form action="<?= BASE_URL; ?>/cart.php" method="POST" class="detail-actions">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="book_id" value="<?= $book['id']; ?>">
                    
                    <div class="qty-selector">
                        <button type="button" class="qty-btn qty-minus">-</button>
                        <input type="number" name="quantity" class="qty-input" value="1" min="1" max="<?= (int)$book['stock']; ?>">
                        <button type="button" class="qty-btn qty-plus">+</button>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                        <i class="fa-solid fa-cart-plus"></i> Add to Shopping Cart
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i> Currently out of stock. We are replenishing inventory shortly.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reviews Section -->
    <div style="margin-top: 3.5rem;">
        <div style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 2.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h2 style="font-size: 1.5rem; font-weight: 800;">Customer Reviews (<?= count($bookReviews); ?>)</h2>
                    <p style="color: #64748b; font-size: 0.9rem;">What fellow readers have experienced with this book</p>
                </div>
            </div>

            <!-- Existing Reviews List -->
            <?php if (!empty($bookReviews)): ?>
                <div style="display: flex; flex-direction: column; gap: 1.25rem; margin-bottom: 2.5rem;">
                    <?php foreach ($bookReviews as $rev): ?>
                        <div style="padding: 1.25rem; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <strong><?= htmlspecialchars($rev['user_name']); ?></strong>
                                <span style="font-size: 0.8rem; color: #94a3b8;"><?= date('M d, Y', strtotime($rev['created_at'])); ?></span>
                            </div>
                            <div style="margin-bottom: 0.5rem;">
                                <?= renderStarRating($rev['rating']); ?>
                            </div>
                            <p style="color: #475569; font-size: 0.925rem; margin: 0;">
                                <?= htmlspecialchars($rev['review_text']); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #64748b; margin-bottom: 2rem;">No reviews yet. Be the first reader to review this book!</p>
            <?php endif; ?>

            <!-- Leave a Review Form -->
            <div style="border-top: 1px solid #e2e8f0; padding-top: 2rem;">
                <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem;">Write a Review</h3>
                <?php if (isLoggedIn()): ?>
                    <form action="<?= BASE_URL; ?>/book-details.php?id=<?= $book['id']; ?>" method="POST" style="max-width: 600px;">
                        <input type="hidden" name="submit_review" value="1">
                        
                        <div class="form-group">
                            <label class="form-label">Your Rating (1 to 5 Stars):</label>
                            <select name="rating" class="form-control" style="width: auto;">
                                <option value="5">⭐⭐⭐⭐⭐ (5 - Outstanding)</option>
                                <option value="4">⭐⭐⭐⭐ (4 - Very Good)</option>
                                <option value="3">⭐⭐⭐ (3 - Average)</option>
                                <option value="2">⭐⭐ (2 - Below Average)</option>
                                <option value="1">⭐ (1 - Disappointing)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Your Feedback / Review:</label>
                            <textarea name="review_text" rows="3" required class="form-control" placeholder="Share your key takeaways and thoughts about this book..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-paper-plane"></i> Submit Review
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info" style="margin: 0;">
                        <i class="fa-solid fa-circle-info"></i> Please <a href="<?= BASE_URL; ?>/login.php" style="text-decoration: underline; font-weight: 700;">sign in</a> to leave your rating and review for this book.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Related Books -->
    <?php if (!empty($relatedBooks)): ?>
        <div style="margin-top: 4rem;">
            <div class="section-header">
                <div>
                    <h2 class="section-title">You Might Also Like</h2>
                    <p class="section-subtitle">More popular titles from <?= htmlspecialchars($book['category_name']); ?></p>
                </div>
            </div>
            <div class="books-grid">
                <?php foreach ($relatedBooks as $book): ?>
                    <?php include __DIR__ . '/includes/book-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
