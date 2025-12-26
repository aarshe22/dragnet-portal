<?php

/**
 * Alert Types Definitions
 * Comprehensive list of standard telematics alert types and their configurations
 */

/**
 * Get all available alert types with their definitions
 */
function get_alert_types(): array
{
    return [
        'device_offline' => [
            'label' => 'Device Offline',
            'description' => 'Device has not reported in for a specified time period',
            'severity' => 'critical',
            'has_threshold' => true,
            'threshold_unit' => 'minutes',
            'default_threshold' => 15,
            'icon' => 'fa-wifi',
            'category' => 'device_status'
        ],
        'device_online' => [
            'label' => 'Device Online',
            'description' => 'Device comes back online after being offline',
            'severity' => 'info',
            'has_threshold' => false,
            'icon' => 'fa-wifi',
            'category' => 'device_status'
        ],
        'ignition_on' => [
            'label' => 'Ignition On',
            'description' => 'Vehicle ignition is turned on',
            'severity' => 'info',
            'has_threshold' => false,
            'icon' => 'fa-power-off',
            'category' => 'ignition'
        ],
        'ignition_off' => [
            'label' => 'Ignition Off',
            'description' => 'Vehicle ignition is turned off',
            'severity' => 'info',
            'has_threshold' => false,
            'icon' => 'fa-power-off',
            'category' => 'ignition'
        ],
        'speed_violation' => [
            'label' => 'Speed Violation',
            'description' => 'Vehicle exceeds specified speed limit',
            'severity' => 'warning',
            'has_threshold' => true,
            'threshold_unit' => 'km/h',
            'default_threshold' => 120,
            'icon' => 'fa-tachometer-alt',
            'category' => 'driving'
        ],
        'idle_time' => [
            'label' => 'Excessive Idle Time',
            'description' => 'Vehicle has been idling for longer than specified duration',
            'severity' => 'warning',
            'has_threshold' => true,
            'threshold_unit' => 'minutes',
            'default_threshold' => 30,
            'icon' => 'fa-clock',
            'category' => 'driving'
        ],
        'low_voltage' => [
            'label' => 'Low Voltage',
            'description' => 'External voltage drops below threshold',
            'severity' => 'warning',
            'has_threshold' => true,
            'threshold_unit' => 'volts',
            'default_threshold' => 11.5,
            'icon' => 'fa-bolt',
            'category' => 'vehicle_health'
        ],
        'low_battery' => [
            'label' => 'Low Battery',
            'description' => 'Internal battery level drops below threshold',
            'severity' => 'warning',
            'has_threshold' => true,
            'threshold_unit' => 'percent',
            'default_threshold' => 20,
            'icon' => 'fa-battery-quarter',
            'category' => 'vehicle_health'
        ],
        'geofence_entry' => [
            'label' => 'Geofence Entry',
            'description' => 'Device enters a defined geofence area',
            'severity' => 'info',
            'has_threshold' => false,
            'icon' => 'fa-map-marker-alt',
            'category' => 'geofence'
        ],
        'geofence_exit' => [
            'label' => 'Geofence Exit',
            'description' => 'Device exits a defined geofence area',
            'severity' => 'warning',
            'has_threshold' => false,
            'icon' => 'fa-map-marker-alt',
            'category' => 'geofence'
        ],
        'harsh_braking' => [
            'label' => 'Harsh Braking',
            'description' => 'Sudden deceleration detected (G-force threshold)',
            'severity' => 'warning',
            'has_threshold' => true,
            'threshold_unit' => 'G',
            'default_threshold' => 0.5,
            'icon' => 'fa-exclamation-triangle',
            'category' => 'driving'
        ],
        'harsh_acceleration' => [
            'label' => 'Harsh Acceleration',
            'description' => 'Sudden acceleration detected (G-force threshold)',
            'severity' => 'warning',
            'has_threshold' => true,
            'threshold_unit' => 'G',
            'default_threshold' => 0.5,
            'icon' => 'fa-exclamation-triangle',
            'category' => 'driving'
        ],
        'harsh_cornering' => [
            'label' => 'Harsh Cornering',
            'description' => 'Sharp turn detected (G-force threshold)',
            'severity' => 'warning',
            'has_threshold' => true,
            'threshold_unit' => 'G',
            'default_threshold' => 0.5,
            'icon' => 'fa-exclamation-triangle',
            'category' => 'driving'
        ],
        'maintenance_reminder' => [
            'label' => 'Maintenance Reminder',
            'description' => 'Vehicle maintenance due based on mileage or time',
            'severity' => 'info',
            'has_threshold' => true,
            'threshold_unit' => 'km or days',
            'default_threshold' => 10000,
            'icon' => 'fa-wrench',
            'category' => 'maintenance'
        ],
        'parking_violation' => [
            'label' => 'Parking Violation',
            'description' => 'Vehicle parked in unauthorized location or time',
            'severity' => 'warning',
            'has_threshold' => false,
            'icon' => 'fa-parking',
            'category' => 'location'
        ],
        'route_deviation' => [
            'label' => 'Route Deviation',
            'description' => 'Vehicle deviates from planned route',
            'severity' => 'warning',
            'has_threshold' => true,
            'threshold_unit' => 'meters',
            'default_threshold' => 500,
            'icon' => 'fa-route',
            'category' => 'location'
        ],
        'fuel_level_low' => [
            'label' => 'Low Fuel Level',
            'description' => 'Fuel level drops below threshold',
            'severity' => 'warning',
            'has_threshold' => true,
            'threshold_unit' => 'percent',
            'default_threshold' => 15,
            'icon' => 'fa-gas-pump',
            'category' => 'vehicle_health'
        ],
        'fuel_level_critical' => [
            'label' => 'Critical Fuel Level',
            'description' => 'Fuel level drops to critical level',
            'severity' => 'critical',
            'has_threshold' => true,
            'threshold_unit' => 'percent',
            'default_threshold' => 5,
            'icon' => 'fa-gas-pump',
            'category' => 'vehicle_health'
        ],
        'engine_fault' => [
            'label' => 'Engine Fault',
            'description' => 'Engine diagnostic trouble code detected',
            'severity' => 'critical',
            'has_threshold' => false,
            'icon' => 'fa-exclamation-circle',
            'category' => 'vehicle_health'
        ],
        'gps_loss' => [
            'label' => 'GPS Signal Loss',
            'description' => 'GPS signal lost for extended period',
            'severity' => 'warning',
            'has_threshold' => true,
            'threshold_unit' => 'seconds',
            'default_threshold' => 60,
            'icon' => 'fa-satellite',
            'category' => 'device_status'
        ],
        'tampering_detected' => [
            'label' => 'Tampering Detected',
            'description' => 'Device tampering or removal detected',
            'severity' => 'critical',
            'has_threshold' => false,
            'icon' => 'fa-shield-alt',
            'category' => 'security'
        ],
        'overspeed_zone' => [
            'label' => 'Overspeed in Zone',
            'description' => 'Vehicle exceeds speed limit in specific zone',
            'severity' => 'warning',
            'has_threshold' => true,
            'threshold_unit' => 'km/h',
            'default_threshold' => 50,
            'icon' => 'fa-tachometer-alt',
            'category' => 'driving'
        ],
        'unauthorized_movement' => [
            'label' => 'Unauthorized Movement',
            'description' => 'Vehicle moves when it should be stationary',
            'severity' => 'critical',
            'has_threshold' => false,
            'icon' => 'fa-lock',
            'category' => 'security'
        ],
        'driver_behavior' => [
            'label' => 'Driver Behavior',
            'description' => 'Aggressive or unsafe driving pattern detected',
            'severity' => 'warning',
            'has_threshold' => false,
            'icon' => 'fa-user-shield',
            'category' => 'driving'
        ],
        'temperature_alert' => [
            'label' => 'Temperature Alert',
            'description' => 'Vehicle temperature exceeds safe operating range',
            'severity' => 'warning',
            'has_threshold' => true,
            'threshold_unit' => 'celsius',
            'default_threshold' => 90,
            'icon' => 'fa-thermometer-half',
            'category' => 'vehicle_health'
        ],
        'door_open' => [
            'label' => 'Door Open',
            'description' => 'Vehicle door opened',
            'severity' => 'info',
            'has_threshold' => false,
            'icon' => 'fa-door-open',
            'category' => 'vehicle_status'
        ],
        'door_closed' => [
            'label' => 'Door Closed',
            'description' => 'Vehicle door closed',
            'severity' => 'info',
            'has_threshold' => false,
            'icon' => 'fa-door-closed',
            'category' => 'vehicle_status'
        ],
        'panic_button' => [
            'label' => 'Panic Button',
            'description' => 'Emergency panic button activated',
            'severity' => 'critical',
            'has_threshold' => false,
            'icon' => 'fa-exclamation-circle',
            'category' => 'security'
        ],
        'tow_detection' => [
            'label' => 'Tow Detection',
            'description' => 'Vehicle being towed or lifted detected',
            'severity' => 'critical',
            'has_threshold' => false,
            'icon' => 'fa-truck',
            'category' => 'security'
        ],
        'impact_detection' => [
            'label' => 'Impact Detection',
            'description' => 'Vehicle impact or collision detected',
            'severity' => 'critical',
            'has_threshold' => true,
            'threshold_unit' => 'G',
            'default_threshold' => 2.0,
            'icon' => 'fa-car-crash',
            'category' => 'safety'
        ]
    ];
}

/**
 * Get alert types by category
 */
function get_alert_types_by_category(): array
{
    $types = get_alert_types();
    $categorized = [];
    
    foreach ($types as $key => $type) {
        $category = $type['category'] ?? 'other';
        if (!isset($categorized[$category])) {
            $categorized[$category] = [];
        }
        $categorized[$category][$key] = $type;
    }
    
    return $categorized;
}

/**
 * Get alert type definition
 */
function get_alert_type(string $type): ?array
{
    $types = get_alert_types();
    return $types[$type] ?? null;
}

