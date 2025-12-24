# DragNet Telematics Web Portal

Multi-tenant asset tracking and telematics management portal built with **procedural PHP** (no MVC frameworks).

## Features

- **Procedural PHP Architecture**: Explicit, maintainable code without MVC complexity
- **Multi-tenant**: Strict tenant isolation at database query level
- **Teltonika FMM13A Support**: Native integration for Teltonika devices
- **SSO Authentication**: SAML, OAuth2, OIDC support (Microsoft Entra ID, Google Workspace)
- **Live Map**: Real-time device tracking with Leaflet.js
- **Device Management**: Complete lifecycle management with health monitoring
- **Alerts & Notifications**: Real-time alerts with push notification support
- **PWA Support**: Installable web app with offline capabilities

## Technology Stack

- **Backend**: PHP 8.2+, MySQL/MariaDB, Procedural PHP (no frameworks)
- **Frontend**: Bootstrap 5, jQuery, Chart.js, Leaflet.js
- **Architecture**: Page-based routing with shared include files

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

## Project Structure

```
dragnet-portal/
├── api/              # AJAX endpoints (procedural PHP)
├── includes/         # Shared functions (db.php, auth.php, etc.)
├── pages/            # Page files (login.php, dashboard.php, etc.)
├── views/            # View templates (layout.php)
├── public/           # Static assets (CSS, JS, icons)
├── database/         # Database schema
├── config.php        # Configuration
└── index.php         # Main router
```

## Code Style

- **Procedural PHP**: Functions, not classes
- **Explicit SQL**: Prepared statements, no ORM
- **Page-based**: Each page is a standalone PHP file
- **Shared includes**: Common logic in include files
- **Tenant-scoped**: All queries explicitly include tenant_id

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

## License

Proprietary - All rights reserved

