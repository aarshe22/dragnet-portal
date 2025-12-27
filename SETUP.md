# DragNet Portal - Setup Guide

## Quick Start

### 1. Prerequisites
- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Web server (Apache with mod_rewrite or Nginx)

### 2. Installation

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Edit .env with your configuration
# - Database credentials
# - SSO provider settings (if using)

# Create database
mysql -u root -p -e "CREATE DATABASE dragnet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p dragnet < database/schema.sql

# Set permissions
chmod -R 755 storage public/uploads
```

### 3. Web Server Configuration

#### Apache
- Ensure `.htaccess` is enabled
- Document root should point to project root

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

1. **Create a tenant**:
   ```sql
   INSERT INTO tenants (name, region) VALUES ('Demo Tenant', 'us-east');
   ```

2. **Test login**:
   - Go to `/login.php`
   - Use development login form
   - Email: `admin@example.com`
   - Tenant ID: `1`

3. **Register Teltonika device**:
   - Go to Devices page
   - Add device with IMEI
   - Configure device to send data to `/api/teltonika/telemetry.php?imei=YOUR_IMEI`

## Architecture

### Procedural PHP Structure

- **Pages** (`/pages/`): Standalone PHP files for each page
- **Includes** (`/includes/`): Shared functions (db.php, auth.php, devices.php, etc.)
- **API** (`/api/`): AJAX endpoints returning JSON
- **Views** (`/views/`): HTML templates (layout.php)
- **Router** (`index.php`): Routes requests to appropriate page files

### Key Principles

1. **No MVC**: Functions, not classes
2. **Explicit SQL**: Prepared statements, no ORM
3. **Tenant Scoping**: All queries include tenant_id
4. **Page-based**: Each page is self-contained

## Teltonika FMM13A Integration

### Telemetry Endpoint

Send normalized telemetry data to:
```
POST /api/teltonika/telemetry.php?imei=YOUR_IMEI
```

Or with header:
```
X-IMEI: YOUR_IMEI
```

### Expected Data Format

```json
{
    "timestamp": "2024-01-01 12:00:00",
    "lat": 40.7128,
    "lon": -74.0060,
    "speed": 65.5,
    "heading": 180,
    "altitude": 100,
    "satellites": 8,
    "ignition": true,
    "rpm": 2500,
    "vehicle_speed": 65.5,
    "fuel_level": 75.5,
    "odometer": 12345.67,
    "gsm_signal": 85,
    "external_voltage": 12.6,
    "internal_battery_level": 95,
    "temperature": 25.5,
    "io_payload": {
        "digital_input_1": 1,
        "analog_input_1": 1024
    }
}
```

### Device Status Logic

- **Online**: Recent telemetry within threshold
- **Moving**: Speed > 5 km/h
- **Idle**: Ignition on, speed ≤ 0.5 km/h
- **Parked**: Ignition off
- **Offline**: No telemetry beyond threshold

## Security

- **Tenant Isolation**: Every query explicitly scoped by tenant_id
- **Role-Based Access**: Guest, ReadOnly, Operator, Administrator, TenantOwner, Developer
- **SSO Only**: No password storage
- **Prepared Statements**: All SQL uses PDO prepared statements
- **CSRF Protection**: Built-in CSRF protection utilities
- **Rate Limiting**: Rate limiting functions available
- **Session Security**: Secure session management with HTTP-only cookies

## Features Overview

### Core Features
- **Real-Time Tracking**: Live GPS tracking with interactive maps
- **Device Management**: Complete device lifecycle management
- **Asset Management**: Link devices to assets/vehicles
- **Trip Detection**: Automatic trip detection and route playback
- **Geofencing**: Virtual boundaries with automatic entry/exit detection
- **Alerts**: Real-time alerts with multiple notification channels
- **Reports**: Comprehensive analytics and reporting
- **PWA**: Installable web app with offline support

### Advanced Features
- **Device Groups**: Organize devices for bulk operations
- **Alert Rules**: Configurable alert rules with thresholds
- **User Alert Subscriptions**: Users can subscribe to specific alerts
- **Email Integration**: Support for multiple email providers
- **Database Migrations**: Server-side migration management (Developer role)
- **Schema Comparison**: Compare live database with schema.sql (Developer role)

## Development

### Adding a New Page

1. Create page file in `/pages/`:
   ```php
   <?php
   require_once __DIR__ . '/../includes/db.php';
   require_once __DIR__ . '/../includes/session.php';
   require_once __DIR__ . '/../includes/tenant.php';
   require_once __DIR__ . '/../includes/auth.php';
   require_once __DIR__ . '/../includes/functions.php';
   
   $config = $GLOBALS['config'];
   db_init($config['database']);
   session_start_custom($config['session']);
   
   require_auth();
   require_role('ReadOnly');
   
   $title = 'My Page';
   // Your page logic here
   
   ob_start();
   ?>
   <!-- HTML content -->
   <?php
   $content = ob_get_clean();
   include __DIR__ . '/../views/layout.php';
   ?>
   ```

2. Add route in `index.php`:
   ```php
   '/mypage' => 'mypage.php',
   '/mypage.php' => 'mypage.php',
   ```

### Adding an API Endpoint

1. Create file in `/api/`:
   ```php
   <?php
   require_once __DIR__ . '/../../includes/db.php';
   require_once __DIR__ . '/../../includes/session.php';
   require_once __DIR__ . '/../../includes/tenant.php';
   require_once __DIR__ . '/../../includes/auth.php';
   require_once __DIR__ . '/../../includes/functions.php';
   
   $config = $GLOBALS['config'];
   db_init($config['database']);
   session_start_custom($config['session']);
   
   require_auth();
   require_role('ReadOnly');
   
   // Your API logic here
   json_response(['data' => 'value']);
   ```

2. Access via: `/api/your-endpoint.php`

## Troubleshooting

- **"Database not initialized"**: Ensure `db_init()` is called before using db functions
- **"Tenant context required"**: User must be logged in
- **404 errors**: Check route mapping in `index.php`
- **Permission denied**: Check user role with `require_role()`

