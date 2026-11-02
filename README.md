# 📚 BookVerse - Online Bookstore Website
### College Mini Project / Final Semester Web Technologies Project

A modern, responsive, and database-driven **Online Bookstore Web Application** built with **HTML5, CSS3, Vanilla JavaScript (ES6+), PHP (PDO), and MySQL**.

---

## 🌟 Key Features

### 🛍️ User Storefront Features
- **Modern Responsive Home Page**:
  - Interactive hero banner with glassmorphism styling and call-to-actions.
  - Browse by genres/categories with dynamic book count badges.
  - Editor's handpicked Featured Books carousel/grid.
  - Real-time Bestselling books display.
  - Promotional discount banner for student textbooks.
  - Customer review cards and newsletter subscription section.
- **Dynamic Book Catalog (`books.php`)**:
  - Live search by book title, author, or ISBN.
  - Category / Genre filtering with active state highlighting.
  - Filter by Collections: *Featured* or *Bestsellers*.
  - Price filter (Filter books by maximum budget).
  - Sorting: *Featured*, *Newest First*, *Price: Low to High*, *Price: High to Low*, and *Highest Rated*.
- **Comprehensive Book Details Page (`book-details.php`)**:
  - High-resolution book cover display.
  - Discount percentage badge & savings indicator.
  - Star ratings & reader reviews count.
  - Full synopsis and overview.
  - Detailed book specifications table: ISBN-13, Publisher, Language, Total Pages, Publication Year.
  - Quantity selector with dynamic stock validation.
  - Interactive reader review submission form.
  - "You Might Also Like" related books recommendation carousel.
- **Shopping Cart System (`cart.php`)**:
  - Add to cart with custom quantity.
  - Increment/decrement items directly in cart.
  - Real-time subtotal and free shipping progress tracker (Free delivery over ₹500).
  - Remove individual books or clear entire shopping cart.
  - Sticky order summary card.
- **Wishlist System (`wishlist.php`)**:
  - Save books for later with 1-click heart toggle.
  - Move items directly from Wishlist into the Cart.
- **User Authentication & Profile (`login.php`, `register.php`, `profile.php`)**:
  - Secure password hashing using PHP `password_hash()` and `password_verify()`.
  - Convenient 1-Click Demo Account filler buttons for quick viva presentation.
  - User profile to update personal details, phone number, and default delivery address.
  - Change password functionality.
- **Checkout & Order Placement (`checkout.php`, `order-success.php`)**:
  - Pre-fills saved customer address.
  - Multiple payment options: Cash on Delivery (COD), Card Payment, UPI / Net Banking (Google Pay / PhonePe).
  - ACID-compliant MySQL transaction ensuring stock is automatically reduced when orders are placed.
  - Printable receipt/invoice with order tracking ID (`ORD-YYYY-XXXX`).
- **Order History (`orders.php`)**:
  - Real-time status badges: *Pending*, *Processing*, *Shipped*, *Delivered*, *Cancelled*.
  - Order breakdown and past receipts.
- **Contact Us & Support (`contact.php`)**:
  - Campus bookstore address, direct contact email, customer hotline.
  - Functional contact form storing inquiries in the MySQL database.
  - Frequently Asked Questions (FAQ) accordion.

---

### 🛡️ Admin Dashboard Features (`admin/`)
- **Dashboard Overview (`admin/index.php`)**:
  - KPI metric cards: Total Sales Revenue, Total Orders Placed, Books in Library, Registered Customers.
  - Recent orders preview table with instant management link.
  - Stock Watchlist (Alerts admin when book inventory drops below 18 units).
- **Manage Books (`admin/books.php`)**:
  - List all books with cover thumbnail, category, author, price, discount, and stock.
  - Real-time search by title, author, or ISBN.
  - **Add Book (`admin/book-add.php`)**: Image file upload or URL, category dropdown, pricing, stock, description.
  - **Edit Book (`admin/book-edit.php`)**: Update existing titles, prices, stock, or replace cover photos.
  - **Delete Book**: Instant deletion with JavaScript confirmation prompt.
- **Manage Categories (`admin/categories.php`)**:
  - Add new genre/category with custom FontAwesome icon.
  - Automatic URL slug generator.
  - Real-time count of books attached to each category.
  - Delete categories safely.
