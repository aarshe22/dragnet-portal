# Dragnet Intelematics Web Portal

Multi-tenant asset tracking and telematics management portal built with **procedural PHP** (no MVC frameworks).

## Overview

Dragnet Intelematics is a comprehensive telematics and asset tracking platform designed to help organizations monitor and manage their fleet, vehicles, and equipment in real-time. The platform provides real-time GPS tracking, automated alerts, geofencing, trip management, comprehensive reporting, and more.

## Key Features

### Core Functionality
- **Multi-Tenant Architecture**: Strict tenant isolation at database query level
- **Real-Time Tracking**: Live GPS tracking with interactive maps
- **Device Management**: Complete lifecycle management with health monitoring
- **Asset Management**: Link devices to assets/vehicles for better organization
- **Trip Detection**: Automatic trip detection and route playback
- **Geofencing**: Virtual boundaries with automatic entry/exit detection
- **Alert System**: Real-time alerts with multiple notification channels
- **Reporting**: Comprehensive analytics and reporting system
- **PWA Support**: Installable web app with offline capabilities and push notifications

### Advanced Features
- **Device Groups**: Organize devices into groups for bulk operations
- **Alert Rules**: Configurable alert rules with thresholds and conditions
- **User Alert Subscriptions**: Users can subscribe to specific alerts
- **Email Integration**: Support for multiple email providers (SMTP and API-based)
- **Database Migrations**: Server-side migration management with auto-scanning
- **Schema Comparison**: Compare live database with schema.sql seed file
- **Role-Based Access Control**: Six role levels (Guest, ReadOnly, Operator, Administrator, TenantOwner, Developer)

### Technical Features
- **Procedural PHP Architecture**: Explicit, maintainable code without MVC complexity
- **Teltonika FMM13A Support**: Native integration for Teltonika devices
- **SSO Authentication**: SAML, OAuth2, OIDC support (Microsoft Entra ID, Google Workspace)
- **Live Map**: Real-time device tracking with Leaflet.js and multiple map providers
- **Push Notifications**: Web Push API support for real-time notifications
- **Service Worker**: Offline support and background sync

## Technology Stack

- **Backend**: PHP 8.2+, MySQL/MariaDB, Procedural PHP (no frameworks)
- **Frontend**: Bootstrap 5, jQuery, Chart.js, Leaflet.js
- **Architecture**: Page-based routing with shared include files
- **Database**: MySQL/MariaDB with multi-tenant schema
- **Web Standards**: PWA, Web Push API, Service Workers

## Installation

