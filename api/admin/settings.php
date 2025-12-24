<?php

/**
 * API: Settings Management
 */

// Load configuration first
$config = require __DIR__ . '/../../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/settings.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Administrator');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    switch ($method) {
        case 'GET':
            $settings = get_settings();
            echo json_encode($settings, JSON_PRETTY_PRINT);
            exit;
            
        case 'POST':
            // Get JSON input
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON: ' . json_last_error_msg()]);
                exit;
            }
            
            if (empty($data)) {
                http_response_code(400);
                echo json_encode(['error' => 'No data provided']);
                exit;
            }
            
            // Handle test email request
            if (isset($data['test_email']) && $data['test_email'] === true) {
                $testEmailTo = $data['test_email_to'] ?? null;
                if (!$testEmailTo || !filter_var($testEmailTo, FILTER_VALIDATE_EMAIL)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid test email address']);
                    exit;
                }
                
                // Get current email settings
                $emailSettings = get_settings();
                $provider = $emailSettings['email_provider'] ?? 'smtp';
                $fromEmail = $emailSettings['email_from'] ?? 'noreply@example.com';
                
                // Send test email (basic implementation - can be enhanced)
                $subject = 'DragNet Portal - Test Email';
                $message = "This is a test email from DragNet Portal.\n\n";
                $message .= "Email provider: " . $provider . "\n";
                $message .= "Sent at: " . date('Y-m-d H:i:s') . "\n\n";
                $message .= "If you received this email, your email relay configuration is working correctly.";
                
                // For now, just return success (actual email sending would be implemented in includes/email.php)
                echo json_encode([
                    'success' => true,
                    'message' => 'Test email queued (email sending functionality to be implemented)',
                    'note' => 'Email sending requires implementation in includes/email.php'
                ], JSON_PRETTY_PRINT);
                exit;
            }
            
            // Validate map settings
            $validProviders = array_keys(get_available_map_providers());
            if (isset($data['map_provider']) && !in_array($data['map_provider'], $validProviders)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid map provider. Valid options: ' . implode(', ', $validProviders)]);
                exit;
            }
            
            // Validate and sanitize numeric values
            if (isset($data['map_zoom'])) {
                $data['map_zoom'] = (int)$data['map_zoom'];
                if ($data['map_zoom'] < 1 || $data['map_zoom'] > 20) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid zoom level (must be 1-20)']);
                    exit;
                }
            }
            
            if (isset($data['map_center_lat'])) {
                $data['map_center_lat'] = (float)$data['map_center_lat'];
                if ($data['map_center_lat'] < -90 || $data['map_center_lat'] > 90) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid latitude (must be -90 to 90)']);
                    exit;
                }
            }
            
            if (isset($data['map_center_lon'])) {
                $data['map_center_lon'] = (float)$data['map_center_lon'];
                if ($data['map_center_lon'] < -180 || $data['map_center_lon'] > 180) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid longitude (must be -180 to 180)']);
                    exit;
                }
            }
            
            // Validate email settings
            if (isset($data['email_provider'])) {
                $validEmailProviders = [
                    'smtp', 'smtp_com', 'smtp2go', 'gmail', 'outlook', 'yahoo', 'zoho', 
                    'protonmail', 'fastmail', 'mail_com', 'aol',
                    'sendgrid', 'mailgun', 'ses', 'postmark', 'sparkpost', 'mailjet', 
                    'mandrill', 'sendinblue', 'pepipost', 'postal'
                ];
                if (!in_array($data['email_provider'], $validEmailProviders)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid email provider. Valid options: ' . implode(', ', $validEmailProviders)]);
                    exit;
                }
            }
            
            if (isset($data['email_from']) && !filter_var($data['email_from'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid from email address']);
                exit;
            }
            
            if (isset($data['smtp_port'])) {
                $data['smtp_port'] = (int)$data['smtp_port'];
                if ($data['smtp_port'] < 1 || $data['smtp_port'] > 65535) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid SMTP port (must be 1-65535)']);
                    exit;
                }
            }
            
            // Save settings (global settings, tenant_id = null for admin)
            if (save_settings($data, null)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Settings saved successfully',
                    'settings' => $data
                ], JSON_PRETTY_PRINT);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to save settings (returned false)']);
            }
            exit;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    $errorMsg = 'Database error: ' . $e->getMessage();
    if ($config['app']['debug']) {
        $errorMsg .= ' | Code: ' . $e->getCode() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine();
        if (isset($e->errorInfo)) {
            $errorMsg .= ' | SQL State: ' . ($e->errorInfo[0] ?? 'N/A');
        }
    }
    echo json_encode(['error' => $errorMsg]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    $errorMsg = $e->getMessage();
    if ($config['app']['debug']) {
        $errorMsg .= ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine();
    }
    echo json_encode(['error' => $errorMsg]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    $errorMsg = 'Fatal error: ' . $e->getMessage();
    if ($config['app']['debug']) {
        $errorMsg .= ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine();
    }
    echo json_encode(['error' => $errorMsg]);
    exit;
}
