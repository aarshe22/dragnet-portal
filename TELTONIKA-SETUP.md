# Teltonika FMM13A Integration Guide

## Overview

The DragNet Portal now supports Teltonika FMM13A devices (and other Teltonika devices using Codec 8/8E/16 protocols). This guide explains how to configure and use Teltonika devices.

## Database Migration

First, run the migration to add Teltonika support:

```bash
mysql -u your_user -p your_database < database/migrations/add_teltonika_support.sql
```

Or via phpMyAdmin, import the SQL file.

## Device Registration

### Option 1: Manual Registration via API

```bash
POST /api/devices/register-teltonika
Content-Type: application/json

{
    "imei": "123456789012345",
    "device_uid": "FMM13A-001"  // Optional
}
```

### Option 2: Automatic Registration

When a Teltonika device first connects, it will send its IMEI. The system will log the IMEI for manual registration. Check your error logs for unregistered devices.

## FMM13A Device Configuration

### 1. Network Settings

Configure your FMM13A device to send data to your server:

- **Protocol**: HTTP
- **Server URL**: `https://yourdomain.com/api/teltonika/receive`
- **Port**: 443 (HTTPS) or 80 (HTTP)
- **Method**: POST

### 2. Data Acquisition Settings

Configure when the device sends data:

- **Time Interval**: Set reporting interval (e.g., every 60 seconds)
- **Distance**: Report when vehicle moves X meters
- **Angle**: Report when heading changes
- **Speed**: Report when speed exceeds threshold

### 3. IMEI Identification

The system identifies devices by IMEI. You can provide IMEI in three ways:

**Option A: HTTP Header** (Recommended)
```
X-IMEI: 123456789012345
```

**Option B: Query Parameter**
```
POST /api/teltonika/receive?imei=123456789012345
```

**Option C: Device Configuration**
Some Teltonika devices can be configured to send IMEI in the data packet itself.

## Data Flow

1. **Device Registration**:
   - Device sends IMEI on first connection
   - System acknowledges with `01`
   - Admin registers device via API or UI

2. **Data Transmission**:
   - Device sends AVL (Automatic Vehicle Location) data packets
   - Each packet contains GPS coordinates, speed, heading, and IO elements
   - System parses and stores telemetry data
   - System responds with acknowledgment packet

3. **Data Storage**:
   - GPS data stored in `telemetry` table
   - Device status updated (battery, signal, last check-in)
   - IO elements stored in JSON format

## Supported Data Elements

### GPS Data
- Latitude/Longitude
- Speed (km/h)
- Heading/Angle
- Altitude
- Satellite count

### IO Elements (FMM13A Specific)
- **Battery Level** (IO ID 67): Battery percentage
- **Battery Voltage** (IO ID 66): Battery voltage in mV
- **Signal Strength** (IO ID 68): GSM signal strength
- **Odometer** (IO ID 16): Total distance traveled
- **Digital Inputs** (IO IDs 1-8): Digital input states
- **Analog Inputs** (IO IDs 17-24): Analog sensor values

## Testing

### Test with Simulated Data

You can test the endpoint with a simple script:

```php
<?php
// test_teltonika.php
$imei = '123456789012345';
$url = 'https://yourdomain.com/api/teltonika/receive?imei=' . $imei;

// Simple test packet (IMEI)
$data = pack('n', strlen($imei)) . $imei;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/octet-stream',
    'X-IMEI: ' . $imei
]);

$response = curl_exec($ch);
echo "Response: " . bin2hex($response) . "\n";
```

### Verify Data Reception

1. Check device status in portal - should show "online"
2. Check telemetry table for new GPS records
3. Check device battery/signal levels are updated

## Troubleshooting

### Device Not Found Error

- **Cause**: Device IMEI not registered
- **Solution**: Register device via API or check IMEI matches

### No Data Received

- **Check**: Device network settings (URL, port)
- **Check**: Server firewall allows incoming connections
- **Check**: Error logs for parsing errors

### Data Not Parsing

- **Check**: Device is using Codec 8, 8E, or 16
- **Check**: Binary data is being received (not corrupted)
- **Check**: Error logs for specific parsing errors

### Device Shows Offline

- **Check**: Last check-in timestamp
- **Check**: Device is actually sending data
- **Check**: Network connectivity from device

## Advanced Configuration

### Custom IO Element Mapping

Edit `src/Services/TeltonikaProtocolParser.php` to map additional IO elements specific to your use case.

### TCP Endpoint (Alternative)

For direct TCP connections (not HTTP), you would need to implement a TCP server. The current implementation uses HTTP for easier deployment.

### Multiple Tenants

Each device is associated with a tenant. When registering, ensure the correct tenant context is set.

## Security Considerations

1. **Authentication**: Consider adding API key authentication for the Teltonika endpoint
2. **Rate Limiting**: Implement rate limiting to prevent abuse
3. **IP Whitelisting**: Restrict endpoint access to known device IPs
4. **HTTPS**: Use HTTPS to encrypt data in transit

## Support

For Teltonika-specific protocol questions, refer to:
- [Teltonika Wiki](https://wiki.teltonika-gps.com/)
- [FMM13A Documentation](https://wiki.teltonika-gps.com/view/FMM13A)

