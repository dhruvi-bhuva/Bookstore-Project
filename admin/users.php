<?php
/**
 * Admin: Customer Accounts
 * Project: Online Bookstore
 */
$adminTitle = 'Registered Users';
$activeAdminPage = 'users';
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/includes/admin-sidebar.php';

$users = $pdo->query("SELECT u.*, COUNT(o.id) as orders_count 
                      FROM users u 
                      LEFT JOIN orders o ON u.id = o.user_id 
                      GROUP BY u.id 
                      ORDER BY u.id ASC")->fetchAll();
?>

<div class="admin-main">
    <header class="admin-topbar">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0;">Registered Accounts</h2>
            <span style="font-size: 0.8rem; color: #64748b;">Total users: <?= count($users); ?></span>
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
                            <th>User</th>
                            <th>Role</th>
                            <th>Phone</th>
                            <th>City / Location</th>
                            <th>Orders Placed</th>
                            <th>Registered Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 36px; height: 36px; border-radius: 50%; background: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                            <?= strtoupper(substr($u['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong style="color: #0f172a;"><?= htmlspecialchars($u['name']); ?></strong>
                                            <div style="font-size: 0.8rem; color: #64748b;"><?= htmlspecialchars($u['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge-tag" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;">ADMIN</span>
                                    <?php else: ?>
                                        <span class="badge-tag" style="background: #f1f5f9; color: #475569;">CUSTOMER</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($u['phone'] ?: 'N/A'); ?></td>
                                <td><?= htmlspecialchars($u['city'] ?: 'N/A'); ?></td>
                                <td><strong><?= (int)$u['orders_count']; ?></strong></td>
                                <td style="font-size: 0.8rem; color: #64748b;">
                                    <?= date('d M Y', strtotime($u['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
