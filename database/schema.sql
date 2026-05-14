-- ========================================
-- CRYPTO INVESTMENT PLATFORM - SQL SCHEMA
-- ========================================
-- Instructions:
-- 1. Create your database via cPanel first
-- 2. Select your database in phpMyAdmin  
-- 3. Import this SQL file
-- ========================================

-- Drop tables if they exist (to avoid duplicate errors)
DROP TABLE IF EXISTS `referrals`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `investments`;
DROP TABLE IF EXISTS `investment_plans`;
DROP TABLE IF EXISTS `withdrawals`;
DROP TABLE IF EXISTS `deposits`;
DROP TABLE IF EXISTS `wallets`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `users`;

-- ========================================
-- 1. USERS TABLE
-- ========================================
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `balance` DECIMAL(15,2) DEFAULT 0.00,
  `referral_code` VARCHAR(20) UNIQUE,
  `referred_by` INT(11) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'suspended', 'deleted') DEFAULT 'active',
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_referral_code` (`referral_code`),
  KEY `idx_referred_by` (`referred_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 2. ADMINS TABLE
-- ========================================
CREATE TABLE `admins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `role` ENUM('super_admin', 'admin', 'support') DEFAULT 'admin',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin (password: admin123)
INSERT INTO `admins` (`username`, `password`, `email`, `name`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@cryptoinvest.com', 'Super Admin', 'super_admin');

-- ========================================
-- 3. SETTINGS TABLE
-- ========================================
CREATE TABLE `settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT,
  `setting_type` ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('site_name', 'Crypto Invest Pro', 'string'),
('site_url', 'https://cryptoinvest.com', 'string'),
('logo_path', 'assets/images/logo.png', 'string'),
('favicon_path', 'assets/images/favicon.ico', 'string'),
('contact_email', 'support@cryptoinvest.com', 'string'),
('contact_phone', '+1 234 567 8900', 'string'),
('min_deposit', '10', 'number'),
('min_withdrawal', '20', 'number'),
('withdrawal_fee_percent', '2', 'number'),
('referral_deposit_bonus', '5', 'number'),
('referral_investment_bonus', '2', 'number'),
('maintenance_mode', '0', 'boolean'),
('auto_approve_deposits', '0', 'boolean'),
('currency_symbol', '$', 'string'),
('timezone', 'UTC', 'string');

-- ========================================
-- 4. WALLETS TABLE
-- ========================================
CREATE TABLE `wallets` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `wallet_name` VARCHAR(50) NOT NULL,
  `symbol` VARCHAR(10) NOT NULL,
  `network` VARCHAR(50) NOT NULL,
  `wallet_address` TEXT NOT NULL,
  `logo` VARCHAR(255) DEFAULT NULL,
  `qr_code` VARCHAR(255) DEFAULT NULL,
  `min_deposit` DECIMAL(15,8) DEFAULT 0.00000000,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample wallets
