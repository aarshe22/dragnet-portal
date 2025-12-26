SET FOREIGN_KEY_CHECKS = 0;

-- Expand alerts.type enum to include comprehensive telematics alert types
ALTER TABLE `alerts` 
MODIFY COLUMN `type` enum(
  'device_offline',
  'device_online',
  'ignition_on',
  'ignition_off',
  'speed_violation',
  'idle_time',
  'low_voltage',
  'low_battery',
  'geofence_entry',
  'geofence_exit',
  'harsh_braking',
  'harsh_acceleration',
  'harsh_cornering',
  'maintenance_reminder',
  'parking_violation',
  'route_deviation',
  'fuel_level_low',
  'fuel_level_critical',
  'engine_fault',
  'gps_loss',
  'tampering_detected',
  'overspeed_zone',
  'unauthorized_movement',
  'driver_behavior',
  'temperature_alert',
  'door_open',
  'door_closed',
  'panic_button',
  'tow_detection',
  'impact_detection'
) NOT NULL;

SET FOREIGN_KEY_CHECKS = 1;

