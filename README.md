# Crypto Investment Platform

A complete web-based cryptocurrency investment platform built with PHP, MySQL, HTML, CSS, and JavaScript.

## Features

### User Features
- User Registration & Login
- Manual Crypto Deposit with Screenshot Upload
- Investment Plans with Daily Profits
- Manual Withdrawal System
- Transaction History
- Referral System (5% deposit, 2% investment commission)
- Profile Management

### Admin Features
- Admin Dashboard
- User Management
- Deposit Approval/Rejection
- Withdrawal Processing
- Wallet Management
- Investment Plan Management
- Website Settings

## Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend:** PHP 8+
- **Database:** MySQL 5.7+

## Installation

### 1. Database Setup

1. Create a MySQL database named `crypto_investment`
2. Import the SQL schema from `database/schema.sql`

```bash
mysql -u root -p crypto_investment < database/schema.sql
```

### 2. Configuration

Edit `config/database.php` with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'crypto_investment');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 3. File Permissions

Set proper permissions for uploads directory:

```bash
chmod -R 755 uploads/
chmod -R 777 uploads/deposits/
chmod -R 777 uploads/withdrawals/
chmod -R 777 uploads/wallets/
```

### 4. Access the Application

- **Homepage:** http://localhost/crypto-investment/
- **User Login:** http://localhost/crypto-investment/login.php
- **User Registration:** http://localhost/crypto-investment/register.php
- **Admin Panel:** http://localhost/crypto-investment/admin/

## Default Credentials

### Admin
- **Username:** admin
- **Password:** admin123

## Project Structure

```
project/
├── admin/              # Admin panel files
├── user/               # User dashboard files
│   └── includes/       # User template includes
├── assets/
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript files
│   └── images/        # Image assets
├── uploads/
│   ├── deposits/      # Deposit screenshots
│   ├── withdrawals/   # Withdrawal proofs
│   └── wallets/       # Wallet logos/QR codes
├── includes/          # Shared PHP includes
├── config/            # Configuration files
├── database/          # Database schema
├── cron/              # Cron job scripts
├── index.php          # Homepage
├── login.php          # User login
├── register.php       # User registration
└── README.md
```

## Database Tables

- `users` - User accounts
- `admins` - Admin accounts
- `wallets` - Crypto wallet addresses
- `deposits` - Deposit requests
- `withdrawals` - Withdrawal requests
- `investment_plans` - Available investment plans
- `investments` - User investments
- `transactions` - Transaction history
- `notifications` - User notifications
- `settings` - Website settings
- `referrals` - Referral tracking

## Security Features

- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- CSRF protection
- Session security
- File upload validation
- Input sanitization

## Cron Jobs

Set up the following cron job for daily profit distribution:

```bash
0 0 * * * php /path/to/cron/profit.php
```

## Supported Cryptocurrencies

- Bitcoin (BTC)
- Ethereum (ETH)
- USDT TRC20
- USDT BEP20

## Investment Plans (Default)

| Plan | Daily Profit | Duration | Min Amount | Max Amount |
|------|-------------|----------|------------|------------|
| Starter | 2% | 30 Days | 10 USDT | 500 USDT |
| Silver | 3% | 60 Days | 500 USDT | 2000 USDT |
| Gold | 5% | 90 Days | 2000 USDT | 10000 USDT |
| Platinum | 7% | 120 Days | 10000 USDT | 50000 USDT |

## Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- SSL certificate (recommended)
- mod_rewrite enabled

## License

This project is proprietary software.

## Support

For support and inquiries, contact: admin@cryptoinvest.com

---

© 2024 Crypto Invest Pro. All rights reserved.
