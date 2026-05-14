<?php
/**
 * Database Configuration
 * Crypto Investment Platform
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'crypto_investment');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'Crypto Invest Pro');
define('SITE_URL', 'http://localhost/crypto-investment');
define('ADMIN_EMAIL', 'admin@cryptoinvest.com');

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png']);

// Security Settings
define('SESSION_LIFETIME', 3600);
define('PASSWORD_MIN_LENGTH', 6);

// Referral Settings
define('DEPOSIT_REFERRAL_COMMISSION', 5); // 5%
define('INVESTMENT_REFERRAL_COMMISSION', 2); // 2%

?>
