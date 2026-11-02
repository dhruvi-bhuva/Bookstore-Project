<?php
/**
 * Admin: Customer Contact Inquiries
 * Project: Online Bookstore
 */
$adminTitle = 'Contact Messages';
$activeAdminPage = 'messages';
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/includes/admin-sidebar.php';

// Mark as Read
if (isset($_GET['action']) && $_GET['action'] === 'read' && isset($_GET['id'])) {
    $msgId = (int)$_GET['id'];
    $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?")->execute([$msgId]);
    header('Location: ' . BASE_URL . '/admin/messages.php');
    exit;
}

// Delete Message
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $msgId = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$msgId]);
    setFlash('info', 'Message deleted.');
    header('Location: ' . BASE_URL . '/admin/messages.php');
    exit;
}

// Fetch Messages
$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY id DESC")->fetchAll();
?>

<div class="admin-main">
    <header class="admin-topbar">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0;">Reader Messages &amp; Inquiries</h2>
            <span style="font-size: 0.8rem; color: #64748b;">Feedback and support questions submitted via Contact form</span>
        </div>
    </header>

    <div class="admin-content">
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']); ?>">
                <span><?= htmlspecialchars($flash['message']); ?></span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($messages)): ?>
                            <?php foreach ($messages as $m): ?>
                                <tr style="<?= $m['status'] === 'unread' ? 'background: #f8fafc;' : ''; ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($m['name']); ?></strong>
                                        <div style="font-size: 0.8rem; color: #64748b;"><?= htmlspecialchars($m['email']); ?></div>
                                    </td>
                                    <td><strong><?= htmlspecialchars($m['subject']); ?></strong></td>
                                    <td style="max-width: 320px; font-size: 0.875rem; color: #475569;">
                                        <?= nl2br(htmlspecialchars($m['message'])); ?>
                                    </td>
                                    <td style="font-size: 0.8rem; color: #64748b; white-space: nowrap;">
                                        <?= date('d M Y, h:i A', strtotime($m['created_at'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($m['status'] === 'unread'): ?>
                                            <span class="badge-tag" style="background: #eff6ff; color: #1d4ed8;">Unread</span>
                                        <?php else: ?>
                                            <span class="badge-tag" style="background: #f1f5f9; color: #64748b;">Read</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <?php if ($m['status'] === 'unread'): ?>
                                            <a href="<?= BASE_URL; ?>/admin/messages.php?action=read&id=<?= $m['id']; ?>" class="btn btn-secondary btn-sm" title="Mark as Read">
                                                <i class="fa-solid fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL; ?>/admin/messages.php?action=delete&id=<?= $m['id']; ?>" class="btn btn-secondary btn-sm" style="color: #ef4444;" title="Delete" data-confirm="Delete this message?">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 3rem 1rem;">No inquiries received yet.</td></tr>
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
