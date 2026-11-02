<?php
/**
 * User & Admin Authentication Page
 * Project: Online Bookstore
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: ' . BASE_URL . '/admin/index.php');
    } else {
        header('Location: ' . BASE_URL . '/index.php');
    }
    exit;
}

$redirect = isset($_GET['redirect']) ? sanitize($_GET['redirect']) : '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) $errors[] = 'Email address is required.';
    if (empty($password)) $errors[] = 'Password is required.';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && verifyUserPassword($password, $user['password'])) {
                // Initialize Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                setFlash('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');

                if ($user['role'] === 'admin') {
                    header('Location: ' . BASE_URL . '/admin/index.php');
                } elseif (!empty($redirect)) {
                    header('Location: ' . BASE_URL . '/' . ltrim($redirect, '/'));
                } else {
                    header('Location: ' . BASE_URL . '/index.php');
                }
                exit;
            } else {
                $errors[] = 'Invalid email or password combination.';
            }
        } catch (Exception $e) {
            $errors[] = 'An error occurred during authentication: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Sign In to Your Account';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container section">
    <div class="auth-wrapper">
        <div class="auth-header">
            <h1>Welcome Back</h1>
            <p>Sign in to access your orders, cart, and profile</p>
        </div>

        <!-- College Mini Project Demo Credentials Quick Fill Box -->
        <div class="demo-accounts-pill">
            <div style="font-weight: 700; color: #4338ca; margin-bottom: 6px;">
                <i class="fa-solid fa-graduation-cap"></i> Demo Accounts (1-Click Fill):
            </div>
            <div style="display: flex; gap: 8px; margin-bottom: 4px;">
                <button type="button" class="btn btn-sm btn-secondary" onclick="fillDemoAccount('admin')" style="font-size: 0.75rem; padding: 4px 8px;">
                    <i class="fa-solid fa-user-shield"></i> Fill Admin (admin@bookstore.com)
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="fillDemoAccount('user')" style="font-size: 0.75rem; padding: 4px 8px;">
                    <i class="fa-solid fa-user"></i> Fill User (john@example.com)
                </button>
            </div>
            <div style="font-size: 0.75rem; color: #64748b;">
                Admin: <code>admin@bookstore.com / admin123</code> | User: <code>john@example.com / user123</code>
            </div>
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

        <form action="<?= BASE_URL; ?>/login.php<?= !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>" method="POST">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" required class="form-control" placeholder="name@example.com" value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <label class="form-label" style="margin: 0;">Password</label>
                    <span style="font-size: 0.8rem; color: #64748b;">Hint: admin123 / user123</span>
                </div>
                <input type="password" name="password" required class="form-control" placeholder="Enter your password">
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 1.5rem;">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.75rem; font-size: 0.9rem; color: #64748b;">
            Don't have an account yet? 
            <a href="<?= BASE_URL; ?>/register.php" style="color: #4f46e5; font-weight: 700;">Create Account</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
