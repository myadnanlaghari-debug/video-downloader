# Database Setup Instructions

## Error Explanation

The error you encountered:
```
#1044 - Access denied for user 'u577740496_hrmadmin'@'127.0.0.1' to database 'crypto_investment'
```

This happens because **shared hosting providers restrict users from creating databases via SQL commands**. You must create the database through your hosting control panel first.

---

## Step-by-Step Solution

### Step 1: Create Database in cPanel

1. Log into your **cPanel** (usually at `yourdomain.com/cpanel`)
2. Find and click **"MySQL Databases"** or **"MySQL Database Wizard"**
3. Under "Create New Database", enter a database name (e.g., `crypto_invest`)
4. Click **"Create Database"**

### Step 2: Create Database User

1. In the same MySQL Databases page, scroll to "MySQL Users"
2. Create a new user with a strong password (e.g., `crypto_user`)
3. Save the username and password securely

### Step 3: Assign User to Database

1. Scroll to "Add User To Database" section
2. Select your user and database from dropdowns
3. Click **"Add"**
4. Check **"ALL PRIVILEGES"** checkbox
5. Click **"Make Changes"**

### Step 4: Update Configuration File

Open `/workspace/config/database.php` and update these values:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'u577740496_your_database_name');  // Your cPanel database name
define('DB_USER', 'u577740496_your_username');       // Your cPanel database user
define('DB_PASS', 'your_strong_password');           // Your database password
```

**Note:** On shared hosting, database names usually include your cPanel username prefix.

### Step 5: Import the Schema

1. Go back to cPanel
2. Open **phpMyAdmin**
3. Select your database from the left sidebar
4. Click **"Import"** tab
5. Choose the file: `/workspace/database/schema.sql`
6. Click **"Go"** to import

---

## Alternative: Direct SQL Import Command

If you have SSH access, you can run:

```bash
mysql -u u577740496_hrmadmin -p your_database_name < /path/to/schema.sql
```

---

## Verification

After importing, verify tables were created:

1. In phpMyAdmin, select your database
2. You should see these tables:
   - users
   - admins
   - wallets
   - deposits
   - withdrawals
   - investment_plans
   - investments
   - transactions
   - notifications
   - settings
   - referrals

---

## Default Admin Login

After successful setup:
- **Username:** `admin`
- **Password:** `admin123`

**⚠️ IMPORTANT:** Change this password immediately after first login!

---

## Troubleshooting

### Still Getting Access Denied?

1. Verify database name includes your cPanel prefix
2. Ensure user has ALL PRIVILEGES on the database
3. Check if you're connecting to correct host (sometimes it's not `localhost`)
4. Contact your hosting support for exact database credentials

### Can't Find MySQL Databases in cPanel?

Some hosts use different names:
- "Database Manager"
- "MariaDB Databases"
- "Databases" section

Look for any database-related icon in your cPanel.

---

## Need Help?

Contact your hosting provider's support team with:
- Your cPanel username
- Request to create a MySQL database
- Ask for database connection details (host, name, user)

They can guide you through their specific process.
