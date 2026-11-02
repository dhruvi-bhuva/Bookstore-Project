<?php
/**
 * Admin: Manage Books Catalog
 * Project: Online Bookstore
 */
$adminTitle = 'Manage Books';
$activeAdminPage = 'books';
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/includes/admin-sidebar.php';

// Handle Book Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $bookId = (int)$_GET['id'];
    try {
        // Fetch book title before deletion
        $stmtTitle = $pdo->prepare("SELECT title FROM books WHERE id = ?");
        $stmtTitle->execute([$bookId]);
        $bookTitle = $stmtTitle->fetchColumn();

        if ($bookTitle) {
            // Clean up related child rows to prevent foreign key errors
            $pdo->prepare("DELETE FROM wishlist WHERE book_id = ?")->execute([$bookId]);
            $pdo->prepare("DELETE FROM reviews WHERE book_id = ?")->execute([$bookId]);
            $pdo->prepare("UPDATE order_items SET book_id = NULL WHERE book_id = ?")->execute([$bookId]);

            // Delete book record
            $del = $pdo->prepare("DELETE FROM books WHERE id = ?");
            $del->execute([$bookId]);

            setFlash('success', 'Book "' . htmlspecialchars($bookTitle) . '" was deleted successfully.');
        } else {
            setFlash('warning', 'Book not found.');
        }
    } catch (Exception $e) {
        setFlash('danger', 'Cannot delete book: ' . $e->getMessage());
    }
    header('Location: ' . BASE_URL . '/admin/books.php');
    exit;
}

// Search Filter
$search = sanitize($_GET['search'] ?? '');
$sql = "SELECT b.*, c.name as category_name 
        FROM books b 
        LEFT JOIN categories c ON b.category_id = c.id";
$params = [];

if (!empty($search)) {
    $sql .= " WHERE b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?";
    $like = '%' . $search . '%';
    $params = [$like, $like, $like];
}
$sql .= " ORDER BY b.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();
?>

<div class="admin-main">
    <header class="admin-topbar">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0;">Manage Books Catalog</h2>
            <span style="font-size: 0.8rem; color: #64748b;">Total books: <?= count($books); ?></span>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <a href="<?= BASE_URL; ?>/admin/book-add.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i> Add New Book
            </a>
        </div>
    </header>

    <div class="admin-content">
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']); ?>">
                <span><?= htmlspecialchars($flash['message']); ?></span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 1rem 1.5rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <form action="<?= BASE_URL; ?>/admin/books.php" method="GET" style="display: flex; gap: 0.75rem; flex: 1; max-width: 480px;">
                <input type="text" name="search" class="form-control" placeholder="Search by title, author, or ISBN..." value="<?= htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-search"></i></button>
                <?php if (!empty($search)): ?>
                    <a href="<?= BASE_URL; ?>/admin/books.php" class="btn btn-outline">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="admin-card">
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Badges</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($books)): ?>
                            <?php foreach ($books as $b): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <img src="<?= getBookImage($b['image']); ?>" alt="" class="admin-thumb" onerror="this.src='<?= BASE_URL; ?>/assets/images/books/default-book.jpg'">
                                            <div>
                                                <strong style="color: #0f172a; display: block; font-size: 0.95rem;"><?= htmlspecialchars($b['title']); ?></strong>
                                                <span style="font-size: 0.8rem; color: #64748b;">By <?= htmlspecialchars($b['author']); ?></span>
                                                <?php if (!empty($b['isbn'])): ?>
                                                    <div style="font-size: 0.725rem; color: #94a3b8;">ISBN: <?= htmlspecialchars($b['isbn']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-tag" style="background: #f1f5f9; color: #334155;">
                                            <?= htmlspecialchars($b['category_name'] ?? 'Uncategorized'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= formatPrice(!empty($b['discount_price']) ? $b['discount_price'] : $b['price']); ?></strong>
                                        <?php if (!empty($b['discount_price'])): ?>
                                            <div style="font-size: 0.75rem; color: #94a3b8; text-decoration: line-through;"><?= formatPrice($b['price']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="font-weight: 700; color: <?= $b['stock'] < 10 ? '#ef4444' : '#059669'; ?>;">
                                            <?= (int)$b['stock']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($b['is_featured']): ?>
                                            <span class="badge-tag" style="background: #e0e7ff; color: #4338ca;">Featured</span>
                                        <?php endif; ?>
                                        <?php if ($b['is_bestseller']): ?>
                                            <span class="badge-tag" style="background: #fef3c7; color: #b45309;">Bestseller</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="<?= BASE_URL; ?>/admin/book-edit.php?id=<?= $b['id']; ?>" class="btn btn-secondary btn-sm" title="Edit Book">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </a>
                                        <a href="<?= BASE_URL; ?>/admin/books.php?action=delete&id=<?= $b['id']; ?>" class="btn btn-secondary btn-sm" style="color: #ef4444;" title="Delete Book" data-confirm="Are you sure you want to permanently delete '<?= htmlspecialchars(addslashes($b['title'])); ?>'?">
                                            <i class="fa-solid fa-trash-can"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 3rem 1rem;">No books found matching criteria.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