- **Manage Orders (`admin/orders.php`, `admin/order-detail.php`)**:
  - View all orders with customer contact and address.
  - Filter orders by status: *Pending*, *Processing*, *Shipped*, *Delivered*, *Cancelled*.
  - Update fulfillment and payment status with 1-click select dropdown.
  - View and print full order invoices.
- **Customer Messages (`admin/messages.php`)**:
  - Read user inquiries submitted from Contact Us form.
  - Mark messages as read / unread or delete.
- **Registered Customers (`admin/users.php`)**:
  - View list of all registered student/reader accounts and total orders made.

---

## 💻 Tech Stack

| Component | Technology |
|---|---|
| **Frontend** | HTML5 Semantic Elements, Modern CSS3 (Flexbox & CSS Grid, Variables), JavaScript (ES6+) |
| **Backend** | PHP 7.4+ / PHP 8.x (Modular Architecture, Object-Oriented PDO) |
| **Database** | MySQL / MariaDB (Prepared Statements, Foreign Keys, Transactions) |
| **Icons & Fonts** | FontAwesome 6, Google Fonts (*Plus Jakarta Sans* & *Playfair Display*) |

---

## 🚀 Setup Instructions (Using XAMPP)

### Step 1: Place the Project in XAMPP
Copy the entire `bookstore` project directory into your XAMPP `htdocs` folder:
```
C:\xampp\htdocs\bookstore
```

### Step 2: Start Apache and MySQL
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

### Step 3: Import the Database
1. Open your browser and navigate to:  
   👉 `http://localhost/phpmyadmin`
2. Click on the **Import** tab in the top navigation bar.
3. Click **Choose File** and select `database.sql` located inside your `bookstore` directory:
   ```
   C:\xampp\htdocs\bookstore\database.sql
   ```
4. Scroll down and click the **Import** / **Go** button.
5. The database `bookstore_db` will be created with all 8 tables and sample seed data.

### Step 4: Verify Database Connection (If Needed)
The database connection settings are located in `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');       // Default is empty in XAMPP
define('DB_NAME', 'bookstore_db');
define('DB_PORT', 3306);
```

### Step 5: Launch the Website!
Open your browser and navigate to:  
👉 **`http://localhost/bookstore`**

---

## 🔑 Default Login Credentials

Use the convenient **1-Click Demo Buttons** on `login.php` or enter manually:

| Account Type | Email | Password | Role / Access |
|---|---|---|---|
| **Administrator** | `admin@bookstore.com` | `admin123` | Full Admin Dashboard Access (`/admin`) |
| **Customer 1** | `john@example.com` | `user123` | User Storefront, Cart, Orders, Wishlist |
| **Customer 2** | `jane@example.com` | `user123` | User Storefront, Cart, Orders, Wishlist |

---

## 🗄️ Database Architecture (Schema)

1. **`users`**: Customer and Admin accounts, hashed passwords, address, phone.
2. **`categories`**: Book genres (Fiction, Technology, Self-Help, Business, Sci-Fi, Mystery).
3. **`books`**: Title, author, ISBN, prices, stock, ratings, featured/bestseller flags.
4. **`orders`**: Customer shipping details, subtotal, shipping fee, total amount, status.
5. **`order_items`**: Line items per order linked to books and orders.
6. **`wishlist`**: Saved books for logged-in users.
7. **`contact_messages`**: Inquiries sent through Contact Us page.
8. **`reviews`**: Ratings and feedback per book.

---

## 🎓 College Mini Project Evaluation Highlights
- **Security**: Utilizes **PDO Prepared Statements** for all SQL queries to prevent SQL Injection attacks. Uses PHP `password_hash()` for encryption and `htmlspecialchars()` to prevent Cross-Site Scripting (XSS).
- **ACID Transactions**: Order placement uses `$pdo->beginTransaction()` and `$pdo->commit()` ensuring order and order items are saved atomically alongside book inventory reduction.
- **Separation of Concerns**: Modular `header.php`, `footer.php`, `config/`, and `includes/` ensures DRY (Don't Repeat Yourself) principle.
- **Mobile Responsive**: Fully responsive across mobile, tablet, and desktop screens with toggleable mobile navigation drawer.
