<?php
/**
 * Single Book Card Partial
 * Expected input: $book array
 */
$discountPercent = 0;
if (!empty($book['discount_price']) && $book['discount_price'] < $book['price']) {
    $discountPercent = round((($book['price'] - $book['discount_price']) / $book['price']) * 100);
}
$displayPrice = !empty($book['discount_price']) ? $book['discount_price'] : $book['price'];
?>
<div class="book-card">
    <div class="book-cover-wrap">
        <!-- Badges -->
        <div class="book-badges">
            <?php if (!empty($book['is_bestseller'])): ?>
                <span class="badge-tag badge-bestseller"><i class="fa-solid fa-fire"></i> Bestseller</span>
            <?php endif; ?>
            <?php if ($discountPercent > 0): ?>
                <span class="badge-tag badge-discount">-<?= $discountPercent; ?>%</span>
            <?php endif; ?>
        </div>

        <!-- Wishlist Button -->
        <div class="book-actions-overlay">
            <a href="<?= BASE_URL; ?>/wishlist.php?action=toggle&id=<?= $book['id']; ?>" class="wishlist-btn" title="Add to Wishlist">
                <i class="fa-regular fa-heart"></i>
            </a>
        </div>

        <!-- Cover Image -->
        <a href="<?= BASE_URL; ?>/book-details.php?id=<?= $book['id']; ?>">
            <img src="<?= getBookImage($book['image']); ?>" alt="<?= htmlspecialchars($book['title']); ?>" loading="lazy" onerror="this.src='<?= BASE_URL; ?>/assets/images/books/default-book.jpg'">
        </a>
    </div>

    <div class="book-card-body">
        <?php if (!empty($book['category_name'])): ?>
            <div class="book-category"><?= htmlspecialchars($book['category_name']); ?></div>
        <?php endif; ?>

        <h3 class="book-title">
            <a href="<?= BASE_URL; ?>/book-details.php?id=<?= $book['id']; ?>" title="<?= htmlspecialchars($book['title']); ?>">
                <?= htmlspecialchars($book['title']); ?>
            </a>
        </h3>

        <div class="book-author">by <?= htmlspecialchars($book['author']); ?></div>

        <?= renderStarRating($book['rating'] ?? 4.5); ?>

        <div class="book-card-footer">
            <div class="price-box">
                <span class="current-price"><?= formatPrice($displayPrice); ?></span>
                <?php if ($discountPercent > 0): ?>
                    <span class="old-price"><?= formatPrice($book['price']); ?></span>
                <?php endif; ?>
            </div>

            <form action="<?= BASE_URL; ?>/cart.php" method="POST" style="margin: 0;">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="book_id" value="<?= $book['id']; ?>">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-primary btn-sm" title="Add to Cart">
                    <i class="fa-solid fa-cart-plus"></i> Add
                </button>
            </form>
        </div>
    </div>
</div>
