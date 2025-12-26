<?php

/**
 * Trip Management Functions (Procedural)
 * Automatic trip detection and tracking
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Detect and create trip from telemetry data
 * Called when new telemetry is received
 */
function trip_detect_from_telemetry(int $deviceId, array $telemetryData): ?int
{
    $device = db_fetch_one("SELECT tenant_id, asset_id FROM devices WHERE id = :id", ['id' => $deviceId]);
    if (!$device) {
        return null;
    }
    
    $tenantId = $device['tenant_id'];
    $assetId = $device['asset_id'];
    $ignition = (bool)($telemetryData['ignition'] ?? false);
    $timestamp = $telemetryData['timestamp'] ?? date('Y-m-d H:i:s');
    
    // Get the last telemetry point
    $lastTelemetry = db_fetch_one(
        "SELECT * FROM telemetry WHERE device_id = :device_id ORDER BY timestamp DESC LIMIT 1",
        ['device_id' => $deviceId]
    );
    
    // Check if there's an active trip
    $activeTrip = trip_get_active($deviceId, $tenantId);
    
    if ($ignition && !$activeTrip) {
        // Ignition turned on - start new trip
        $tripId = trip_start($deviceId, $tenantId, $assetId, $telemetryData);
        
        // Add first waypoint
        if ($tripId) {
            trip_add_waypoint($tripId, $telemetryData);
        }
        
        return $tripId;
    } elseif (!$ignition && $activeTrip) {
        // Ignition turned off - end trip
        trip_end($activeTrip['id'], $telemetryData);
        return null;
    } elseif ($ignition && $activeTrip) {
        // Trip in progress - add waypoint
        trip_add_waypoint($activeTrip['id'], $telemetryData);
        return $activeTrip['id'];
    }
    
    return null;
}

/**
 * Get active trip for device
 */
function trip_get_active(int $deviceId, ?int $tenantId = null): ?array
{
    if ($tenantId === null) {
        $device = db_fetch_one("SELECT tenant_id FROM devices WHERE id = :id", ['id' => $deviceId]);
        $tenantId = $device['tenant_id'] ?? null;
    }
    
    if (!$tenantId) {
        return null;
    }
    
    $sql = "SELECT * FROM trips 
            WHERE device_id = :device_id 
            AND tenant_id = :tenant_id 
            AND end_time IS NULL 
            ORDER BY start_time DESC 
            LIMIT 1";
    
    return db_fetch_one($sql, ['device_id' => $deviceId, 'tenant_id' => $tenantId]);
}

/**
 * Start a new trip
 */
function trip_start(int $deviceId, int $tenantId, ?int $assetId, array $telemetryData): int
{
    $sql = "INSERT INTO trips (
        tenant_id, device_id, asset_id, start_time, start_lat, start_lon, 
        start_odometer, created_at
    ) VALUES (
        :tenant_id, :device_id, :asset_id, :start_time, :start_lat, :start_lon,
        :start_odometer, NOW()
    )";
    
    db_execute($sql, [
        'tenant_id' => $tenantId,
        'device_id' => $deviceId,
        'asset_id' => $assetId,
        'start_time' => $telemetryData['timestamp'] ?? date('Y-m-d H:i:s'),
        'start_lat' => $telemetryData['lat'] ?? 0,
        'start_lon' => $telemetryData['lon'] ?? 0,
        'start_odometer' => $telemetryData['odometer'] ?? null
    ]);
    
    return (int)db_last_insert_id();
}

/**
 * End a trip
 */
function trip_end(int $tripId, array $telemetryData): bool
{
    $trip = db_fetch_one("SELECT * FROM trips WHERE id = :id", ['id' => $tripId]);
    if (!$trip) {
        return false;
    }
    
    $endTime = $telemetryData['timestamp'] ?? date('Y-m-d H:i:s');
    $endLat = $telemetryData['lat'] ?? $trip['start_lat'];
    $endLon = $telemetryData['lon'] ?? $trip['start_lon'];
    $endOdometer = $telemetryData['odometer'] ?? null;
    
    // Calculate trip statistics
    $stats = trip_calculate_statistics($tripId);
    
    $sql = "UPDATE trips SET 
        end_time = :end_time,
        end_lat = :end_lat,
        end_lon = :end_lon,
        end_odometer = :end_odometer,
        distance_km = :distance_km,
        duration_minutes = :duration_minutes,
        max_speed = :max_speed,
        avg_speed = :avg_speed,
        idle_time_minutes = :idle_time_minutes,
        updated_at = NOW()
    WHERE id = :id";
    
    db_execute($sql, [
        'id' => $tripId,
        'end_time' => $endTime,
        'end_lat' => $endLat,
        'end_lon' => $endLon,
        'end_odometer' => $endOdometer,
        'distance_km' => $stats['distance_km'] ?? 0,
        'duration_minutes' => $stats['duration_minutes'] ?? 0,
        'max_speed' => $stats['max_speed'] ?? null,
        'avg_speed' => $stats['avg_speed'] ?? null,
        'idle_time_minutes' => $stats['idle_time_minutes'] ?? 0
    ]);
    
    // Add final waypoint
    trip_add_waypoint($tripId, $telemetryData);
    
    return true;
}

/**
 * Add waypoint to trip
 */
