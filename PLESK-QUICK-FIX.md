# Plesk Quick Fix Guide

## Immediate Steps to Get Your Site Working

### Step 1: Run Diagnostic
1. After pulling the repo, access: `https://yourdomain.com/diagnostic.php`
2. This will show you exactly what's wrong
3. **Delete diagnostic.php after fixing issues!**

### Step 2: Install Composer Dependencies (MOST COMMON ISSUE)

Plesk Git doesn't automatically run `composer install`. You MUST do this:

**Via Plesk File Manager:**
1. Go to **File Manager**
2. Navigate to your domain's root directory
3. Look for **Terminal** or **SSH** option
4. Run: `composer install --no-dev --optimize-autoloader`

**Via SSH (if you have access):**
```bash
cd /var/www/vhosts/yourdomain.com/httpdocs/
composer install --no-dev --optimize-autoloader
```

**If composer is not installed on server:**
- Install Composer globally or use Plesk's Composer extension
- Or download `composer.phar` and run: `php composer.phar install`

### Step 3: Create .env File

1. In Plesk File Manager, go to your domain root
2. Create a file named `.env`
3. Copy content from `.env.example` and fill in your values:

```env
APP_ENV=production
APP_DEBUG=false

DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASSWORD=your_database_password

SESSION_SECURE=true
```

### Step 4: Set Document Root

1. Go to **Domains** → Your Domain → **Hosting Settings**
2. **Document root** should point to your repository root (where `index.php` is)
3. NOT to `/public` subdirectory
4. Save changes

### Step 5: Check PHP Version

1. Go to **Domains** → Your Domain → **PHP Settings**
2. Select **PHP 8.2** or higher
3. Enable these extensions:
   - pdo
   - pdo_mysql
   - json
   - session
   - curl

### Step 6: Set File Permissions

Via SSH or Plesk File Manager:
```bash
chmod 755 index.php
chmod 644 .htaccess
chmod -R 755 storage
chmod -R 755 public
```

### Step 7: Import Database

1. Create database in Plesk: **Databases** → **Add Database**
2. Import schema:
   - Go to **phpMyAdmin** or use command line
   - Select your database
   - Import `database/schema.sql`

### Step 8: Test

1. Visit: `https://yourdomain.com/`
2. Should redirect to `/login`
3. If you see errors, check:
   - Plesk error logs: **Logs** → **Error Log**
   - Enable debug temporarily in `.env`: `APP_DEBUG=true`

## Common Error Messages

### "Class not found" or "Controller not found"
→ **Solution:** Run `composer install`

### "Database connection failed"
→ **Solution:** Check `.env` database credentials

### "500 Internal Server Error"
→ **Solution:** 
1. Enable debug: `APP_DEBUG=true` in `.env`
2. Check error logs in Plesk
3. Run diagnostic.php

### "404 Not Found" on all pages
→ **Solution:** 
1. Check document root is correct
2. Verify `.htaccess` exists
3. Check mod_rewrite is enabled

### Blank white page
→ **Solution:**
1. Enable error display in `index.php` (temporarily):
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
2. Check PHP error logs

## Plesk Git Deployment Hook (Optional)

To automatically run `composer install` after Git pull:

1. Go to **Git** settings in Plesk
2. Add **Post-receive hook**:
   ```bash
   cd /var/www/vhosts/yourdomain.com/httpdocs/
   composer install --no-dev --optimize-autoloader
   ```

Or create `.git/hooks/post-receive`:
```bash
#!/bin/bash
cd /path/to/your/repo
composer install --no-dev --optimize-autoloader
chmod -R 755 storage public
```

## Still Not Working?

1. **Check diagnostic.php output** - it will tell you exactly what's wrong
2. **Check Plesk error logs** - **Logs** → **Error Log**
3. **Enable debug mode** temporarily:
   - Set `APP_DEBUG=true` in `.env`
   - Add to top of `index.php`:
     ```php
     ini_set('display_errors', 1);
     error_reporting(E_ALL);
     ```
4. **Test direct access**: `https://yourdomain.com/index.php` should work
5. **Check file paths** - Plesk paths might differ from local

## Security Reminder

**DELETE `diagnostic.php` after troubleshooting!**

