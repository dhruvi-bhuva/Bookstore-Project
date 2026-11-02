-- ========================================================
-- Online Bookstore Project Database Schema
-- College Mini Project / Final Project
-- Compatible with MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+
-- ========================================================

CREATE DATABASE IF NOT EXISTS `bookstore_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bookstore_db`;

-- --------------------------------------------------------
-- 1. Table structure for `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `pincode` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Table structure for `categories`
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` VARCHAR(255) DEFAULT NULL,
  `icon` VARCHAR(50) DEFAULT 'fa-book',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. Table structure for `books`
-- --------------------------------------------------------
CREATE TABLE `books` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `author` VARCHAR(150) NOT NULL,
  `isbn` VARCHAR(30) DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `discount_price` DECIMAL(10,2) DEFAULT NULL,
  `description` TEXT NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `stock` INT NOT NULL DEFAULT 15,
  `pages` INT DEFAULT 320,
  `publisher` VARCHAR(150) DEFAULT 'Penguin Random House',
  `language` VARCHAR(50) DEFAULT 'English',
  `publication_year` INT DEFAULT 2022,
  `rating` DECIMAL(2,1) DEFAULT 4.5,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_bestseller` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_books_categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 4. Table structure for `orders`
-- --------------------------------------------------------
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL,
  `pincode` VARCHAR(20) NOT NULL,
  `payment_method` ENUM('cod', 'card', 'upi') NOT NULL DEFAULT 'cod',
  `payment_status` ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
  `subtotal` DECIMAL(10,2) NOT NULL,
  `shipping_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `order_status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_orders_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. Table structure for `order_items`
-- --------------------------------------------------------
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `book_id` INT DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `total_price` DECIMAL(10,2) NOT NULL,
  CONSTRAINT `fk_order_items_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_order_items_books` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 6. Table structure for `wishlist`
-- --------------------------------------------------------
CREATE TABLE `wishlist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `book_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_book_pair` (`user_id`, `book_id`),
  CONSTRAINT `fk_wishlist_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_wishlist_books` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 7. Table structure for `contact_messages`
-- --------------------------------------------------------
CREATE TABLE `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('unread', 'read', 'replied') NOT NULL DEFAULT 'unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 8. Table structure for `reviews`
-- --------------------------------------------------------
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `book_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `rating` INT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `review_text` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reviews_books` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reviews_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- SEED DATA
-- Default Passwords:
-- Admin: admin@bookstore.com / admin123
-- User:  john@example.com   / user123
-- User:  jane@example.com   / user123
-- ========================================================

-- Users
-- Password hash for 'admin123' is $2y$10$O0Fq/pL9I.xHnNkW6bUfQ.g9mJj0c2l1w3Y0Gv8Uo3FhDk1z7N.y6 (or fallback support)
-- We insert standard bcrypt hashes generated via password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `city`, `state`, `pincode`, `role`) VALUES
(1, 'Admin Manager', 'admin@bookstore.com', '$2y$10$O6v4zVqE5oYm01R7W320e.8s7o2R9j3vI9Z9Pj1.j9.sD1kKkO2yG', '9876543210', '101 Admin Towers, Tech City', 'Bangalore', 'Karnataka', '560001', 'admin'),
(2, 'John Doe', 'john@example.com', '$2y$10$Y1sLdGzP8s0rY1q3Y2.fNuH5s7e0k9d8j3uH0k7e1j9f4k2e9w8uS', '9876543211', '42 Maple Street, Sector 14', 'Mumbai', 'Maharashtra', '400001', 'user'),
(3, 'Jane Smith', 'jane@example.com', '$2y$10$Y1sLdGzP8s0rY1q3Y2.fNuH5s7e0k9d8j3uH0k7e1j9f4k2e9w8uS', '9876543212', '77 Riverview Apartments', 'New Delhi', 'Delhi', '110001', 'user');

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`) VALUES
(1, 'Fiction & Literature', 'fiction', 'Novels, literary classics, and storytelling treasures.', 'fa-book-open'),
(2, 'Computer Science & Tech', 'technology', 'Programming, software architecture, data science & AI.', 'fa-laptop-code'),
(3, 'Self-Help & Mindset', 'self-help', 'Personal development, psychology, productivity, and habits.', 'fa-brain'),
(4, 'Business & Finance', 'business', 'Investing, wealth building, startups, and leadership.', 'fa-chart-line'),
(5, 'Sci-Fi & Fantasy', 'sci-fi', 'Futuristic worlds, epic sagas, and galactic adventures.', 'fa-rocket'),
(6, 'Mystery & Thriller', 'thriller', 'Crime fiction, edge-of-seat suspense, and investigations.', 'fa-mask');

