-- Crypto Investment Platform Database Schema
-- Run this SQL to create all necessary tables

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create Database
CREATE DATABASE IF NOT EXISTS `crypto_investment` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `crypto_investment`;

-- Users Table
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
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `referral_code` (`referral_code`),
  KEY `referred_by` (`referred_by`),
  CONSTRAINT `fk_referred_by` FOREIGN KEY (`referred_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admins Table
CREATE TABLE `admins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'admin', 'support') DEFAULT 'admin',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wallets Table
CREATE TABLE `wallets` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `wallet_name` VARCHAR(50) NOT NULL,
  `symbol` VARCHAR(10) NOT NULL,
  `network` VARCHAR(50) NOT NULL,
  `wallet_address` TEXT NOT NULL,
  `logo` VARCHAR(255) DEFAULT NULL,
  `qr_code` VARCHAR(255) DEFAULT NULL,
  `min_deposit` DECIMAL(15,2) DEFAULT 0.00,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deposits Table
CREATE TABLE `deposits` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `wallet_id` INT(11) NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `txid` VARCHAR(100) DEFAULT NULL,
  `screenshot` VARCHAR(255) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `approved_by` INT(11) DEFAULT NULL,
  `approved_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `wallet_id` (`wallet_id`),
  KEY `status` (`status`),
  CONSTRAINT `fk_deposits_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_deposits_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `wallets`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Withdrawals Table
CREATE TABLE `withdrawals` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `wallet_id` INT(11) DEFAULT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `wallet_address` TEXT NOT NULL,
  `network` VARCHAR(50) NOT NULL,
  `txid` VARCHAR(100) DEFAULT NULL,
  `screenshot` VARCHAR(255) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'rejected') DEFAULT 'pending',
  `processed_by` INT(11) DEFAULT NULL,
  `processed_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  CONSTRAINT `fk_withdrawals_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Investment Plans Table
CREATE TABLE `investment_plans` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `plan_name` VARCHAR(100) NOT NULL,
  `profit_percentage` DECIMAL(5,2) NOT NULL,
  `duration_days` INT(11) NOT NULL,
  `min_amount` DECIMAL(15,2) NOT NULL,
  `max_amount` DECIMAL(15,2) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Investments Table
CREATE TABLE `investments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `plan_id` INT(11) NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `total_profit` DECIMAL(15,2) DEFAULT 0.00,
  `daily_profit` DECIMAL(15,2) DEFAULT 0.00,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `last_profit_date` DATE DEFAULT NULL,
  `status` ENUM('active', 'completed', 'expired') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `plan_id` (`plan_id`),
  KEY `status` (`status`),
  CONSTRAINT `fk_investments_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_investments_plan` FOREIGN KEY (`plan_id`) REFERENCES `investment_plans`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions Table
CREATE TABLE `transactions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `type` ENUM('deposit', 'withdrawal', 'investment', 'profit', 'referral_bonus') NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `balance_before` DECIMAL(15,2) DEFAULT 0.00,
  `balance_after` DECIMAL(15,2) DEFAULT 0.00,
  `reference_id` INT(11) DEFAULT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `type` (`type`),
  CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications Table
CREATE TABLE `notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings Table
CREATE TABLE `settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `setting_type` ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Referrals Table
CREATE TABLE `referrals` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `referrer_id` INT(11) NOT NULL,
  `referred_id` INT(11) NOT NULL,
  `commission_earned` DECIMAL(15,2) DEFAULT 0.00,
  `source` ENUM('deposit', 'investment') NOT NULL,
  `reference_id` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `referrer_id` (`referrer_id`),
  KEY `referred_id` (`referred_id`),
  CONSTRAINT `fk_referrals_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_referrals_referred` FOREIGN KEY (`referred_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Admin (password: admin123)
INSERT INTO `admins` (`username`, `email`, `password`, `role`, `status`) VALUES
('admin', 'admin@cryptoinvest.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active');

-- Insert Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('site_name', 'Crypto Invest Pro', 'text'),
('site_url', 'http://localhost/crypto-investment', 'text'),
('admin_email', 'admin@cryptoinvest.com', 'text'),
('maintenance_mode', '0', 'boolean'),
('min_withdrawal', '10', 'number'),
('max_withdrawal_daily', '10000', 'number');

-- Insert Sample Investment Plans
INSERT INTO `investment_plans` (`plan_name`, `profit_percentage`, `duration_days`, `min_amount`, `max_amount`, `description`, `status`) VALUES
('Starter Plan', 2.00, 30, 10, 500, 'Perfect for beginners. Earn 2% daily profit for 30 days.', 'active'),
('Silver Plan', 3.00, 60, 500, 2000, 'Intermediate plan with 3% daily profit for 60 days.', 'active'),
('Gold Plan', 5.00, 90, 2000, 10000, 'Premium plan offering 5% daily profit for 90 days.', 'active'),
('Platinum Plan', 7.00, 120, 10000, 50000, 'Elite plan with maximum returns of 7% daily for 120 days.', 'active');

COMMIT;
