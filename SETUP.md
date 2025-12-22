# DragNet Portal - Setup and Deployment Guide

## Quick Start

### 1. Prerequisites
- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Web server (Apache with mod_rewrite or Nginx)

### 2. Installation Steps

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Edit .env with your configuration
# - Database credentials
# - SSO provider settings (if using)
# - Push notification VAPID keys

# Create database
mysql -u root -p -e "CREATE DATABASE dragnet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p dragnet < database/schema.sql

# Set permissions
chmod -R 755 storage public/uploads
chown -R www-data:www-data storage public/uploads  # Adjust user/group as needed
```

### 3. Web Server Configuration

#### Apache
Ensure `.htaccess` is enabled and document root points to project root (not `/public`).

#### Nginx
```nginx
server {
    listen 80;
    server_name dragnet.example.com;
    root /path/to/dragnet-portal;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 4. Initial Setup

1. **Create a tenant** (manually in database or via migration):
   ```sql
   INSERT INTO tenants (name, region) VALUES ('Demo Tenant', 'us-east');
   ```

2. **Create an admin user** (after SSO setup):
   - Users are created automatically on first SSO login
   - Update role manually: `UPDATE users SET role = 'Administrator' WHERE email = 'admin@example.com';`

3. **Configure SSO**:
   - Microsoft Entra ID: Set up app registration and configure in `.env`
   - Google Workspace: Set up OAuth2 credentials and configure in `.env`

4. **Generate PWA icons**:
   - Create `icon-192.png` and `icon-512.png` in `/public/icons/`
   - Use a tool like PWA Asset Generator

5. **Generate VAPID keys for push notifications**:
   ```bash
   # Using web-push npm package
   npx web-push generate-vapid-keys
   ```
   Add keys to `.env`

## Architecture Overview

### Core Components

1. **Front Controller** (`index.php`): Single entry point, handles routing and request dispatching
2. **Router** (`src/Core/Router.php`): Matches HTTP method + path to controller methods
3. **Application** (`src/Core/Application.php`): Container for config, database, tenant context
4. **Database** (`src/Core/Database.php`): PDO wrapper with tenant-scoped query helpers
5. **TenantContext** (`src/Core/TenantContext.php`): Manages tenant isolation and user roles

### Multi-Tenancy

Every request is tenant-scoped:
- Tenant context loaded from session (established via SSO)
- All database queries include tenant_id filter
- Models enforce tenant scoping automatically
- Controllers require tenant context before processing

### Authentication Flow

1. User accesses protected route → redirected to `/login`
2. User clicks SSO provider → redirected to IdP
3. IdP authenticates → redirects to `/auth/callback`
4. Callback extracts user info → creates/updates user record
5. Tenant context established → session created
6. User redirected to dashboard

### Security Features

- **No password storage**: SSO-only authentication
- **Tenant isolation**: Every query scoped by tenant_id
- **Role-based access**: Guest, ReadOnly, Operator, Administrator, TenantOwner
- **Secure sessions**: HTTP-only, secure cookies, SameSite protection
- **Audit logging**: Administrative actions logged (infrastructure in place)

## Development Notes

### Adding New Features

1. **Add route** in `config/routes.php`:
   ```php
   'GET /new-feature' => 'NewFeatureController@index',
   ```

2. **Create controller** in `src/Controllers/`:
   ```php
   class NewFeatureController extends BaseController {
       public function index(): string {
           return $this->view('newfeature/index');
       }
   }
   ```

3. **Create model** (if needed) in `src/Models/`:
   ```php
   class NewFeature extends BaseModel {
       protected string $table = 'new_features';
   }
   ```

4. **Create view** in `src/Views/`:
   - Use `layout.php` as base template
   - Access `$app` variable for tenant context

### Database Migrations

Currently using direct SQL schema. For production, consider:
- Migration system (e.g., Phinx)
- Version control for schema changes
- Rollback capabilities

### SSO Integration

Current implementation provides placeholders. For production:

1. **Microsoft Entra ID**:
   - Install `microsoft/microsoft-graph` or similar
   - Implement OAuth2 flow in `AuthController@oauth`
   - Extract tenant_id from token claims

2. **Google Workspace**:
   - Install `google/apiclient`
   - Implement OAuth2 flow
   - Map Google domain to tenant

3. **SAML**:
   - Install `onelogin/php-saml`
   - Implement SAML flow in `AuthController@saml`

### Push Notifications

Infrastructure in place:
- Service worker registered
- Subscription storage in database
- VAPID key configuration

To send notifications:
1. Implement notification service
2. Use web-push library server-side
3. Query `push_subscriptions` table
4. Send to each endpoint

### Performance Considerations

- **Database indexing**: Schema includes indexes, monitor query performance
- **Caching**: Consider Redis/Memcached for:
  - Device status
  - Map markers
  - Dashboard widgets
- **CDN**: Serve static assets via CDN
- **API rate limiting**: Implement for public APIs

## Testing

### Manual Testing Checklist

- [ ] SSO login flow
- [ ] Dashboard widgets load
- [ ] Live map displays devices
- [ ] Asset CRUD operations
- [ ] Device status updates
- [ ] Alert creation and acknowledgment
- [ ] Geofence creation
- [ ] Trip playback
- [ ] Video streaming
- [ ] User management
- [ ] Role-based access control
- [ ] Tenant isolation (test with multiple tenants)

### Security Testing

- [ ] Verify tenant isolation (user from tenant A cannot access tenant B data)
- [ ] Test role-based access (ReadOnly cannot modify, etc.)
- [ ] Verify SSO token validation
- [ ] Test session security
- [ ] Verify SQL injection protection (prepared statements)

## Troubleshooting

### Common Issues

1. **"Controller not found"**
   - Check namespace in controller file
   - Verify autoloader: `composer dump-autoload`

2. **"Tenant context required"**
   - User not logged in
   - Session not established
   - Check SSO callback flow

3. **Database connection errors**
   - Verify `.env` configuration
   - Check database server is running
   - Verify user permissions

4. **Routes not working**
   - Check `.htaccess` is enabled (Apache)
   - Verify URL rewriting (Nginx)
   - Check route definition in `config/routes.php`

5. **Views not rendering**
   - Check file paths
   - Verify `$app` variable is available
   - Check PHP error logs

## Production Deployment

### Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure production database
- [ ] Set up SSL/TLS certificates
- [ ] Configure SSO providers
- [ ] Generate and configure VAPID keys
- [ ] Set up monitoring/logging
- [ ] Configure backups
- [ ] Set up CI/CD pipeline
- [ ] Load testing
- [ ] Security audit

### Environment Variables

Required for production:
- Database credentials
- SSO provider credentials
- VAPID keys
- Session secure flag = true

## Support

For issues or questions, refer to:
- README.md for overview
- Code comments for implementation details
- Database schema for data model