-- Books
INSERT INTO `books` (`id`, `category_id`, `title`, `author`, `isbn`, `price`, `discount_price`, `description`, `image`, `stock`, `pages`, `publisher`, `language`, `publication_year`, `rating`, `is_featured`, `is_bestseller`) VALUES
(1, 2, 'Clean Code: A Handbook of Agile Software Craftsmanship', 'Robert C. Martin', '978-0132350884', 699.00, 549.00, 'Even bad code can function. But if code isn''t clean, it can bring a development organization to its knees. Every year, countless hours and significant resources are lost because of poorly written code. Clean Code is divided into three parts: principles of writing clean code, case studies of increasing complexity, and a list of heuristics and smells.', 'assets/images/books/clean-code.jpg', 25, 464, 'Prentice Hall', 'English', 2008, 4.8, 1, 1),
(2, 3, 'Atomic Habits: An Easy & Proven Way to Build Good Habits', 'James Clear', '978-0735211292', 499.00, 399.00, 'No matter your goals, Atomic Habits offers a proven framework for improving every day. James Clear, one of the world''s leading experts on habit formation, reveals practical strategies that teach you exactly how to form good habits, break bad ones, and master tiny behaviors that lead to remarkable results.', 'assets/images/books/atomic-habits.jpg', 40, 320, 'Avery', 'English', 2018, 4.9, 1, 1),
(3, 4, 'The Psychology of Money: Timeless Lessons on Wealth', 'Morgan Housel', '978-0857197689', 399.00, 319.00, 'Doing well with money isn''t necessarily about what you know. It''s about how you behave. And behavior is hard to teach, even to really smart people. In The Psychology of Money, award-winning author Morgan Housel shares 19 short stories exploring the strange ways people think about money.', 'assets/images/books/psychology-of-money.jpg', 30, 256, 'Harriman House', 'English', 2020, 4.7, 1, 1),
(4, 2, 'The Pragmatic Programmer: Your Journey To Mastery', 'David Thomas & Andrew Hunt', '978-0135957059', 750.00, 620.00, 'The Pragmatic Programmer is one of the most significant books in software development. Filled with classic and fresh insights, it examines the core of modern development: personal responsibility, career development, and architectural modularity.', 'assets/images/books/pragmatic-programmer.jpg', 18, 352, 'Addison-Wesley', 'English', 2019, 4.9, 1, 0),
(5, 5, 'Dune: Deluxe Collector Edition', 'Frank Herbert', '978-0441013593', 899.00, 720.00, 'Set on the desert planet Arrakis, Dune is the story of the boy Paul Atreides, heir to a noble family tasked with ruling an inhospitable world where the only thing of value is the spice melange. A triumph of the imagination and one of the bestselling science fiction novels of all time.', 'assets/images/books/dune.jpg', 20, 688, 'Ace Books', 'English', 2019, 4.6, 1, 1),
(6, 1, 'The Great Gatsby', 'F. Scott Fitzgerald', '978-0743273565', 299.00, 199.00, 'The Great Gatsby, F. Scott Fitzgerald''s third book, stands as the supreme achievement of his career. First published in 1925, this quintessential novel of the Jazz Age has been acclaimed by generations of readers.', 'assets/images/books/great-gatsby.jpg', 50, 180, 'Scribner', 'English', 2004, 4.4, 0, 1),
(7, 2, 'Designing Data-Intensive Applications', 'Martin Kleppmann', '978-1449373320', 999.00, 850.00, 'Data is at the center of many challenges in system design today. Difficult issues need to be figured out, such as scalability, consistency, reliability, efficiency, and maintainability. This book helps you navigate this diverse landscape.', 'assets/images/books/designing-data.jpg', 12, 616, 'O''Reilly Media', 'English', 2017, 4.9, 1, 0),
(8, 4, 'Rich Dad Poor Dad: What The Rich Teach Their Kids About Money', 'Robert T. Kiyosaki', '978-1612680194', 450.00, 360.00, 'Rich Dad Poor Dad explodes the myth that you need to earn a high income to be rich and explains the difference between working for money and having your money work for you. It continues to be a worldwide financial education phenomenon.', 'assets/images/books/rich-dad.jpg', 35, 336, 'Plata Publishing', 'English', 2017, 4.6, 0, 1),
(9, 6, 'The Silent Patient: A Thrilling Psychological Mystery', 'Alex Michaelides', '978-1250301696', 420.00, 340.00, 'Alicia Berenson''s life is seemingly perfect. A famous painter married to an in-demand fashion photographer, she lives in a grand house. One evening, she shoots her husband five times in the face and never speaks another word.', 'assets/images/books/silent-patient.jpg', 22, 336, 'Celadon Books', 'English', 2019, 4.5, 0, 1),
(10, 5, 'Project Hail Mary: A Novel', 'Andy Weir', '978-0593135204', 599.00, 480.00, 'Ryland Grace is the sole survivor on a desperate, last-chance mission—and if he fails, humanity and the earth itself are doomed. Except right now, he doesn''t know that. He can''t even remember his own name. A lone astronaut must save the earth from disaster.', 'assets/images/books/project-hail-mary.jpg', 15, 496, 'Ballantine Books', 'English', 2021, 4.8, 1, 0),
(11, 3, 'Deep Work: Rules for Focused Success in a Distracted World', 'Cal Newport', '978-1455586691', 499.00, 399.00, 'Deep work is the ability to focus without distraction on a cognitively demanding task. It''s a skill that allows you to quickly master complicated information and produce better results in less time. Deep work will make you better at what you do.', 'assets/images/books/deep-work.jpg', 28, 304, 'Grand Central Publishing', 'English', 2016, 4.7, 0, 0),
(12, 1, 'To Kill a Mockingbird', 'Harper Lee', '978-0060935467', 350.00, 275.00, 'One of the best-loved stories of all time, To Kill a Mockingbird has earned a phenomenal reputation since winning the Pulitzer Prize in 1961. The unforgettable novel of a childhood in a sleepy Southern town and the crisis of conscience that rocked it.', 'assets/images/books/mockingbird.jpg', 19, 336, 'Harper Perennial', 'English', 2002, 4.8, 0, 0);

