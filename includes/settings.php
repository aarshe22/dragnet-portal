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
        // Try to query the table
        db_fetch_one("SELECT 1 FROM settings LIMIT 1");
        return true;
    } catch (PDOException $e) {
        // Check if it's a "table doesn't exist" error
        if (strpos($e->getMessage(), "doesn't exist") !== false || 
            strpos($e->getMessage(), "Unknown table") !== false ||
            strpos($e->getMessage(), "Table") !== false && strpos($e->getMessage(), "doesn't exist") !== false) {
            return false;
        }
        // Other database errors - rethrow
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
        'map_zoom' => $_ENV['MAP_ZOOM'] ?? 10,
        'map_center_lat' => $_ENV['MAP_CENTER_LAT'] ?? 40.7128,
        'map_center_lon' => $_ENV['MAP_CENTER_LON'] ?? -74.0060,
    ];
    
    // Try to get from database first
    if (!settings_table_exists()) {
        return $defaults;
    }
    
    try {
        $settings = db_fetch_all(
            "SELECT setting_key, setting_value FROM settings WHERE tenant_id " . ($tenantId ? "= :tenant_id" : "IS NULL"),
            $tenantId ? ['tenant_id' => $tenantId] : []
        );
        
        $result = [];
        foreach ($settings as $setting) {
            $value = $setting['setting_value'];
            // Try to decode JSON, otherwise use as string
            $decoded = json_decode($value, true);
            $result[$setting['setting_key']] = $decoded !== null ? $decoded : $value;
        }
        
        // Merge with defaults
        return array_merge($defaults, $result);
    } catch (Exception $e) {
        // If settings table doesn't exist or query fails, return defaults
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
            // Convert value to string
            $stringValue = is_array($value) ? json_encode($value) : (string)$value;
            
            // Use NULL for tenant_id if not provided (global settings)
            $params = [
                'tenant_id' => $tenantId,
                'key' => $key,
                'value' => $stringValue
            ];
            
            // Try insert first, then update if duplicate
            try {
                db_execute(
                    "INSERT INTO settings (tenant_id, setting_key, setting_value) 
                     VALUES (:tenant_id, :key, :value)",
                    $params
                );
            } catch (PDOException $e) {
                // If duplicate key error, update instead
                if (strpos($e->getMessage(), 'Duplicate entry') !== false || 
                    strpos($e->getMessage(), '23000') !== false) {
                    db_execute(
                        "UPDATE settings SET setting_value = :value, updated_at = NOW() 
                         WHERE tenant_id " . ($tenantId ? "= :tenant_id" : "IS NULL") . " AND setting_key = :key",
                        $params
                    );
                } else {
                    throw $e;
                }
            }
        }
        
        db_commit();
        return true;
    } catch (PDOException $e) {
        db_rollback();
        $errorMsg = 'Database error: ' . $e->getMessage();
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $errorMsg .= ' (Duplicate key - trying update instead)';
        }
        error_log('Failed to save settings: ' . $errorMsg);
        throw new Exception($errorMsg);
    } catch (Exception $e) {
        db_rollback();
        error_log('Failed to save settings: ' . $e->getMessage());
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
