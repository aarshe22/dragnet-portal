<?php

/**
 * Settings Functions (Procedural)
 * Application settings management
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Check if settings table exists
 */
function settings_table_exists(): bool
{
    try {
        db_fetch_one("SELECT 1 FROM settings LIMIT 1");
        return true;
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (stripos($msg, "doesn't exist") !== false || 
            stripos($msg, "Unknown table") !== false ||
            stripos($msg, "Table") !== false) {
            return false;
        }
        throw $e;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get application settings
 */
function get_settings(?int $tenantId = null): array
{
    // Default values
    $defaults = [
        'map_provider' => $_ENV['MAP_PROVIDER'] ?? 'openstreetmap',
        'map_zoom' => (int)($_ENV['MAP_ZOOM'] ?? 10),
        'map_center_lat' => (float)($_ENV['MAP_CENTER_LAT'] ?? 40.7128),
        'map_center_lon' => (float)($_ENV['MAP_CENTER_LON'] ?? -74.0060),
    ];
    
    // Try to get from database first
    if (!settings_table_exists()) {
        return $defaults;
    }
    
    try {
        // Build query for NULL tenant_id (global settings)
        if ($tenantId === null) {
            $settings = db_fetch_all(
                "SELECT setting_key, setting_value FROM settings WHERE tenant_id IS NULL",
                []
            );
        } else {
            $settings = db_fetch_all(
                "SELECT setting_key, setting_value FROM settings WHERE tenant_id = :tenant_id",
                ['tenant_id' => $tenantId]
            );
        }
        
        $result = [];
        foreach ($settings as $setting) {
            $value = $setting['setting_value'];
            // Try to decode JSON, otherwise use as string
            $decoded = json_decode($value, true);
            $result[$setting['setting_key']] = $decoded !== null ? $decoded : $value;
        }
        
        // Merge with defaults and convert types
        $merged = array_merge($defaults, $result);
        $merged['map_zoom'] = (int)($merged['map_zoom'] ?? 10);
        $merged['map_center_lat'] = (float)($merged['map_center_lat'] ?? 40.7128);
        $merged['map_center_lon'] = (float)($merged['map_center_lon'] ?? -74.0060);
        
        return $merged;
    } catch (Exception $e) {
        error_log('Failed to get settings: ' . $e->getMessage());
        return $defaults;
    }
}

/**
 * Save settings (for tenant or global)
 */
function save_settings(array $settings, ?int $tenantId = null): bool
{
    if (!settings_table_exists()) {
        throw new Exception('Settings table does not exist. Please run: mysql -u root -p dragnet < database/migrations/add_settings_table_safe.sql');
    }
    
    try {
        db_begin_transaction();
        
        foreach ($settings as $key => $value) {
            // Convert value to string (handle arrays/objects)
            if (is_array($value) || is_object($value)) {
                $stringValue = json_encode($value);
            } else {
                $stringValue = (string)$value;
            }
            
            // Check if record exists - handle NULL tenant_id properly
            if ($tenantId === null) {
                $existing = db_fetch_one(
                    "SELECT id FROM settings WHERE tenant_id IS NULL AND setting_key = :key",
                    ['key' => $key]
                );
            } else {
                $existing = db_fetch_one(
                    "SELECT id FROM settings WHERE tenant_id = :tenant_id AND setting_key = :key",
                    ['tenant_id' => $tenantId, 'key' => $key]
                );
            }
            
            if ($existing && isset($existing['id'])) {
                // Update existing record
                db_execute(
                    "UPDATE settings SET setting_value = :value, updated_at = NOW() WHERE id = :id",
                    ['id' => $existing['id'], 'value' => $stringValue]
                );
            } else {
                // Insert new record
                if ($tenantId === null) {
                    db_execute(
                        "INSERT INTO settings (tenant_id, setting_key, setting_value) VALUES (NULL, :key, :value)",
                        ['key' => $key, 'value' => $stringValue]
                    );
                } else {
                    db_execute(
                        "INSERT INTO settings (tenant_id, setting_key, setting_value) VALUES (:tenant_id, :key, :value)",
                        ['tenant_id' => $tenantId, 'key' => $key, 'value' => $stringValue]
                    );
                }
            }
        }
        
        db_commit();
        return true;
    } catch (PDOException $e) {
        db_rollback();
        $errorCode = $e->getCode();
        $errorMsg = $e->getMessage();
        
        // Log detailed error
        error_log('Settings save PDO error: Code=' . $errorCode . ', Message=' . $errorMsg);
        error_log('SQL State: ' . ($e->errorInfo[0] ?? 'N/A'));
        error_log('Driver Code: ' . ($e->errorInfo[1] ?? 'N/A'));
        
        throw new Exception('Database error: ' . $errorMsg . ' (Code: ' . $errorCode . ')');
    } catch (Exception $e) {
        db_rollback();
        error_log('Settings save error: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Get map provider configuration
 */
function get_map_provider_config(string $provider): array
{
    $providers = get_available_map_providers();
    return $providers[$provider] ?? $providers['openstreetmap'];
}

/**
 * Get all available map providers
 */
function get_available_map_providers(): array
{
    return [
        'openstreetmap' => [
            'name' => 'OpenStreetMap',
            'url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'attribution' => '© OpenStreetMap contributors',
            'maxZoom' => 19,
            'subdomains' => ['a', 'b', 'c']
        ],
        'openstreetmap_fr' => [
            'name' => 'OpenStreetMap France',
            'url' => 'https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png',
            'attribution' => '© OpenStreetMap France | © OpenStreetMap contributors',
            'maxZoom' => 20,
            'subdomains' => ['a', 'b', 'c']
        ],
        'openstreetmap_de' => [
            'name' => 'OpenStreetMap DE',
            'url' => 'https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png',
            'attribution' => '© OpenStreetMap DE | © OpenStreetMap contributors',
            'maxZoom' => 18,
            'subdomains' => ['a', 'b', 'c']
        ],
        'cartodb_positron' => [
            'name' => 'CartoDB Positron',
            'url' => 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
            'attribution' => '© OpenStreetMap contributors © CARTO',
            'maxZoom' => 20,
            'subdomains' => ['a', 'b', 'c', 'd']
        ],
        'cartodb_dark' => [
            'name' => 'CartoDB Dark Matter',
            'url' => 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
            'attribution' => '© OpenStreetMap contributors © CARTO',
            'maxZoom' => 20,
            'subdomains' => ['a', 'b', 'c', 'd']
        ],
        'stamen_terrain' => [
            'name' => 'Stamen Terrain',
            'url' => 'https://stamen-tiles-{s}.a.ssl.fastly.net/terrain/{z}/{x}/{y}{r}.png',
            'attribution' => 'Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under ODbL.',
            'maxZoom' => 18,
            'subdomains' => ['a', 'b', 'c', 'd']
        ],
        'stamen_toner' => [
            'name' => 'Stamen Toner',
            'url' => 'https://stamen-tiles-{s}.a.ssl.fastly.net/toner/{z}/{x}/{y}{r}.png',
            'attribution' => 'Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under ODbL.',
            'maxZoom' => 20,
            'subdomains' => ['a', 'b', 'c', 'd']
        ],
        'stamen_watercolor' => [
            'name' => 'Stamen Watercolor',
            'url' => 'https://stamen-tiles-{s}.a.ssl.fastly.net/watercolor/{z}/{x}/{y}.jpg',
            'attribution' => 'Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under ODbL.',
            'maxZoom' => 18,
            'subdomains' => ['a', 'b', 'c', 'd']
        ],
        'esri_worldstreetmap' => [
            'name' => 'Esri World Street Map',
            'url' => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}',
            'attribution' => 'Tiles © Esri',
            'maxZoom' => 19,
            'subdomains' => []
        ],
        'esri_worldtopomap' => [
            'name' => 'Esri World Topo Map',
            'url' => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',
            'attribution' => 'Tiles © Esri',
            'maxZoom' => 19,
            'subdomains' => []
        ],
        'esri_worldimagery' => [
            'name' => 'Esri World Imagery',
            'url' => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            'attribution' => 'Tiles © Esri',
            'maxZoom' => 19,
            'subdomains' => []
        ],
        'opentopomap' => [
            'name' => 'OpenTopoMap',
            'url' => 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
            'attribution' => 'Map data: &copy; OpenStreetMap contributors, SRTM | Map style: &copy; OpenTopoMap (CC-BY-SA)',
            'maxZoom' => 17,
            'subdomains' => ['a', 'b', 'c']
        ],
        'cyclosm' => [
            'name' => 'CyclOSM',
            'url' => 'https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png',
            'attribution' => '© OpenStreetMap contributors, style by CyclOSM',
            'maxZoom' => 20,
            'subdomains' => ['a', 'b', 'c']
        ],
        'wikimedia' => [
            'name' => 'Wikimedia Maps',
            'url' => 'https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png',
            'attribution' => '© OpenStreetMap contributors',
            'maxZoom' => 19,
            'subdomains' => []
        ],
    ];
}
