<?php
/**
 * Admin: Manage Categories
 * Project: Online Bookstore
 */
$adminTitle = 'Categories';
$activeAdminPage = 'categories';
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/includes/admin-sidebar.php';

$errors = [];

// Handle Category Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $catId = (int)$_GET['id'];
    try {
        $del = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $del->execute([$catId]);
        setFlash('success', 'Category deleted successfully.');
    } catch (Exception $e) {
        setFlash('danger', 'Error deleting category: ' . $e->getMessage());
    }
    header('Location: ' . BASE_URL . '/admin/categories.php');
    exit;
}

// Handle Add New Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = sanitize($_POST['name'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    $desc = sanitize($_POST['description'] ?? '');
    $icon = sanitize($_POST['icon'] ?? 'fa-book');

    if (empty($name)) $errors[] = 'Category name is required.';
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }

    if (empty($errors)) {
        try {
            $ins = $pdo->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
            $ins->execute([$name, $slug, $desc, $icon]);
            setFlash('success', 'New category created successfully.');
            header('Location: ' . BASE_URL . '/admin/categories.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Category creation failed: ' . $e->getMessage();
        }
    }
}

// Fetch Categories with Book Count
$categories = $pdo->query("SELECT c.*, COUNT(b.id) as book_count 
                           FROM categories c 
                           LEFT JOIN books b ON c.id = b.category_id 
                           GROUP BY c.id 
                           ORDER BY c.id ASC")->fetchAll();
?>

<div class="admin-main">
    <header class="admin-topbar">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0;">Manage Book Categories</h2>
            <span style="font-size: 0.8rem; color: #64748b;">Organize books by genre, study discipline, or themes</span>
        </div>
    </header>

    <div class="admin-content">
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']); ?>">
                <span><?= htmlspecialchars($flash['message']); ?></span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 2rem; align-items: start;">
            <!-- Form to Add Category -->
            <div class="admin-card" style="padding: 1.75rem;">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">Add New Category</h3>

                <form action="<?= BASE_URL; ?>/admin/categories.php" method="POST">
                    <input type="hidden" name="add_category" value="1">

                    <div class="form-group">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="name" required class="form-control" placeholder="e.g. Artificial Intelligence">
                    </div>

                    <div class="form-group">
                        <label class="form-label">URL Slug (Auto-generated if blank)</label>
                        <input type="text" name="slug" class="form-control" placeholder="e.g. artificial-intelligence">
                    </div>

                    <div class="form-group">
                        <label class="form-label">FontAwesome Icon Class</label>
                        <input type="text" name="icon" class="form-control" value="fa-book" placeholder="fa-brain, fa-rocket, fa-code">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Brief overview of books in this category"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-plus"></i> Create Category
                    </button>
                </form>
            </div>

            <!-- Existing Categories List -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Existing Categories (<?= count($categories); ?>)</h2>
                </div>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Slug</th>
                                <th>Books Count</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 32px; height: 32px; border-radius: 6px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid <?= htmlspecialchars($cat['icon']); ?>"></i>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($cat['name']); ?></strong>
                                                <div style="font-size: 0.75rem; color: #64748b;"><?= htmlspecialchars($cat['description'] ?? ''); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><code><?= htmlspecialchars($cat['slug']); ?></code></td>
                                    <td>
                                        <span class="badge-tag" style="background: #f1f5f9; color: #0f172a;">
                                            <?= (int)$cat['book_count']; ?> Books
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="<?= BASE_URL; ?>/admin/categories.php?action=delete&id=<?= $cat['id']; ?>" class="btn btn-secondary btn-sm" style="color: #ef4444;" title="Delete" data-confirm="Delete this category? Books in this category will become uncategorized.">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
