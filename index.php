<?php
/**
 * Home Page
 * Project: Online Bookstore
 */
$pageTitle = 'Home - Discover Your Next Favorite Book';
$activePage = 'home';
require_once __DIR__ . '/includes/header.php';

// Fetch Categories with Book Count
try {
    $catStmt = $pdo->query("SELECT c.*, COUNT(b.id) as book_count 
                           FROM categories c 
                           LEFT JOIN books b ON c.id = b.category_id 
                           GROUP BY c.id 
                           ORDER BY c.name ASC");
    $categories = $catStmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

// Fetch Featured Books
try {
    $featStmt = $pdo->query("SELECT b.*, c.name as category_name, c.slug as category_slug 
                            FROM books b 
                            LEFT JOIN categories c ON b.category_id = c.id 
                            WHERE b.is_featured = 1 
                            ORDER BY b.id DESC 
                            LIMIT 4");
    $featuredBooks = $featStmt->fetchAll();
} catch (Exception $e) {
    $featuredBooks = [];
}

// Fetch Bestselling Books
try {
    $bestStmt = $pdo->query("SELECT b.*, c.name as category_name, c.slug as category_slug 
                            FROM books b 
                            LEFT JOIN categories c ON b.category_id = c.id 
                            WHERE b.is_bestseller = 1 
                            ORDER BY b.rating DESC 
                            LIMIT 8");
    $bestsellerBooks = $bestStmt->fetchAll();
} catch (Exception $e) {
    $bestsellerBooks = [];
}

// Fetch Recent Customer Reviews
try {
    $revStmt = $pdo->query("SELECT r.*, u.name as user_name, b.title as book_title 
                           FROM reviews r 
                           JOIN users u ON r.user_id = u.id 
                           JOIN books b ON r.book_id = b.id 
                           ORDER BY r.id DESC 
                           LIMIT 3");
    $reviews = $revStmt->fetchAll();
} catch (Exception $e) {
    $reviews = [];
}
?>

<!-- Hero Banner Section -->
<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-content">
            <div class="hero-tag">
                <i class="fa-solid fa-sparkles"></i> Welcome to BookVerse
            </div>
            <h1 class="hero-title">
                Expand Your Mind, <br>
                <span>One Page At A Time.</span>
            </h1>
            <p class="hero-desc">
                Explore handpicked collections across technology, classic literature, self-development, and sci-fi. Enjoy prompt delivery, student discounts, and exclusive editions.
            </p>
            <div class="hero-buttons">
                <a href="<?= BASE_URL; ?>/books.php" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-compass"></i> Explore All Books
                </a>
                <a href="<?= BASE_URL; ?>/books.php?filter=bestseller" class="btn btn-secondary btn-lg" style="background: rgba(255,255,255,0.12); color: #ffffff; border-color: rgba(255,255,255,0.2);">
                    <i class="fa-solid fa-fire text-amber"></i> View Bestsellers
                </a>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <h4>10,000+</h4>
                    <p>Books Available</p>
                </div>
                <div class="stat-item">
                    <h4>98.5%</h4>
                    <p>Positive Ratings</p>
                </div>
                <div class="stat-item">
                    <h4>24-Hour</h4>
                    <p>Dispatch Guarantee</p>
                </div>
            </div>
        </div>

        <div class="hero-showcase">
            <div class="hero-card-stack">
                <div class="floating-offer-badge">
                    <i class="fa-solid fa-bolt"></i> Flat 25% Off
                </div>
                <div class="hero-featured-card">
                    <div class="hero-card-image">
                        <img src="<?= getBookImage('assets/images/books/clean-code.jpg'); ?>" alt="Clean Code Book" onerror="this.src='<?= BASE_URL; ?>/assets/images/books/default-book.jpg'">
                    </div>
                    <div style="font-size: 0.75rem; font-weight: 700; color: #4f46e5; text-transform: uppercase;">Top Recommended</div>
                    <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0.25rem 0;">Clean Code</h3>
                    <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.75rem;">By Robert C. Martin</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 1.3rem; font-weight: 800; color: #0f172a;">₹549.00</span>
                        <a href="<?= BASE_URL; ?>/book-details.php?id=1" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-eye"></i> View Book
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Browse Categories Section -->
<section class="section" style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Browse By Category</h2>
                <p class="section-subtitle">Find your favorite genres curated with care</p>
            </div>
            <a href="<?= BASE_URL; ?>/books.php" class="btn btn-secondary btn-sm">
                View All <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="<?= BASE_URL; ?>/books.php?category=<?= urlencode($cat['slug']); ?>" class="category-card">
                    <div class="cat-icon">
                        <i class="fa-solid <?= htmlspecialchars($cat['icon'] ?: 'fa-book'); ?>"></i>
                    </div>
                    <h3><?= htmlspecialchars($cat['name']); ?></h3>
                    <p><?= (int)$cat['book_count']; ?> Books</p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Books Section -->
<?php if (!empty($featuredBooks)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="badge-tag badge-bestseller" style="margin-bottom: 6px; display: inline-block;">Handpicked</span>
                <h2 class="section-title">Editor's Featured Picks</h2>
                <p class="section-subtitle">Top-rated titles highly recommended by our literature team</p>
            </div>
            <a href="<?= BASE_URL; ?>/books.php?filter=featured" class="btn btn-outline btn-sm">
                View All Featured <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="books-grid">
            <?php foreach ($featuredBooks as $book): ?>
                <?php include __DIR__ . '/includes/book-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Big Promotional Banner -->
<section class="container">
    <div class="promo-banner">
        <div class="promo-content">
            <span class="promo-tag">Special Student Offer</span>
            <h2>Get Up To 30% Off On Computer Science &amp; Engineering Books</h2>
            <p>Empower your semester preparations with standard reference textbooks, algorithms, software engineering principles, and data science guides.</p>
            <a href="<?= BASE_URL; ?>/books.php?category=technology" class="btn btn-secondary btn-lg" style="background: #ffffff; color: #4f46e5; font-weight: 700;">
                <i class="fa-solid fa-laptop-code"></i> Shop Tech Books
            </a>
        </div>
        <div style="font-size: 7rem; opacity: 0.15; color: #ffffff;">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
    </div>
</section>

<!-- Bestselling Books Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Trending &amp; Bestsellers</h2>
                <p class="section-subtitle">The most popular and highly rated books right now</p>
            </div>
            <a href="<?= BASE_URL; ?>/books.php" class="btn btn-outline btn-sm">
                Explore Catalog <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="books-grid">
            <?php foreach ($bestsellerBooks as $book): ?>
                <?php include __DIR__ . '/includes/book-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Customer Reviews Section -->
<?php if (!empty($reviews)): ?>
<section class="section" style="background: #ffffff; border-top: 1px solid #e2e8f0;">
    <div class="container">
        <div class="section-header" style="justify-content: center; text-align: center;">
            <div>
                <h2 class="section-title">What Readers Are Saying</h2>
                <p class="section-subtitle">Real experiences and book reviews from verified students and readers</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <?php foreach ($reviews as $rev): ?>
                <div style="background: #f8fafc; border-radius: 12px; padding: 1.75rem; border: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="margin-bottom: 0.75rem;">
                            <?= renderStarRating($rev['rating']); ?>
                        </div>
                        <p style="font-size: 0.95rem; font-style: italic; color: #334155; margin-bottom: 1.25rem;">
                            "<?= htmlspecialchars($rev['review_text']); ?>"
                        </p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.75rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #4f46e5; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                            <?= strtoupper(substr($rev['user_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h4 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 2px;"><?= htmlspecialchars($rev['user_name']); ?></h4>
                            <span style="font-size: 0.75rem; color: #64748b;">on <em><?= htmlspecialchars($rev['book_title']); ?></em></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
