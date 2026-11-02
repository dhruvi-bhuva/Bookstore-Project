<?php
/**
 * Global Footer Template
 * Project: Online Bookstore
 */

// Fetch top categories for footer links
try {
    $footerCatStmt = $pdo->query("SELECT name, slug FROM categories ORDER BY id ASC LIMIT 5");
    $footerCategories = $footerCatStmt->fetchAll();
} catch (Exception $e) {
    $footerCategories = [];
}
?>
    <!-- Features Highlight Bar -->
    <section class="features-bar">
        <div class="container">
            <div class="features-grid">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fa-solid fa-truck-fast"></i></div>
                    <div class="feature-info">
                        <h4>Free Express Delivery</h4>
                        <p>On all prepaid orders over ₹500</p>
                    </div>
                </div>
                <div class="feature-box">
                    <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <div class="feature-info">
                        <h4>100% Genuine Books</h4>
                        <p>Direct from verified publishers</p>
                    </div>
                </div>
                <div class="feature-box">
                    <div class="feature-icon"><i class="fa-solid fa-rotate-left"></i></div>
                    <div class="feature-info">
                        <h4>7-Day Easy Returns</h4>
                        <p>Hassle-free replacement policy</p>
                    </div>
                </div>
                <div class="feature-box">
                    <div class="feature-icon"><i class="fa-solid fa-headset"></i></div>
                    <div class="feature-info">
                        <h4>24/7 Reader Support</h4>
                        <p>Dedicated student helpdesk</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Site Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Col 1: Brand & Project info -->
                <div class="footer-col">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                        <div class="brand-icon" style="width: 32px; height: 32px; font-size: 1rem;"><i class="fa-solid fa-book-bookmark"></i></div>
                        <h3 style="color: #ffffff; font-size: 1.35rem; font-weight: 800;">Book<span style="color: #818cf8;">Verse</span></h3>
                    </div>
                    <p style="font-size: 0.9rem; line-height: 1.6; margin-bottom: 1.25rem;">
                        Your premier online destination for literature, computer science textbooks, bestselling fiction, and personal growth gems.
                    </p>
                    <div class="college-credit-badge">
                        <i class="fa-solid fa-graduation-cap"></i> Designed for College Mini Project
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL; ?>/index.php">Home</a></li>
                        <li><a href="<?= BASE_URL; ?>/books.php">Explore All Books</a></li>
                        <li><a href="<?= BASE_URL; ?>/about.php">About This Project</a></li>
                        <li><a href="<?= BASE_URL; ?>/contact.php">Contact & Support</a></li>
                        <li><a href="<?= BASE_URL; ?>/cart.php">Shopping Cart</a></li>
                    </ul>
                </div>

                <!-- Col 3: Categories -->
                <div class="footer-col">
                    <h4>Top Categories</h4>
                    <ul class="footer-links">
                        <?php foreach ($footerCategories as $fCat): ?>
                            <li><a href="<?= BASE_URL; ?>/books.php?category=<?= urlencode($fCat['slug']); ?>"><?= htmlspecialchars($fCat['name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Col 4: Newsletter & Contact -->
                <div class="footer-col">
                    <h4>Stay Updated</h4>
                    <p style="font-size: 0.85rem; margin-bottom: 1rem;">
                        Subscribe to get updates on new book arrivals, author interviews, and exclusive discounts.
                    </p>
                    <form onsubmit="event.preventDefault(); alert('Thank you for subscribing to BookVerse newsletter!');" style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                        <input type="email" required placeholder="Enter email..." style="padding: 0.5rem 0.8rem; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #fff; font-size: 0.85rem; width: 100%;">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-paper-plane"></i></button>
                    </form>
                    <div style="display: flex; gap: 0.75rem;">
                        <a href="#" class="icon-btn" style="background: #1e293b; color: #cbd5e1;"><i class="fa-brands fa-github"></i></a>
                        <a href="#" class="icon-btn" style="background: #1e293b; color: #cbd5e1;"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="#" class="icon-btn" style="background: #1e293b; color: #cbd5e1;"><i class="fa-brands fa-twitter"></i></a>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright Bar -->
            <div class="footer-bottom">
                <div>
                    &copy; <?= date('Y'); ?> <strong>BookVerse</strong>. All rights reserved. Built with PHP &amp; MySQL.
                </div>
                <div style="display: flex; gap: 1.5rem;">
                    <span>Created for Computer Science Department</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Core Scripts -->
    <script src="<?= BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