1. **Install dependencies**
   ```bash
   composer install
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

3. **Set up database**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

4. **Configure web server**
   - Point document root to project root
   - Ensure `.htaccess` is enabled (Apache) or configure URL rewriting (Nginx)

5. **Set permissions**
   ```bash
   chmod -R 755 storage public/uploads
   ```

6. **Initial Setup**
   - Create initial tenant via Admin panel or database
   - Create first user account (via SSO or development login)
   - Configure email settings (Admin → Email Integration)
   - Configure map provider (Admin → Settings)

## Project Structure

```
dragnet-portal/
├── api/                    # AJAX endpoints (procedural PHP)
│   ├── admin/              # Admin API endpoints
│   ├── alerts/             # Alert management endpoints
│   ├── assets/             # Asset management endpoints
│   ├── geofences/          # Geofence endpoints
│   ├── push/               # Push notification endpoints
│   ├── reports/            # Report generation endpoints
│   └── teltonika/          # Telemetry ingestion
├── includes/               # Shared functions
│   ├── db.php             # Database functions
│   ├── auth.php           # Authentication
│   ├── tenant.php         # Tenant context and roles
│   ├── devices.php        # Device management
│   ├── assets.php         # Asset management
│   ├── alerts.php         # Alert system
│   ├── geofences.php      # Geofencing
│   ├── trips.php          # Trip detection
│   ├── reports.php        # Report generation
│   ├── migrations.php     # Database migrations
│   ├── schema_comparison.php # Schema comparison
│   └── ...                # Other includes
├── pages/                  # Page files
│   ├── dashboard.php      # Main dashboard
│   ├── map.php            # Live map
│   ├── devices.php        # Device listing
│   ├── assets.php         # Asset listing
│   ├── alerts.php         # Alerts management
│   ├── geofences.php      # Geofence management
│   ├── trips.php          # Trip history
│   ├── reports.php        # Reports
│   ├── admin.php          # Admin panel
│   └── help.php           # Help system
├── views/                  # View templates
│   └── layout.php         # Main layout
├── public/                 # Static assets
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript
│   ├── icons/             # PWA icons
│   ├── manifest.json      # PWA manifest
│   └── service-worker.js  # Service worker
├── database/               # Database files
│   ├── schema.sql         # Database schema
│   └── migrations/        # Migration files
├── config.php             # Configuration
└── index.php              # Main router
```

## Code Style

- **Procedural PHP**: Functions, not classes
- **Explicit SQL**: Prepared statements, no ORM
- **Page-based**: Each page is a standalone PHP file
- **Shared includes**: Common logic in include files
- **Tenant-scoped**: All queries explicitly include tenant_id

## User Roles

The platform supports six role levels with hierarchical permissions:

1. **Guest** (Level 0): Read-only access, view-only permissions
2. **ReadOnly** (Level 1): Can view all data but cannot make changes
3. **Operator** (Level 2): Can manage devices, assets, alerts, and view reports
4. **Administrator** (Level 3): Full access including user management and settings
5. **TenantOwner** (Level 4): Complete control over tenant and all resources
6. **Developer** (Level 5): Top-level role with all capabilities, including database migrations and schema management

## Teltonika FMM13A Integration

The portal supports Teltonika FMM13A devices. Telemetry data should be sent to:

```
POST /api/teltonika/telemetry.php?imei=YOUR_IMEI
```

Or with header:
```
X-IMEI: YOUR_IMEI
```

The endpoint expects normalized JSON telemetry data (Codec8/8E parsing handled separately).

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

## Main Pages

- `/dashboard` - Main dashboard with widgets and alerts
- `/map` - Live map with real-time device tracking
- `/devices` - Device listing and management
- `/assets` - Asset listing and management
- `/alerts` - Alert management and acknowledgment
- `/geofences` - Geofence creation and management
- `/trips` - Trip history and playback
- `/reports` - Report generation and analytics
- `/admin` - Administration panel (Administrator+ only)
- `/help` - Comprehensive help documentation
- `/profile` - User profile management
- `/settings` - User settings

## API Endpoints

### Device & Asset Management
- `GET /api/devices/map.php` - Get devices for map display
- `GET /api/assets.php` - List assets
- `POST /api/assets.php` - Create asset
- `PUT /api/assets.php` - Update asset
- `DELETE /api/assets.php` - Delete asset

### Alerts
- `GET /api/alerts/acknowledge.php` - Acknowledge alert
- `GET /api/alert_rules.php` - List alert rules
- `POST /api/alert_rules.php` - Create alert rule

### Geofences
- `GET /api/geofences.php` - List geofences
- `GET /api/geofences/events.php` - Get geofence events
- `GET /api/geofences/analytics.php` - Get geofence analytics

### Reports
- `GET /api/reports/generate.php` - Generate report

### Admin
- `GET /api/admin/tenants.php` - Tenant management
- `GET /api/admin/users.php` - User management
- `GET /api/admin/devices.php` - Device management
- `GET /api/admin/logs.php` - Telematics logs
- `GET /api/admin/migrations.php` - Migration management (Developer only)
- `GET /api/admin/schema.php` - Schema comparison (Developer only)

### Push Notifications
- `POST /api/push/subscribe.php` - Subscribe to push notifications
- `POST /api/push/unsubscribe.php` - Unsubscribe from push notifications
- `GET /api/push/vapid-key.php` - Get VAPID public key

### Telemetry
- `POST /api/teltonika/telemetry.php` - Ingest telemetry data

## Database Migrations

The platform includes a comprehensive migration management system:

- **Auto-Scanning**: Automatically detects applied migrations by checking database schema
- **Apply Migrations**: Apply pending migrations with error handling
- **Purge Migrations**: Remove migration tracking records
- **Schema Comparison**: Compare live database with `database/schema.sql`
- **Schema Sync**: Update `schema.sql` to match live database

Accessible via Admin panel → Database Migrations tab (Developer role only).

## Progressive Web App (PWA)

The platform is a fully functional PWA with:

- **Installable**: Can be installed on iOS, Android, Windows, and macOS
- **Offline Support**: Service worker caches resources for offline use
- **Push Notifications**: Web Push API support for real-time notifications
- **GPS Access**: Can request location permissions for enhanced features
- **App-like Experience**: Standalone mode with custom icons and splash screens

## Email Integration

Supports multiple email providers:

**SMTP Providers**: SMTP, SMTP.com, SMTP2GO, Gmail, Outlook/Office 365, Yahoo Mail, Zoho Mail, ProtonMail, FastMail, Mail.com, AOL Mail

**API Providers**: SendGrid, Mailgun, Amazon SES, Postmark, SparkPost, Mailjet, Mandrill (Mailchimp), Sendinblue (Brevo), Pepipost, Postal

Configure via Admin → Email Integration tab.

## Documentation

- **README.md** (this file):** Overview and quick start
- **FEATURES.md**: Complete feature list
- **SETUP.md**: Detailed setup instructions
- **PRODUCTION.md**: Production deployment guide
- **GEOFENCE_FEATURES.md**: Geofencing system documentation
- **Online Help**: Comprehensive help system at `/help`

## License

Proprietary - All rights reserved