-- Reviews
INSERT INTO `reviews` (`book_id`, `user_id`, `rating`, `review_text`) VALUES
(1, 2, 5, 'An absolute bible for software developers. Every programmer must read this!'),
(2, 2, 5, 'Changed my daily routine completely. Tiny changes really do compound into big results.'),
(3, 3, 4, 'Very insightful perspective on how human psychology dictates financial decisions.'),
(5, 3, 5, 'One of the greatest sci-fi masterpieces ever written. Brilliant world building!');

-- Orders
INSERT INTO `orders` (`id`, `user_id`, `order_number`, `full_name`, `email`, `phone`, `address`, `city`, `state`, `pincode`, `payment_method`, `payment_status`, `subtotal`, `shipping_fee`, `total_amount`, `order_status`, `notes`) VALUES
(1, 2, 'ORD-2026-1001', 'John Doe', 'john@example.com', '9876543211', '42 Maple Street, Sector 14', 'Mumbai', 'Maharashtra', '400001', 'card', 'paid', 948.00, 0.00, 948.00, 'delivered', 'Please deliver after 5 PM.'),
(2, 3, 'ORD-2026-1002', 'Jane Smith', 'jane@example.com', '9876543212', '77 Riverview Apartments', 'New Delhi', 'Delhi', '110001', 'cod', 'pending', 720.00, 40.00, 760.00, 'shipped', 'Ring the doorbell twice.');

-- Order Items
INSERT INTO `order_items` (`order_id`, `book_id`, `title`, `price`, `quantity`, `total_price`) VALUES
(1, 1, 'Clean Code: A Handbook of Agile Software Craftsmanship', 549.00, 1, 549.00),
(1, 2, 'Atomic Habits: An Easy & Proven Way to Build Good Habits', 399.00, 1, 399.00),
(2, 5, 'Dune: Deluxe Collector Edition', 720.00, 1, 720.00);

-- Contact Messages
INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`) VALUES
(1, 'Alex Wilson', 'alex@example.com', 'Book availability question', 'Hello, do you plan to stock more copies of Designing Data-Intensive Applications anytime soon?', 'unread'),
(2, 'Sarah Jenkins', 'sarah@example.com', 'Campus delivery inquiry', 'Do you provide bulk discounts for university library orders?', 'read');
