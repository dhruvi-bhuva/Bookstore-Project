<?php
/**
 * User Registration Page
 * Project: Online Bookstore
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validations
    if (empty($name)) $errors[] = 'Full name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (empty($password) || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        try {
            // Check if email already exists
            $chk = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $chk->execute([$email]);
            if ($chk->fetch()) {
                $errors[] = 'An account with this email address already exists. Please sign in instead.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $ins = $pdo->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'user')");
                $ins->execute([$name, $email, $hashedPassword, $phone]);
                
                $newUserId = $pdo->lastInsertId();

                // Auto-login newly registered user
                $_SESSION['user_id'] = $newUserId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'user';

                setFlash('success', 'Your account has been created successfully! Welcome to BookVerse.');
                header('Location: ' . BASE_URL . '/index.php');
                exit;
            }
        } catch (Exception $e) {
            $errors[] = 'Registration failed: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Create a New Account';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container section">
    <div class="auth-wrapper">
        <div class="auth-header">
            <h1>Create Account</h1>
            <p>Join BookVerse to discover and order thousands of books</p>
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

        <form action="<?= BASE_URL; ?>/register.php" method="POST">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" required class="form-control" placeholder="e.g. Rahul Sharma" value="<?= htmlspecialchars($_POST['name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" required class="form-control" placeholder="e.g. rahul@example.com" value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Mobile Phone Number (Optional)</label>
                <input type="tel" name="phone" class="form-control" placeholder="e.g. 9876543210" value="<?= htmlspecialchars($_POST['phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" required class="form-control" placeholder="At least 6 characters">
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password *</label>
                <input type="password" name="confirm_password" required class="form-control" placeholder="Re-enter password">
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 1.5rem;">
                <i class="fa-solid fa-user-plus"></i> Create Account
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.75rem; font-size: 0.9rem; color: #64748b;">
            Already have an account? 
            <a href="<?= BASE_URL; ?>/login.php" style="color: #4f46e5; font-weight: 700;">Sign In</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
