<?php
/**
 * Database Connection Configuration
 * Project: Online Bookstore (College Mini Project)
 */

// Database credentials - change if needed for your environment
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bookstore_db');
define('DB_PORT', 3306);

// Base application URL (Dynamically auto-detected for XAMPP, WAMP, or PHP CLI Server)
if (!defined('BASE_URL')) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    $protocol = $isHttps ? 'https://' : 'http://';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    
    // Auto-detect base folder path from SCRIPT_NAME
    $scriptName = str_replace('\\', '/', isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '');
    $dir = dirname($scriptName);
    
    // If request is from admin/, includes/, or config/ subfolders, traverse up
    $baseName = basename($dir);
    if ($baseName === 'admin' || $baseName === 'includes' || $baseName === 'config') {
        $dir = dirname($dir);
    }
    
    $subFolder = ($dir === '/' || $dir === '\\' || $dir === '.') ? '' : rtrim($dir, '/');
    define('BASE_URL', $protocol . $host . $subFolder);
}

// Establish PDO connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Graceful helpful message if database has not been imported yet
    $errorMsg = $e->getMessage();
    $dbMissing = strpos($errorMsg, 'Unknown database') !== false;
    
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Setup Required - BookVerse</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #1e293b; padding: 40px 20px; line-height: 1.6; }
            .container { max-width: 680px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 36px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; }
            h1 { color: #dc2626; font-size: 24px; margin-top: 0; display: flex; align-items: center; gap: 10px; }
            .badge { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 20px; font-size: 13px; font-weight: 600; }
            .steps { background: #f1f5f9; border-radius: 10px; padding: 20px; margin: 20px 0; }
            .steps ol { margin: 0; padding-left: 20px; }
            .steps li { margin-bottom: 8px; }
            code { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 13px; color: #0f172a; }
            .retry-btn { display: inline-block; background: #4f46e5; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; margin-top: 15px; }
            .err-box { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px; font-family: monospace; font-size: 12px; margin-top: 15px; word-break: break-all; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1><span>⚠️</span> Database Connection Error</h1>
            <p>Could not connect to the MySQL database <code>' . htmlspecialchars(DB_NAME) . '</code>.</p>';
            
    if ($dbMissing) {
        echo '<p><strong>The database does not exist yet. Please follow these quick steps:</strong></p>
        <div class="steps">
            <ol>
                <li>Open <strong>phpMyAdmin</strong> (usually at <code>http://localhost/phpmyadmin</code>) or your MySQL CLI.</li>
                <li>Click the <strong>Import</strong> tab at the top.</li>
                <li>Choose the file <code>database.sql</code> from your <code>bookstore</code> project folder.</li>
                <li>Click <strong>Go / Import</strong> at the bottom.</li>
            </ol>
        </div>';
    } else {
        echo '<p>Ensure your MySQL service is running (e.g., in XAMPP / WAMP Control Panel) and verify credentials in <code>config/db.php</code>.</p>';
    }
    
    echo '<div class="err-box">Error details: ' . htmlspecialchars($errorMsg) . '</div>
        <p><a href="" class="retry-btn">🔄 Retry Connection</a></p>
    </div>
    </body>
    </html>';
    exit;
}
