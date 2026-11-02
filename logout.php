<?php
/**
 * Logout Handler
 * Project: Online Bookstore
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

// Unset user session
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['user_email']);
unset($_SESSION['user_role']);

setFlash('info', 'You have been successfully logged out.');
header('Location: ' . BASE_URL . '/login.php');
exit;
