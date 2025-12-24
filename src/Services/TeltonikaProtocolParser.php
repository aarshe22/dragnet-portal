<?php

namespace DragNet\Services;

/**
 * Teltonika Protocol Parser
 * 
 * Parses Teltonika Codec 8, Codec 8E, and Codec 16 AVL data packets
 * Supports FMM13A and other Teltonika devices
 */
class TeltonikaProtocolParser
{
    /**
     * Parse Teltonika data packet
     * 
     * @param string $data Binary data from device
     * @return array Parsed data structure
     */
    public static function parsePacket(string $data): array
    {
        $offset = 0;
        $result = [
            'imei' => null,
            'codec_id' => null,
            'records' => [],
            'records_count' => 0,
        ];
        
        // Check if this is an IMEI packet (first connection)
        if (self::isImeiPacket($data)) {
            $result['imei'] = self::parseImei($data);
            return $result;
        }
        
        // Parse AVL data packet
        $result['codec_id'] = self::readUInt8($data, $offset);
        $offset += 1;
        
        $result['records_count'] = self::readUInt8($data, $offset);
        $offset += 1;
        
        // Parse each AVL record
        for ($i = 0; $i < $result['records_count']; $i++) {
            $record = self::parseAvlRecord($data, $offset);
            $result['records'][] = $record;
            $offset = $record['next_offset'];
        }
        
        // Read CRC (if present in Codec 8E/16)
        if ($result['codec_id'] == 0x08 || $result['codec_id'] == 0x8E) {
            $result['crc'] = self::readUInt32($data, $offset);
        }
        
        return $result;
    }
    
    /**
     * Check if packet is IMEI packet
     */
    private static function isImeiPacket(string $data): bool
    {
        // IMEI packet: 2 bytes length + IMEI string
        if (strlen($data) < 2) {
            return false;
        }
        
        $length = unpack('n', substr($data, 0, 2))[1];
        return ($length > 0 && $length < 20 && strlen($data) == $length + 2);
    }
    
    /**
     * Parse IMEI from packet
     */
    private static function parseImei(string $data): string
    {
        $length = unpack('n', substr($data, 0, 2))[1];
        return substr($data, 2, $length);
    }
    
    /**
     * Parse AVL record
     */
    private static function parseAvlRecord(string $data, int $offset): array
    {
        $record = [
            'timestamp' => null,
            'priority' => null,
            'gps' => [],
            'io_elements' => [],
            'next_offset' => $offset,
        ];
        
        // Timestamp (8 bytes, milliseconds since epoch)
        $timestampMs = self::readUInt64($data, $offset);
        $record['timestamp'] = date('Y-m-d H:i:s', intval($timestampMs / 1000));
        $offset += 8;
        
        // Priority (1 byte)
        $record['priority'] = self::readUInt8($data, $offset);
        $offset += 1;
        
        // GPS element (15 bytes)
        $record['gps'] = self::parseGpsElement($data, $offset);
        $offset += 15;
        
        // IO element count (1 byte)
        $ioCount = self::readUInt8($data, $offset);
        $offset += 1;
        
        // IO elements
        $record['io_elements'] = self::parseIoElements($data, $offset, $ioCount);
        $offset = $record['io_elements']['next_offset'];
        
        $record['next_offset'] = $offset;
        
        return $record;
    }
    
    /**
     * Parse GPS element
     */
    private static function parseGpsElement(string $data, int $offset): array
    {
        $gps = [];
        
        // Longitude (4 bytes, signed)
        $gps['longitude'] = self::readInt32($data, $offset) / 10000000.0;
        $offset += 4;
        
        // Latitude (4 bytes, signed)
        $gps['latitude'] = self::readInt32($data, $offset) / 10000000.0;
        $offset += 4;
        
        // Altitude (2 bytes, signed)
        $gps['altitude'] = self::readInt16($data, $offset);
        $offset += 2;
        
        // Angle (2 bytes, unsigned)
        $gps['angle'] = self::readUInt16($data, $offset);
        $offset += 2;
        
        // Satellites (1 byte)
        $gps['satellites'] = self::readUInt8($data, $offset);
        $offset += 1;
        
        // Speed (2 bytes, unsigned, km/h)
        $gps['speed'] = self::readUInt16($data, $offset);
        
        return $gps;
    }
    
