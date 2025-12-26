<?php

/**
 * Device Type Definitions
 * Common device types that can be tracked with their corresponding icons
 */

/**
 * Get all available device types
 */
function get_device_types(): array
{
    return [
        'vehicle' => [
            'label' => 'Vehicle',
            'icon' => 'fa-car',
            'color' => '#0066cc'
        ],
        'truck' => [
            'label' => 'Truck',
            'icon' => 'fa-truck',
            'color' => '#0066cc'
        ],
        'van' => [
            'label' => 'Van',
            'icon' => 'fa-shuttle-van',
            'color' => '#0066cc'
        ],
        'trailer' => [
            'label' => 'Trailer',
            'icon' => 'fa-trailer',
            'color' => '#666666'
        ],
        'motorcycle' => [
            'label' => 'Motorcycle',
            'icon' => 'fa-motorcycle',
            'color' => '#cc6600'
        ],
        'boat' => [
            'label' => 'Boat',
            'icon' => 'fa-ship',
            'color' => '#0066cc'
        ],
        'aircraft' => [
            'label' => 'Aircraft',
            'icon' => 'fa-plane',
            'color' => '#0066cc'
        ],
        'equipment' => [
            'label' => 'Equipment',
            'icon' => 'fa-tools',
            'color' => '#cc6600'
        ],
        'container' => [
            'label' => 'Container',
            'icon' => 'fa-box',
            'color' => '#666666'
        ],
        'person' => [
            'label' => 'Person',
            'icon' => 'fa-user',
            'color' => '#0066cc'
        ],
        'cargo' => [
            'label' => 'Cargo',
            'icon' => 'fa-boxes',
            'color' => '#666666'
        ],
        'generator' => [
            'label' => 'Generator',
            'icon' => 'fa-bolt',
            'color' => '#ffcc00'
        ],
        'tank' => [
            'label' => 'Tank',
            'icon' => 'fa-flask',
            'color' => '#cc6600'
        ],
        'crane' => [
            'label' => 'Crane',
            'icon' => 'fa-building',
            'color' => '#cc6600'
        ],
        'excavator' => [
            'label' => 'Excavator',
            'icon' => 'fa-tractor',
            'color' => '#cc6600'
        ],
        'other' => [
            'label' => 'Other',
            'icon' => 'fa-circle',
            'color' => '#999999'
        ]
    ];
}

/**
 * Get device type configuration
 */
function get_device_type_config(string $type): ?array
{
    $types = get_device_types();
    return $types[$type] ?? $types['vehicle'];
}

/**
 * Get device type icon HTML
 */
function get_device_type_icon_html(string $type, string $status = 'offline', int $size = 24): string
{
    $config = get_device_type_config($type);
    $icon = $config['icon'] ?? 'fa-circle';
    
    // Status-based color
    $statusColors = [
        'online' => '#28a745',
        'moving' => '#28a745',
        'idle' => '#ffc107',
        'parked' => '#6c757d',
        'offline' => '#dc3545'
    ];
    
    $color = $statusColors[$status] ?? $config['color'];
    
    return sprintf(
        '<i class="fas %s" style="color: %s; font-size: %dpx; text-shadow: 0 0 3px rgba(255,255,255,0.8);"></i>',
        htmlspecialchars($icon),
        htmlspecialchars($color),
        $size
    );
}

