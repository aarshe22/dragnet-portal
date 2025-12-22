# DragNet Telematics Web Portal

Multi-tenant asset tracking and telematics management portal for GPS tracking, vehicle telemetry, video evidence, and intelligent alerting.

## Features

- **Multi-tenant Architecture**: Strict tenant isolation at all layers
- **SSO Authentication**: SAML, OAuth2, and OIDC support (Microsoft Entra ID, Google Workspace)
- **Live Map**: Real-time device tracking with Leaflet.js
- **Asset & Device Management**: Complete lifecycle management
- **Alerts & Notifications**: Real-time alerts with push notification support
- **Video Review**: Timeline-based video playback and evidence management
- **Geofencing**: Polygon, circle, and rectangle geofences with entry/exit rules
- **Trips & History**: Trip segmentation and playback
- **Reports & Analytics**: Comprehensive reporting suite
- **PWA Support**: Installable web app with offline capabilities

## Technology Stack

- **Backend**: PHP 8.2+, MySQL/MariaDB
- **Frontend**: Bootstrap 5, jQuery, Chart.js, Leaflet.js
- **Architecture**: Lightweight modular monolith with MVC separation

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd dragnet-portal
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

4. **Set up database**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

5. **Configure web server**
   - Point document root to `/public` directory
   - Ensure `.htaccess` is enabled (Apache) or configure URL rewriting (Nginx)
   - PHP 8.2+ required

6. **Set permissions**
   ```bash
   chmod -R 755 storage public/uploads
   ```

## Configuration

### Database
Configure database connection in `.env`:
```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=dragnet
DB_USER=your_user
DB_PASSWORD=your_password
```

### SSO Authentication
Configure your SSO provider in `.env`. See `.env.example` for available options.

### Push Notifications
Generate VAPID keys for push notifications:
```bash
# Use a tool like web-push to generate keys
```

## Project Structure

```
dragnet-portal/
├── config/           # Configuration files
├── database/         # Database schema
├── public/           # Public web root
│   ├── css/         # Stylesheets
│   ├── js/          # JavaScript
│   └── icons/       # PWA icons
├── src/
│   ├── Core/        # Core framework classes
│   ├── Controllers/ # MVC controllers
│   ├── Models/      # Data models
│   └── Views/       # View templates
└── index.php        # Front controller
```

## Security

- **Tenant Isolation**: Every request, query, and view is tenant-scoped
- **Role-Based Access Control**: Guest, ReadOnly, Operator, Administrator, TenantOwner
- **No Password Storage**: SSO-only authentication
- **Secure Sessions**: HTTP-only, secure cookies
- **Audit Logging**: Administrative actions and sensitive operations logged

## Development

### Code Style
- Explicit, readable, production-minded code
- No placeholders or framework invention
- Maintainable long-lived platform focus

### Adding New Features
1. Add route in `config/routes.php`
2. Create controller in `src/Controllers/`
3. Create model in `src/Models/` (if needed)
4. Create view in `src/Views/`
5. Ensure tenant scoping in all database operations

## License

Proprietary - All rights reserved
