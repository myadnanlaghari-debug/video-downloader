<?php
/**
 * Database Configuration
 * Crypto Investment Platform
 * 
 * IMPORTANT: Update these values with your hosting database credentials
 * For shared hosting, database names usually include your cPanel username prefix
 * Example: u577740496_crypto_invest
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'u577740496_crypto_invest');
define('DB_USER', 'u577740496_crypto_user');
define('DB_PASS', 'Adnan7272!');
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
