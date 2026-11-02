<?php
/**
 * About Project & Technology Stack Page
 * Project: Online Bookstore
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

$pageTitle = 'About BookVerse - College Mini Project';
$activePage = 'about';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container section">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 3.5rem;">
        <span class="badge-tag badge-category" style="margin-bottom: 0.5rem; display: inline-block;">Project Architecture</span>
        <h1 style="font-size: 2.35rem; font-weight: 800; margin-bottom: 0.75rem;">About BookVerse</h1>
        <p style="color: #64748b; font-size: 1.05rem;">
            A responsive, database-driven Web Application designed to solve modern e-commerce requirements for college mini-project evaluation.
        </p>
    </div>

    <!-- Tech Stack Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 4rem;">
        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 1.75rem; text-align: center;">
            <div style="font-size: 2.5rem; color: #e34f26; margin-bottom: 1rem;"><i class="fa-brands fa-html5"></i></div>
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">HTML5 &amp; Semantic Web</h3>
            <p style="color: #64748b; font-size: 0.85rem;">Structured semantic markup conforming to W3C standards with accessible cards, modals, and forms.</p>
        </div>

        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 1.75rem; text-align: center;">
            <div style="font-size: 2.5rem; color: #2965f1; margin-bottom: 1rem;"><i class="fa-brands fa-css3-alt"></i></div>
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">Modern CSS3</h3>
            <p style="color: #64748b; font-size: 0.85rem;">Custom CSS variables, Flexbox &amp; Grid layout, smooth hover micro-interactions, fully mobile-responsive.</p>
        </div>

        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 1.75rem; text-align: center;">
            <div style="font-size: 2.5rem; color: #777bb4; margin-bottom: 1rem;"><i class="fa-brands fa-php"></i></div>
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">PHP 7.4 / 8.x PDO</h3>
            <p style="color: #64748b; font-size: 0.85rem;">Secure prepared statements preventing SQL injections, modular includes, flash session messaging.</p>
        </div>

        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 1.75rem; text-align: center;">
            <div style="font-size: 2.5rem; color: #00758f; margin-bottom: 1rem;"><i class="fa-solid fa-database"></i></div>
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">MySQL Relational DB</h3>
            <p style="color: #64748b; font-size: 0.85rem;">Foreign keys with cascade rules, ACID transaction for order processing, optimized indexing.</p>
        </div>
    </div>

    <!-- Key Modules Overview -->
    <div style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 2.5rem; margin-bottom: 4rem;">
        <h2 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 1.5rem;">Project Core Modules</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            <div>
                <h4 style="color: #4f46e5; margin-bottom: 0.35rem;"><i class="fa-solid fa-cart-shopping"></i> Shopping Cart &amp; Wishlist</h4>
                <p style="color: #64748b; font-size: 0.9rem;">Real-time item counter in navbar, quantity update with validation, and seamless wishlist toggle.</p>
            </div>
            <div>
                <h4 style="color: #4f46e5; margin-bottom: 0.35rem;"><i class="fa-solid fa-credit-card"></i> Checkout &amp; Order Pipeline</h4>
                <p style="color: #64748b; font-size: 0.9rem;">Atomic multi-table insertion using PDO transactions, stock reduction, and printable invoice generator.</p>
            </div>
            <div>
                <h4 style="color: #4f46e5; margin-bottom: 0.35rem;"><i class="fa-solid fa-gauge-high"></i> Dedicated Admin Portal</h4>
                <p style="color: #64748b; font-size: 0.9rem;">Complete CRUD dashboard for catalog management, sales reporting, order fulfillment, and user logs.</p>
            </div>
            <div>
                <h4 style="color: #4f46e5; margin-bottom: 0.35rem;"><i class="fa-solid fa-shield-halved"></i> Role-Based Access Control</h4>
                <p style="color: #64748b; font-size: 0.9rem;">Segregated permissions for regular customers and administrative managers with bcrypt encryption.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
