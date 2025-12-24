# DragNet Portal - Production Deployment Guide

## Pre-Deployment Checklist

### 1. Environment Configuration

- [ ] Copy `.env.example` to `.env`
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure database credentials
- [ ] Configure SSO providers (if using)
- [ ] Generate VAPID keys for push notifications
- [ ] Set secure session configuration

### 2. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE dragnet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p dragnet < database/schema.sql

# Create initial tenant
mysql -u root -p dragnet -e "INSERT INTO tenants (name, region) VALUES ('Production Tenant', 'us-east');"
```

### 3. File Permissions

```bash
# Set proper permissions
chmod -R 755 .
chmod -R 777 storage
chmod -R 777 public/uploads
chown -R www-data:www-data .
```

### 4. Security Hardening

- [ ] Disable directory listing in web server
- [ ] Enable HTTPS only
- [ ] Configure secure headers (HSTS, CSP, etc.)
- [ ] Set up firewall rules
- [ ] Configure rate limiting
- [ ] Enable database connection encryption
- [ ] Review and restrict file uploads

### 5. Web Server Configuration

#### Apache (.htaccess already included)

Ensure mod_rewrite is enabled:
```bash
a2enmod rewrite
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name dragnet.example.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name dragnet.example.com;
    root /var/www/dragnet-portal;
    index index.php;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    # Static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Main routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ \.(env|log|sql)$ {
        deny all;
    }
}
```

### 6. PHP Configuration

Recommended `php.ini` settings:

```ini
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
date.timezone = UTC
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

### 7. Monitoring & Logging

- [ ] Set up error logging
- [ ] Configure log rotation
- [ ] Set up monitoring (e.g., New Relic, Datadog)
- [ ] Configure alerts for critical errors
- [ ] Set up database backups
- [ ] Monitor disk space

### 8. Performance Optimization

- [ ] Enable OPcache
- [ ] Configure database query caching
- [ ] Set up CDN for static assets
- [ ] Enable gzip compression
- [ ] Configure browser caching
- [ ] Optimize database indexes

### 9. Backup Strategy

```bash
# Database backup script
#!/bin/bash
mysqldump -u root -p dragnet > /backups/dragnet_$(date +%Y%m%d_%H%M%S).sql

# File backup
tar -czf /backups/dragnet_files_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/dragnet-portal
```

### 10. Testing

- [ ] Test all authentication flows
- [ ] Test tenant isolation
- [ ] Test device data ingestion
- [ ] Test admin panel functions
- [ ] Test mobile responsiveness
- [ ] Load testing
- [ ] Security testing

## Post-Deployment

### Initial Setup

1. **Create Admin User:**
   - Use development login or SSO
   - Access admin panel
   - Create additional tenants/users as needed

2. **Register Devices:**
   - Add devices via admin panel
   - Configure Teltonika devices to send data to `/api/teltonika/telemetry.php?imei=YOUR_IMEI`

3. **Configure Alerts:**
   - Set up alert rules
   - Configure push notifications (if using)

### Maintenance

- Regular database backups
- Monitor error logs
- Update dependencies regularly
- Review audit logs
- Monitor performance metrics

## Troubleshooting

### Common Issues

1. **Database Connection Errors:**
   - Check `.env` configuration
   - Verify database server is running
   - Check firewall rules

2. **404 Errors:**
   - Verify `.htaccess` is enabled (Apache)
   - Check URL rewriting configuration
   - Verify file permissions

3. **Session Issues:**
   - Check session directory permissions
   - Verify session configuration in `config.php`
   - Check cookie settings

4. **Performance Issues:**
   - Enable OPcache
   - Review slow query log
   - Check database indexes
   - Monitor server resources

## Support

For production support, ensure:
- Error logging is enabled
- Debug mode is disabled
- All sensitive data is properly secured
- Regular security audits are performed

