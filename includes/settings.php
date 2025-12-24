<?php

/**
 * Settings Functions (Procedural)
 * Application settings management
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Get application settings
 */
function get_settings(?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    // For now, store in config. In production, could use a settings table
    $config = $GLOBALS['config'] ?? [];
    
    return [
        'map_provider' => $_ENV['MAP_PROVIDER'] ?? 'openstreetmap',
        'map_zoom' => $_ENV['MAP_ZOOM'] ?? 10,
        'map_center_lat' => $_ENV['MAP_CENTER_LAT'] ?? 40.7128,
        'map_center_lon' => $_ENV['MAP_CENTER_LON'] ?? -74.0060,
    ];
}

/**
 * Save settings (for tenant or global)
 */
function save_settings(array $settings, ?int $tenantId = null): bool
{
    // In production, this would save to a settings table
    // For now, we'll store in session/cache or return success
    // The actual implementation would depend on your storage preference
    
    // Could use a settings table:
    // INSERT INTO settings (tenant_id, key, value) VALUES (...) ON DUPLICATE KEY UPDATE value = ...
    
    return true;
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