    /**
     * Parse IO elements
     */
    private static function parseIoElements(string $data, int $offset, int $ioCount): array
    {
        $io = [
            'digital_inputs' => [],
            'digital_outputs' => [],
            'analog_inputs' => [],
            'analog_outputs' => [],
            'next_offset' => $offset,
        ];
        
        // 1-byte IO count
        $oneByteCount = self::readUInt8($data, $offset);
        $offset += 1;
        
        for ($i = 0; $i < $oneByteCount; $i++) {
            $id = self::readUInt8($data, $offset);
            $offset += 1;
            $value = self::readUInt8($data, $offset);
            $offset += 1;
            
            // Map common IO IDs for FMM13A
            if ($id >= 1 && $id <= 8) {
                $io['digital_inputs'][$id] = $value;
            } elseif ($id >= 9 && $id <= 16) {
                $io['digital_outputs'][$id] = $value;
            } elseif ($id >= 17 && $id <= 24) {
                $io['analog_inputs'][$id] = $value;
            }
        }
        
        // 2-byte IO count
        $twoByteCount = self::readUInt8($data, $offset);
        $offset += 1;
        
        for ($i = 0; $i < $twoByteCount; $i++) {
            $id = self::readUInt8($data, $offset);
            $offset += 1;
            $value = self::readUInt16($data, $offset);
            $offset += 2;
            
            // Common 2-byte values: battery, signal, etc.
            if ($id == 66) { // Battery voltage (mV)
                $io['battery_voltage'] = $value;
            } elseif ($id == 67) { // Battery level (%)
                $io['battery_level'] = $value;
            } elseif ($id == 68) { // Signal strength
                $io['signal_strength'] = $value;
            }
        }
        
        // 4-byte IO count
        $fourByteCount = self::readUInt8($data, $offset);
        $offset += 1;
        
        for ($i = 0; $i < $fourByteCount; $i++) {
            $id = self::readUInt8($data, $offset);
            $offset += 1;
            $value = self::readUInt32($data, $offset);
            $offset += 4;
            
            // Common 4-byte values: odometer, etc.
            if ($id == 16) { // Total odometer
                $io['odometer'] = $value;
            }
        }
        
        // 8-byte IO count
        $eightByteCount = self::readUInt8($data, $offset);
        $offset += 1;
        
        for ($i = 0; $i < $eightByteCount; $i++) {
            $id = self::readUInt8($data, $offset);
            $offset += 1;
            $value = self::readUInt64($data, $offset);
            $offset += 8;
        }
        
        $io['next_offset'] = $offset;
        return $io;
    }
    
    // Binary reading helpers
    private static function readUInt8(string $data, int $offset): int
    {
        return ord($data[$offset]);
    }
    
    private static function readUInt16(string $data, int $offset): int
    {
        return unpack('n', substr($data, $offset, 2))[1];
    }
    
    private static function readUInt32(string $data, int $offset): int
    {
        return unpack('N', substr($data, $offset, 4))[1];
    }
    
    private static function readUInt64(string $data, int $offset): int
    {
        $high = unpack('N', substr($data, $offset, 4))[1];
        $low = unpack('N', substr($data, $offset + 4, 4))[1];
        return ($high << 32) | $low;
    }
    
    private static function readInt16(string $data, int $offset): int
    {
        $value = unpack('n', substr($data, $offset, 2))[1];
        if ($value > 32767) {
            $value -= 65536;
        }
        return $value;
    }
    
    private static function readInt32(string $data, int $offset): int
    {
        $value = unpack('N', substr($data, $offset, 4))[1];
        if ($value > 2147483647) {
            $value -= 4294967296;
        }
        return $value;
    }
    
    /**
     * Generate acknowledgment packet
     */
    public static function generateAck(int $recordsCount): string
    {
        // Teltonika acknowledgment: 0x00 + number of accepted records
        return pack('CC', 0x00, $recordsCount);
    }
}

