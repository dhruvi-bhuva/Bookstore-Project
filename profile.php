<?php
/**
 * User Profile & Settings Page
 * Project: Online Bookstore
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$errors = [];

// Handle Profile Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $pincode = sanitize($_POST['pincode'] ?? '');
    
    // Optional password change
    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';

    if (empty($name)) {
        $errors[] = 'Full name cannot be empty.';
    }

    if (empty($errors)) {
        try {
            $upd = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ?, city = ?, state = ?, pincode = ? WHERE id = ?");
            $upd->execute([$name, $phone, $address, $city, $state, $pincode, $userId]);
            $_SESSION['user_name'] = $name;

            // Handle password change if requested
            if (!empty($newPass)) {
                $uStmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $uStmt->execute([$userId]);
                $currHash = $uStmt->fetchColumn();

                if (verifyUserPassword($currentPass, $currHash)) {
                    if (strlen($newPass) >= 6) {
                        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                        $pUpd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $pUpd->execute([$newHash, $userId]);
                        setFlash('success', 'Profile and password updated successfully.');
                    } else {
                        $errors[] = 'New password must be at least 6 characters long.';
                    }
                } else {
                    $errors[] = 'Current password entered is incorrect.';
                }
            } else {
                setFlash('success', 'Profile details updated successfully.');
            }

            if (empty($errors)) {
                header('Location: ' . BASE_URL . '/profile.php');
                exit;
            }
        } catch (Exception $e) {
            $errors[] = 'Failed to update profile: ' . $e->getMessage();
        }
    }
}

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Count user orders
$orderCountStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$orderCountStmt->execute([$userId]);
$orderCount = (int)$orderCountStmt->fetchColumn();

$pageTitle = 'My Account Profile';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container section" style="max-width: 850px;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 800;">Account Settings</h1>
        <p style="color: #64748b;">Manage your personal profile, delivery address, and security</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 280px 1fr; gap: 2rem; align-items: start;">
        <!-- Left: Account Avatar & Stats -->
        <div style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 2rem; text-align: center;">
            <div style="width: 84px; height: 84px; border-radius: 50%; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; margin: 0 auto 1.25rem;">
                <?= strtoupper(substr($user['name'], 0, 1)); ?>
            </div>
            <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 4px;"><?= htmlspecialchars($user['name']); ?></h3>
            <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 1.5rem;"><?= htmlspecialchars($user['email']); ?></p>

            <div style="border-top: 1px solid #f1f5f9; padding-top: 1.25rem; text-align: left; font-size: 0.85rem;">
                <p style="margin-bottom: 0.5rem; color: #64748b;">Role: <strong style="color: #4f46e5; text-transform: uppercase;"><?= htmlspecialchars($user['role']); ?></strong></p>
                <p style="margin-bottom: 0.5rem; color: #64748b;">Orders Placed: <strong style="color: #0f172a;"><?= $orderCount; ?></strong></p>
                <p style="margin-bottom: 0; color: #64748b;">Joined: <strong style="color: #0f172a;"><?= date('M Y', strtotime($user['created_at'])); ?></strong></p>
            </div>
        </div>

        <!-- Right: Edit Details Form -->
        <div style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 2rem;">
            <form action="<?= BASE_URL; ?>/profile.php" method="POST">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">Personal Information</h3>

                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" required class="form-control" value="<?= htmlspecialchars($user['name']); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address (Read-Only)</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" disabled style="background: #f8fafc; color: #94a3b8;">
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>

                <h3 style="font-size: 1.15rem; font-weight: 800; margin: 2rem 0 1.25rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">Default Shipping Address</h3>

                <div class="form-group">
                    <label class="form-label">Street Address</label>
                    <textarea name="address" rows="2" class="form-control"><?= htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($user['city'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($user['state'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pincode</label>
                        <input type="text" name="pincode" class="form-control" value="<?= htmlspecialchars($user['pincode'] ?? ''); ?>">
                    </div>
                </div>

                <h3 style="font-size: 1.15rem; font-weight: 800; margin: 2rem 0 1.25rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">Change Password (Optional)</h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" placeholder="Leave blank to keep same">
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="margin-top: 1rem;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Profile Changes
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