INSERT INTO `wallets` (`wallet_name`, `symbol`, `network`, `wallet_address`, `min_deposit`, `status`) VALUES
('Bitcoin', 'BTC', 'Bitcoin', 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh', 0.0001, 'active'),
('Ethereum', 'ETH', 'ERC20', '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb', 0.001, 'active'),
('Tether USDT', 'USDT', 'TRC20', 'TRX4FDSFSDF3432434FDSF', 10, 'active'),
('Tether USDT', 'USDT', 'BEP20', '0x8894E0a0c962CB723c1976a4421c95949bE2D4E3', 10, 'active'),
('Binance Coin', 'BNB', 'BEP20', 'bnb1grpf0955h0ykzq3ar5nmum7y6gdfl6lxfn46h2', 0.01, 'active');

-- ========================================
-- 5. DEPOSITS TABLE
-- ========================================
CREATE TABLE `deposits` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `wallet_id` INT(11) NOT NULL,
  `amount` DECIMAL(15,8) NOT NULL,
  `txid` VARCHAR(100) DEFAULT NULL,
  `screenshot` VARCHAR(255) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `admin_note` TEXT DEFAULT NULL,
  `approved_by` INT(11) DEFAULT NULL,
  `approved_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_wallet_id` (`wallet_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 6. WITHDRAWALS TABLE
-- ========================================
CREATE TABLE `withdrawals` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `wallet_id` INT(11) NOT NULL,
  `amount` DECIMAL(15,8) NOT NULL,
  `fee` DECIMAL(15,8) DEFAULT 0.00000000,
  `net_amount` DECIMAL(15,8) NOT NULL,
  `wallet_address` TEXT NOT NULL,
  `network` VARCHAR(50) NOT NULL,
  `txid` VARCHAR(100) DEFAULT NULL,
  `screenshot` VARCHAR(255) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'rejected') DEFAULT 'pending',
  `admin_note` TEXT DEFAULT NULL,
  `processed_by` INT(11) DEFAULT NULL,
  `processed_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_wallet_id` (`wallet_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 7. INVESTMENT PLANS TABLE
-- ========================================
CREATE TABLE `investment_plans` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `plan_name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `profit_percent` DECIMAL(5,2) NOT NULL,
  `profit_type` ENUM('daily', 'weekly', 'monthly') DEFAULT 'daily',
  `duration_days` INT(11) NOT NULL,
  `min_amount` DECIMAL(15,2) NOT NULL,
  `max_amount` DECIMAL(15,2) NOT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample investment plans
INSERT INTO `investment_plans` (`plan_name`, `description`, `profit_percent`, `profit_type`, `duration_days`, `min_amount`, `max_amount`, `status`) VALUES
('Starter Plan', 'Perfect for beginners', 2.00, 'daily', 30, 10, 500, 'active'),
('Silver Plan', 'Great for growing your portfolio', 3.00, 'daily', 60, 500, 2000, 'active'),
('Gold Plan', 'Maximum returns for serious investors', 5.00, 'daily', 90, 2000, 10000, 'active'),
('Platinum Plan', 'Elite investment tier', 7.00, 'daily', 120, 10000, 50000, 'active');

-- ========================================
-- 8. INVESTMENTS TABLE
-- ========================================
CREATE TABLE `investments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `plan_id` INT(11) NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `profit_percent` DECIMAL(5,2) NOT NULL,
  `total_profit` DECIMAL(15,2) DEFAULT 0.00,
  `earned_profit` DECIMAL(15,2) DEFAULT 0.00,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `last_profit_date` DATE DEFAULT NULL,
  `status` ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_plan_id` (`plan_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 9. TRANSACTIONS TABLE
-- ========================================
CREATE TABLE `transactions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `type` ENUM('deposit', 'withdrawal', 'investment', 'profit', 'referral_bonus', 'bonus', 'deduction') NOT NULL,
  `amount` DECIMAL(15,8) NOT NULL,
  `balance_before` DECIMAL(15,8) DEFAULT 0.00000000,
  `balance_after` DECIMAL(15,8) DEFAULT 0.00000000,
  `reference_id` INT(11) DEFAULT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_reference` (`reference_id`, `reference_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 10. NOTIFICATIONS TABLE
-- ========================================
CREATE TABLE `notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
  `is_read` TINYINT(1) DEFAULT 0,
  `link` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 11. REFERRALS TABLE
-- ========================================
CREATE TABLE `referrals` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `referrer_id` INT(11) NOT NULL,
  `referred_id` INT(11) NOT NULL,
  `bonus_type` ENUM('deposit', 'investment') NOT NULL,
  `bonus_amount` DECIMAL(15,8) NOT NULL,
  `reference_id` INT(11) DEFAULT NULL,
  `status` ENUM('pending', 'paid') DEFAULT 'pending',
  `paid_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_referrer_id` (`referrer_id`),
  KEY `idx_referred_id` (`referred_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
