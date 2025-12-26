-- Performance Optimization Indexes
-- Improves query performance for common operations

SET FOREIGN_KEY_CHECKS = 0;

-- Telemetry table indexes (most critical for performance)
-- Composite index for device queries with timestamp sorting
CREATE INDEX idx_telemetry_device_timestamp ON telemetry(device_id, timestamp DESC);

-- Index for date range queries
CREATE INDEX idx_telemetry_timestamp ON telemetry(timestamp DESC);

-- Index for location-based queries
CREATE INDEX idx_telemetry_location ON telemetry(lat, lon);

-- Index for ignition-based queries (trip detection)
CREATE INDEX idx_telemetry_ignition ON telemetry(device_id, ignition, timestamp DESC);

-- Devices table indexes
-- Composite index for tenant status queries (dashboard, device lists)
CREATE INDEX idx_devices_tenant_status ON devices(tenant_id, status);

-- Index for device lookup by IMEI (telemetry ingestion)
CREATE INDEX idx_devices_imei ON devices(imei);

-- Index for last_seen queries (offline detection)
CREATE INDEX idx_devices_last_seen ON devices(last_seen DESC);

-- Index for asset_id lookups
CREATE INDEX idx_devices_asset_id ON devices(asset_id);

-- Alerts table indexes
-- Composite index for tenant alert queries
CREATE INDEX idx_alerts_tenant_created ON alerts(tenant_id, created_at DESC);

-- Index for unacknowledged alerts
CREATE INDEX idx_alerts_acknowledged ON alerts(tenant_id, acknowledged, created_at DESC);

-- Index for alert type queries
CREATE INDEX idx_alerts_type ON alerts(tenant_id, type, created_at DESC);

-- Index for severity-based queries
CREATE INDEX idx_alerts_severity ON alerts(tenant_id, severity, acknowledged);

-- Index for device alerts
CREATE INDEX idx_alerts_device ON alerts(device_id, created_at DESC);

-- Assets table indexes
-- Index for tenant asset queries
CREATE INDEX idx_assets_tenant ON assets(tenant_id, status);

-- Geofences table indexes
-- Index for active geofence queries
CREATE INDEX idx_geofences_active ON geofences(tenant_id, active);

-- Users table indexes
-- Index for email lookups (authentication)
CREATE INDEX idx_users_email ON users(email);

-- Index for tenant user queries
CREATE INDEX idx_users_tenant ON users(tenant_id, role);

-- Index for SSO subject lookups
CREATE INDEX idx_users_sso ON users(sso_provider, sso_subject);

-- Telemetry aggregation indexes (for reports)
-- Index for speed-based queries (violations)
CREATE INDEX idx_telemetry_speed ON telemetry(device_id, speed, timestamp DESC);

-- Index for fuel level queries
CREATE INDEX idx_telemetry_fuel ON telemetry(device_id, fuel_level, timestamp DESC);

SET FOREIGN_KEY_CHECKS = 1;
