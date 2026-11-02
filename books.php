<?php
/**
 * Books Catalog & Filter Page
 * Project: Online Bookstore
 */
$pageTitle = 'Explore All Books';
$activePage = 'books';
require_once __DIR__ . '/includes/header.php';

// Get query parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categorySlug = isset($_GET['category']) ? trim($_GET['category']) : '';
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'featured';
$maxPrice = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : 0;

// Fetch all categories for sidebar
try {
    $catStmt = $pdo->query("SELECT c.*, COUNT(b.id) as count 
                           FROM categories c 
                           LEFT JOIN books b ON c.id = b.category_id 
                           GROUP BY c.id 
                           ORDER BY c.name ASC");
    $allCategories = $catStmt->fetchAll();
} catch (Exception $e) {
    $allCategories = [];
}

// Build SQL query dynamically
$sql = "SELECT b.*, c.name as category_name, c.slug as category_slug 
        FROM books b 
        LEFT JOIN categories c ON b.category_id = c.id 
        WHERE 1=1";
$params = [];

// Filter: Category
$currentCategoryName = 'All Books';
if (!empty($categorySlug)) {
    $sql .= " AND c.slug = ?";
    $params[] = $categorySlug;
    foreach ($allCategories as $c) {
        if ($c['slug'] === $categorySlug) {
            $currentCategoryName = $c['name'];
            break;
        }
    }
}

// Filter: Search Keyword
if (!empty($search)) {
    $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

// Filter: Special flags
if ($filter === 'featured') {
    $sql .= " AND b.is_featured = 1";
} elseif ($filter === 'bestseller') {
    $sql .= " AND b.is_bestseller = 1";
}

// Filter: Max Price
if ($maxPrice > 0) {
    $sql .= " AND COALESCE(b.discount_price, b.price) <= ?";
    $params[] = $maxPrice;
}

// Sorting logic
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY COALESCE(b.discount_price, b.price) ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY COALESCE(b.discount_price, b.price) DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY b.rating DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY b.id DESC";
        break;
    case 'featured':
    default:
        $sql .= " ORDER BY b.is_featured DESC, b.rating DESC, b.id DESC";
        break;
}

// Execute query
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll();
} catch (Exception $e) {
    $books = [];
}
?>

<div class="container section">
    <div class="catalog-layout">
        <!-- Sidebar Filters -->
        <aside class="filter-sidebar">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: 800;"><i class="fa-solid fa-filter"></i> Filters</h3>
                <?php if (!empty($categorySlug) || !empty($search) || !empty($filter) || $maxPrice > 0): ?>
                    <a href="<?= BASE_URL; ?>/books.php" style="font-size: 0.8rem; color: #ef4444; font-weight: 600;">Reset All</a>
                <?php endif; ?>
            </div>

            <!-- Categories Filter -->
            <div class="filter-group">
                <div class="filter-title">Genres &amp; Categories</div>
                <ul class="filter-list">
                    <li>
                        <a href="<?= BASE_URL; ?>/books.php" class="<?= empty($categorySlug) ? 'active' : ''; ?>">
                            <span>All Genres</span>
                        </a>
                    </li>
                    <?php foreach ($allCategories as $cat): ?>
                        <li>
                            <a href="<?= BASE_URL; ?>/books.php?category=<?= urlencode($cat['slug']); ?><?= !empty($sort) ? '&sort=' . urlencode($sort) : ''; ?>" class="<?= $categorySlug === $cat['slug'] ? 'active' : ''; ?>">
                                <span><?= htmlspecialchars($cat['name']); ?></span>
                                <span class="filter-count"><?= (int)$cat['count']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Quick Collections -->
            <div class="filter-group">
                <div class="filter-title">Collections</div>
                <ul class="filter-list">
                    <li>
                        <a href="<?= BASE_URL; ?>/books.php?filter=bestseller" class="<?= $filter === 'bestseller' ? 'active' : ''; ?>">
                            <span><i class="fa-solid fa-fire text-amber"></i> Bestselling Books</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL; ?>/books.php?filter=featured" class="<?= $filter === 'featured' ? 'active' : ''; ?>">
                            <span><i class="fa-solid fa-star text-amber"></i> Editor's Picks</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Price Filter Form -->
            <div class="filter-group">
                <div class="filter-title">Filter by Max Price</div>
                <form action="<?= BASE_URL; ?>/books.php" method="GET">
                    <?php if (!empty($categorySlug)): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($categorySlug); ?>">
                    <?php endif; ?>
                    <?php if (!empty($search)): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>">
                    <?php endif; ?>
                    <?php if (!empty($sort)): ?>
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort); ?>">
                    <?php endif; ?>
                    <div style="display: flex; gap: 0.5rem; align-items: center; margin-top: 0.5rem;">
                        <input type="number" name="max_price" placeholder="e.g. 500" value="<?= $maxPrice > 0 ? $maxPrice : ''; ?>" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem;" min="100" max="5000">
                        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                    </div>
                </form>
            </div>
        </aside>

        <!-- Main Catalog Listing -->
        <main>
            <!-- Top Bar -->
            <div class="catalog-topbar">
                <div>
                    <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 2px;">
                        <?= htmlspecialchars($currentCategoryName); ?>
                        <?php if (!empty($search)): ?>
                            <span style="font-weight: 400; font-size: 1rem; color: #64748b;">(Matching "<?= htmlspecialchars($search); ?>")</span>
                        <?php endif; ?>
                    </h1>
                    <span style="font-size: 0.85rem; color: #64748b;">Showing <?= count($books); ?> results</span>
                </div>

                <!-- Sort Control -->
                <form action="<?= BASE_URL; ?>/books.php" method="GET" id="sortForm" style="display: flex; align-items: center; gap: 0.5rem;">
                    <?php if (!empty($categorySlug)): ?><input type="hidden" name="category" value="<?= htmlspecialchars($categorySlug); ?>"><?php endif; ?>
                    <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>"><?php endif; ?>
                    <?php if (!empty($filter)): ?><input type="hidden" name="filter" value="<?= htmlspecialchars($filter); ?>"><?php endif; ?>
                    <?php if ($maxPrice > 0): ?><input type="hidden" name="max_price" value="<?= $maxPrice; ?>"><?php endif; ?>
                    
                    <label for="sortSelect" style="font-size: 0.85rem; font-weight: 600; color: #64748b; white-space: nowrap;">Sort by:</label>
                    <select name="sort" id="sortSelect" class="sort-select" onchange="document.getElementById('sortForm').submit();">
                        <option value="featured" <?= $sort === 'featured' ? 'selected' : ''; ?>>Featured</option>
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="rating" <?= $sort === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                    </select>
                </form>
            </div>

            <!-- Book Grid -->
            <?php if (!empty($books)): ?>
                <div class="books-grid">
                    <?php foreach ($books as $book): ?>
                        <?php include __DIR__ . '/includes/book-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="background: #ffffff; border-radius: 12px; padding: 4rem 2rem; text-align: center; border: 1px solid #e2e8f0;">
                    <div style="width: 70px; height: 70px; background: #fee2e2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1.5rem;">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <h3 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.5rem;">No Books Found</h3>
                    <p style="color: #64748b; max-width: 440px; margin: 0 auto 1.5rem;">
                        We couldn't find any titles matching your filter criteria. Try adjusting your search keyword or clearing filters.
                    </p>
                    <a href="<?= BASE_URL; ?>/books.php" class="btn btn-primary">
                        <i class="fa-solid fa-arrow-rotate-left"></i> View All Books
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