function trip_add_waypoint(int $tripId, array $telemetryData): bool
{
    $sequence = db_fetch_one(
        "SELECT COALESCE(MAX(sequence), 0) + 1 as next_seq FROM trip_waypoints WHERE trip_id = :trip_id",
        ['trip_id' => $tripId]
    );
    
    $nextSeq = $sequence['next_seq'] ?? 1;
    
    $sql = "INSERT INTO trip_waypoints (
        trip_id, telemetry_id, sequence, lat, lon, timestamp, 
        speed, heading, altitude
    ) VALUES (
        :trip_id, :telemetry_id, :sequence, :lat, :lon, :timestamp,
        :speed, :heading, :altitude
    )";
    
    db_execute($sql, [
        'trip_id' => $tripId,
        'telemetry_id' => $telemetryData['telemetry_id'] ?? null,
        'sequence' => $nextSeq,
        'lat' => $telemetryData['lat'] ?? 0,
        'lon' => $telemetryData['lon'] ?? 0,
        'timestamp' => $telemetryData['timestamp'] ?? date('Y-m-d H:i:s'),
        'speed' => $telemetryData['speed'] ?? null,
        'heading' => $telemetryData['heading'] ?? null,
        'altitude' => $telemetryData['altitude'] ?? null
    ]);
    
    return true;
}

/**
 * Calculate trip statistics
 */
function trip_calculate_statistics(int $tripId): array
{
    $trip = db_fetch_one("SELECT * FROM trips WHERE id = :id", ['id' => $tripId]);
    if (!$trip) {
        return [];
    }
    
    // Get waypoints
    $waypoints = db_fetch_all(
        "SELECT * FROM trip_waypoints WHERE trip_id = :trip_id ORDER BY sequence ASC",
        ['trip_id' => $tripId]
    );
    
    if (empty($waypoints)) {
        return [
            'distance_km' => 0,
            'duration_minutes' => 0,
            'max_speed' => null,
            'avg_speed' => null,
            'idle_time_minutes' => 0
        ];
    }
    
    // Calculate distance
    $distanceKm = 0;
    $maxSpeed = 0;
    $totalSpeed = 0;
    $speedCount = 0;
    $idleTimeMinutes = 0;
    
    for ($i = 1; $i < count($waypoints); $i++) {
        $prev = $waypoints[$i - 1];
        $curr = $waypoints[$i];
        
        // Calculate distance between points
        $distanceKm += haversine_distance(
            $prev['lat'], $prev['lon'],
            $curr['lat'], $curr['lon']
        );
        
        // Track speeds
        if ($curr['speed'] !== null) {
            $speed = (float)$curr['speed'];
            $maxSpeed = max($maxSpeed, $speed);
            $totalSpeed += $speed;
            $speedCount++;
            
            // Idle detection (speed < 5 km/h for more than 1 minute)
            if ($speed < 5) {
                $timeDiff = (strtotime($curr['timestamp']) - strtotime($prev['timestamp'])) / 60;
                if ($timeDiff > 1) {
                    $idleTimeMinutes += $timeDiff;
                }
            }
        }
    }
    
    // Calculate duration
    $startTime = strtotime($trip['start_time']);
    $endTime = $trip['end_time'] ? strtotime($trip['end_time']) : time();
    $durationMinutes = ($endTime - $startTime) / 60;
    
    // Calculate average speed
    $avgSpeed = $speedCount > 0 ? ($totalSpeed / $speedCount) : null;
    
    return [
        'distance_km' => round($distanceKm, 2),
        'duration_minutes' => (int)round($durationMinutes),
        'max_speed' => $maxSpeed > 0 ? round($maxSpeed, 2) : null,
        'avg_speed' => $avgSpeed ? round($avgSpeed, 2) : null,
        'idle_time_minutes' => (int)round($idleTimeMinutes)
    ];
}

/**
 * Get trips for device
 */
function trip_list_by_device(int $deviceId, ?int $tenantId = null, int $limit = 50): array
{
    if ($tenantId === null) {
        $device = db_fetch_one("SELECT tenant_id FROM devices WHERE id = :id", ['id' => $deviceId]);
        $tenantId = $device['tenant_id'] ?? null;
    }
    
    if (!$tenantId) {
        return [];
    }
    
    $sql = "SELECT t.*, d.device_uid, a.name as asset_name
            FROM trips t
            INNER JOIN devices d ON t.device_id = d.id
            LEFT JOIN assets a ON t.asset_id = a.id
            WHERE t.device_id = :device_id AND t.tenant_id = :tenant_id
            ORDER BY t.start_time DESC
            LIMIT :limit";
    
    return db_fetch_all($sql, [
        'device_id' => $deviceId,
        'tenant_id' => $tenantId,
        'limit' => $limit
    ]);
}

/**
 * Get trip with waypoints
 */
function trip_find_with_waypoints(int $tripId, ?int $tenantId = null): ?array
{
    if ($tenantId === null) {
        $trip = db_fetch_one("SELECT tenant_id FROM trips WHERE id = :id", ['id' => $tripId]);
        $tenantId = $trip['tenant_id'] ?? null;
    }
    
    if (!$tenantId) {
        return null;
    }
    
    $trip = db_fetch_one(
        "SELECT t.*, d.device_uid, a.name as asset_name
         FROM trips t
         INNER JOIN devices d ON t.device_id = d.id
         LEFT JOIN assets a ON t.asset_id = a.id
         WHERE t.id = :id AND t.tenant_id = :tenant_id",
        ['id' => $tripId, 'tenant_id' => $tenantId]
    );
    
    if (!$trip) {
        return null;
    }
    
    $waypoints = db_fetch_all(
        "SELECT * FROM trip_waypoints WHERE trip_id = :trip_id ORDER BY sequence ASC",
        ['trip_id' => $tripId]
    );
    
    $trip['waypoints'] = $waypoints;
    
    return $trip;
}

/**
 * Haversine distance calculation (helper function)
 */
function haversine_distance(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $earthRadius = 6371; // km
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earthRadius * $c;
}

