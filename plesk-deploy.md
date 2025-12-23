# Plesk Deployment Guide

## Common Issues with Plesk Git Deployment

### 1. Document Root Configuration

In Plesk, after pulling the Git repository:

1. Go to **Domains** → Your Domain → **Hosting Settings**
2. Set **Document root** to the repository root directory (not `/public`)
3. The path should be something like: `/var/www/vhosts/yourdomain.com/httpdocs/` or wherever your Git repo is cloned

### 2. Install Composer Dependencies

Plesk Git extension doesn't automatically run `composer install`. You need to do this manually:

**Option A: Via Plesk File Manager**
1. Navigate to your domain's file manager
2. Open terminal/SSH (if available)
3. Run: `cd /path/to/your/repo && composer install`

**Option B: Via SSH**
```bash
ssh into your server
cd /path/to/your/repo
composer install --no-dev --optimize-autoloader
```

**Option C: Add Post-Deployment Hook**
Create a file `.git/hooks/post-receive` or use Plesk's deployment hooks:
```bash
#!/bin/bash
cd /path/to/your/repo
composer install --no-dev --optimize-autoloader
```

### 3. File Permissions

Set correct permissions:
```bash
chmod 755 index.php
chmod 644 .htaccess
chmod -R 755 storage
chmod -R 755 public
```

### 4. PHP Version

Ensure PHP 8.2+ is selected:
1. Go to **Domains** → Your Domain → **PHP Settings**
2. Select PHP 8.2 or higher
3. Enable required extensions:
   - pdo
   - pdo_mysql
   - json
   - session
   - curl

### 5. Enable Error Reporting (Temporarily)

Add to `index.php` at the top (for debugging):
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### 6. Check .htaccess Support

Ensure Apache mod_rewrite is enabled:
1. Go to **Tools & Settings** → **Apache Modules**
2. Enable `mod_rewrite`

Or if using Nginx, you'll need to configure URL rewriting differently.

### 7. Database Configuration

1. Create database in Plesk
2. Update `.env` file with database credentials
3. Import schema: `mysql -u user -p database < database/schema.sql`

### 8. Environment File

Create `.env` file in the root directory with your configuration.

## Quick Diagnostic Steps

1. **Check if index.php is accessible directly:**
   - Visit: `https://yourdomain.com/index.php`
   - Should show JSON error or redirect to login

2. **Check PHP errors:**
   - Enable error display (see step 5)
   - Check Plesk error logs: **Logs** → **Error Log**

3. **Check if vendor directory exists:**
   - If missing, run `composer install`

4. **Test database connection:**
   - Use the diagnostic script (see below)

5. **Check file paths:**
   - Ensure all paths in `config/config.php` are correct
   - Plesk paths might differ from local

## Plesk-Specific Paths

Common Plesk paths:
- Document root: `/var/www/vhosts/yourdomain.com/httpdocs/`
- Or: `/var/www/vhosts/yourdomain.com/`
- Logs: `/var/www/vhosts/yourdomain.com/logs/`

Adjust paths in `config/config.php` if needed.

