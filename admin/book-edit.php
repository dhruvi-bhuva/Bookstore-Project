<?php
/**
 * Admin: Edit Existing Book
 * Project: Online Bookstore
 */
$adminTitle = 'Edit Book';
$activeAdminPage = 'books';
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/includes/admin-sidebar.php';

$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Book
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$bookId]);
$book = $stmt->fetch();

if (!$book) {
    setFlash('danger', 'Book not found.');
    header('Location: ' . BASE_URL . '/admin/books.php');
    exit;
}

// Fetch Categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $author = sanitize($_POST['author'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $isbn = sanitize($_POST['isbn'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $discountPrice = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $pages = (int)($_POST['pages'] ?? 300);
    $publisher = sanitize($_POST['publisher'] ?? '');
    $language = sanitize($_POST['language'] ?? 'English');
    $pubYear = (int)($_POST['publication_year'] ?? date('Y'));
    $rating = (float)($_POST['rating'] ?? 4.5);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $description = sanitize($_POST['description'] ?? '');
    $imageUrl = sanitize($_POST['image_url'] ?? '');

    if (empty($title)) $errors[] = 'Title is required.';
    if (empty($author)) $errors[] = 'Author is required.';
    if ($price <= 0) $errors[] = 'Valid price is required.';

    // Preserve existing image unless replaced
    $imagePath = $book['image'];
    if (!empty($imageUrl)) {
        $imagePath = $imageUrl;
    }

    if (isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['cover_file']['tmp_name'];
        $fileName = basename($_FILES['cover_file']['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

        if (in_array($ext, $allowed)) {
            $newFileName = 'book_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $uploadDir = dirname(__DIR__) . '/uploads/books/';
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
            $uploadTarget = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmp, $uploadTarget)) {
                $imagePath = 'uploads/books/' . $newFileName;
            }
        }
    }

    if (empty($errors)) {
        try {
            $upd = $pdo->prepare("UPDATE books SET 
                category_id = ?, title = ?, author = ?, isbn = ?, price = ?, discount_price = ?, 
                description = ?, image = ?, stock = ?, pages = ?, publisher = ?, language = ?, 
                publication_year = ?, rating = ?, is_featured = ?, is_bestseller = ? 
                WHERE id = ?");
            $upd->execute([
                $categoryId, $title, $author, $isbn, $price, $discountPrice, 
                $description, $imagePath, $stock, $pages, $publisher, $language, 
                $pubYear, $rating, $isFeatured, $isBestseller, $bookId
            ]);
            setFlash('success', 'Book updated successfully.');
            header('Location: ' . BASE_URL . '/admin/books.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Update failed: ' . $e->getMessage();
        }
    }
}
?>

<div class="admin-main">
    <header class="admin-topbar">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0;">Edit Book</h2>
            <span style="font-size: 0.8rem; color: #64748b;">Editing: <?= htmlspecialchars($book['title']); ?></span>
        </div>
        <a href="<?= BASE_URL; ?>/admin/books.php" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Back to Books
        </a>
    </header>

    <div class="admin-content" style="max-width: 900px;">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="admin-card" style="padding: 2rem;">
            <form action="<?= BASE_URL; ?>/admin/book-edit.php?id=<?= $book['id']; ?>" method="POST" enctype="multipart/form-data">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Book Title *</label>
                        <input type="text" name="title" required class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? $book['title']); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category_id" required class="form-control">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id']; ?>" <?= ($book['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Author Name *</label>
                        <input type="text" name="author" required class="form-control" value="<?= htmlspecialchars($_POST['author'] ?? $book['author']); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">ISBN-13</label>
                        <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($_POST['isbn'] ?? $book['isbn']); ?>">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Price (₹) *</label>
                        <input type="number" step="0.01" name="price" required class="form-control" value="<?= htmlspecialchars($_POST['price'] ?? $book['price']); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Discount Price (₹)</label>
                        <input type="number" step="0.01" name="discount_price" class="form-control" value="<?= htmlspecialchars($_POST['discount_price'] ?? $book['discount_price']); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Stock Units *</label>
                        <input type="number" name="stock" required class="form-control" value="<?= htmlspecialchars($_POST['stock'] ?? $book['stock']); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Total Pages</label>
                        <input type="number" name="pages" class="form-control" value="<?= htmlspecialchars($_POST['pages'] ?? $book['pages']); ?>">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Publisher</label>
                        <input type="text" name="publisher" class="form-control" value="<?= htmlspecialchars($_POST['publisher'] ?? $book['publisher']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Language</label>
                        <input type="text" name="language" class="form-control" value="<?= htmlspecialchars($_POST['language'] ?? $book['language']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Publication Year</label>
                        <input type="number" name="publication_year" class="form-control" value="<?= htmlspecialchars($_POST['publication_year'] ?? $book['publication_year']); ?>">
                    </div>
                </div>

                <!-- Current Cover Preview & Replacement -->
                <div style="display: flex; gap: 1.5rem; align-items: center; background: #f8fafc; padding: 1.25rem; border-radius: 8px; margin: 1rem 0;">
                    <div>
                        <span style="font-size: 0.75rem; color: #64748b; display: block; margin-bottom: 4px;">Current Cover</span>
                        <img src="<?= getBookImage($book['image']); ?>" alt="" class="admin-thumb" style="width: 55px; height: 75px;" onerror="this.src='<?= BASE_URL; ?>/assets/images/books/default-book.jpg'">
                    </div>
                    <div style="flex: 1;">
                        <div class="form-group" style="margin-bottom: 0.5rem;">
                            <label class="form-label">Upload New Cover (Optional)</label>
                            <input type="file" name="cover_file" class="form-control" accept="image/*">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label">Or Image URL</label>
                            <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($book['image']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($_POST['description'] ?? $book['description']); ?></textarea>
                </div>

                <div style="display: flex; gap: 2rem; margin: 1.5rem 0;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" name="is_featured" value="1" <?= $book['is_featured'] ? 'checked' : ''; ?>>
                        <span>Mark as Featured Pick</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" name="is_bestseller" value="1" <?= $book['is_bestseller'] ? 'checked' : ''; ?>>
                        <span>Mark as Bestseller</span>
                    </label>
                </div>

                <div style="display: flex; gap: 1rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-floppy-disk"></i> Update Book
                    </button>
                    <a href="<?= BASE_URL; ?>/admin/books.php" class="btn btn-secondary btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
