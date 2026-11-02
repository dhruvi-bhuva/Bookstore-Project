<?php
/**
 * Contact Us & FAQs Page
 * Project: Online Bookstore
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

$pageTitle = 'Contact Us & Reader Support';
$activePage = 'contact';

$errors = [];

// Handle Contact Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name)) $errors[] = 'Your name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (empty($subject)) $errors[] = 'Subject cannot be empty.';
    if (empty($message)) $errors[] = 'Message content is required.';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'unread')");
            $stmt->execute([$name, $email, $subject, $message]);
            setFlash('success', 'Thank you! Your message has been received. Our team will get back to you shortly.');
            header('Location: ' . BASE_URL . '/contact.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Failed to send message: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container section">
    <div style="text-align: center; max-width: 600px; margin: 0 auto 3.5rem;">
        <span class="badge-tag badge-category" style="margin-bottom: 0.5rem; display: inline-block;">Get In Touch</span>
        <h1 style="font-size: 2.35rem; font-weight: 800; margin-bottom: 0.75rem;">We’d Love to Hear From You</h1>
        <p style="color: #64748b; font-size: 1.05rem;">
            Have questions about a book, bulk student orders, or library delivery? Send us a message and we will respond promptly.
        </p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="max-width: 800px; margin: 0 auto 2rem;">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="contact-layout">
        <!-- Contact Information Cards -->
        <div class="contact-cards">
            <div class="contact-card">
                <div class="contact-icon"><i class="fa-solid fa-location-dot"></i></div>
                <div>
                    <h4 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.25rem;">Our Campus Bookstore</h4>
                    <p style="color: #64748b; font-size: 0.9rem;">
                        Block C, Computer Science Department,<br>
                        Engineering College Campus, Bangalore - 560001
                    </p>
                </div>
            </div>

            <div class="contact-card">
                <div class="contact-icon"><i class="fa-solid fa-envelope"></i></div>
                <div>
                    <h4 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.25rem;">Direct Email Support</h4>
                    <p style="color: #64748b; font-size: 0.9rem;">
                        support@bookverse.local<br>
                        orders@bookverse.local
                    </p>
                </div>
            </div>

            <div class="contact-card">
                <div class="contact-icon"><i class="fa-solid fa-phone"></i></div>
                <div>
                    <h4 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.25rem;">Customer Hotline</h4>
                    <p style="color: #64748b; font-size: 0.9rem;">
                        +91 98765 43210<br>
                        Mon – Sat: 9:00 AM – 7:00 PM
                    </p>
                </div>
            </div>

            <div class="contact-card" style="background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-color: #c7d2fe;">
                <div class="contact-icon" style="background: #4f46e5; color: #ffffff;"><i class="fa-solid fa-graduation-cap"></i></div>
                <div>
                    <h4 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.25rem; color: #312e81;">College Project Demo</h4>
                    <p style="color: #4338ca; font-size: 0.85rem;">
                        Submitted as a Web Technologies Mini Project. Demonstrates full CRUD operations, PDO prepared statements, and responsive UI.
                    </p>
                </div>
            </div>
        </div>

        <!-- Working Contact Form -->
        <div style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 2.5rem; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 1.5rem;">Send Us an Inquiry</h3>

            <form action="<?= BASE_URL; ?>/contact.php" method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Your Name *</label>
                        <input type="text" name="name" required class="form-control" placeholder="e.g. Priya Sharma" value="<?= htmlspecialchars($_POST['name'] ?? ($_SESSION['user_name'] ?? '')); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" required class="form-control" placeholder="e.g. priya@example.com" value="<?= htmlspecialchars($_POST['email'] ?? ($_SESSION['user_email'] ?? '')); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Subject *</label>
                    <input type="text" name="subject" required class="form-control" placeholder="e.g. Order Tracking or Book Recommendation" value="<?= htmlspecialchars($_POST['subject'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Your Message *</label>
                    <textarea name="message" rows="5" required class="form-control" placeholder="Write your questions or comments here..."><?= htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
    </div>

    <!-- Frequently Asked Questions Accordion -->
    <div style="margin-top: 4rem; max-width: 800px; margin-left: auto; margin-right: auto;">
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.85rem; font-weight: 800;">Frequently Asked Questions</h2>
            <p style="color: #64748b;">Quick answers to common questions about orders, payments &amp; delivery</p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <details style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 1.25rem; cursor: pointer;">
                <summary style="font-weight: 700; color: #0f172a; font-size: 1rem;">How long does delivery take?</summary>
                <p style="margin-top: 0.75rem; color: #475569; font-size: 0.925rem; line-height: 1.6;">
                    Standard delivery typically takes 2 to 4 business days. Express delivery to campus addresses is usually completed within 24 to 48 hours.
                </p>
            </details>

            <details style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 1.25rem; cursor: pointer;">
                <summary style="font-weight: 700; color: #0f172a; font-size: 1rem;">What payment methods are supported?</summary>
                <p style="margin-top: 0.75rem; color: #475569; font-size: 0.925rem; line-height: 1.6;">
                    We support Cash on Delivery (COD), UPI (Google Pay, PhonePe, Paytm), and major Credit/Debit cards. In this demo project, all payment flows are simulated safely.
                </p>
            </details>

            <details style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 1.25rem; cursor: pointer;">
                <summary style="font-weight: 700; color: #0f172a; font-size: 1rem;">Can I cancel or modify an order after placing it?</summary>
                <p style="margin-top: 0.75rem; color: #475569; font-size: 0.925rem; line-height: 1.6;">
                    Yes, as long as your order status is 'Pending' or 'Processing', you can reach out through our contact form or contact hotline to request modifications or cancellation.
                </p>
            </details>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
