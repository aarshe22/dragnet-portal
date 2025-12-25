<?php

/**
 * API: User Invite Management
 */

// Load configuration first
$config = require __DIR__ . '/../../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/invites.php';
require_once __DIR__ . '/../../includes/email.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Administrator');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        if ($method !== 'POST') {
            json_response(['error' => 'Method not allowed'], 405);
        }
        
        $data = input();
        $email = $data['email'] ?? '';
        $role = $data['role'] ?? 'Guest';
        $expiresInDays = (int)($data['expires_in_days'] ?? 7);
        
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['error' => 'Valid email address required'], 400);
        }
        
        $context = get_tenant_context();
        
        try {
            $invite = invite_create(
                $context['tenant_id'],
                $email,
                $role,
                $context['user_id'],
                $expiresInDays
            );
            
            // Send invitation email
            $inviteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                . '://' . $_SERVER['HTTP_HOST']
                . '/register.php?token=' . urlencode($invite['token']);
            
            $tenant = db_fetch_one("SELECT name FROM tenants WHERE id = :id", ['id' => $context['tenant_id']]);
            
            $subject = 'Invitation to Dragnet Intelematics';
            $htmlMessage = "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <h2>You've been invited to Dragnet Intelematics</h2>
                    <p>You have been invited to join <strong>" . htmlspecialchars($tenant['name'] ?? 'Dragnet Intelematics') . "</strong> on Dragnet Intelematics.</p>
                    <p>Your role will be: <strong>" . htmlspecialchars($role) . "</strong></p>
                    <p>Click the button below to accept your invitation and create your account:</p>
                    <p style='margin: 30px 0;'>
                        <a href='" . htmlspecialchars($inviteUrl) . "' 
                           style='background-color: #1a1a1a; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold;'>
                            Accept Invitation
                        </a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; color: #666;'>" . htmlspecialchars($inviteUrl) . "</p>
                    <p style='color: #999; font-size: 12px; margin-top: 30px;'>
                        This invitation will expire on " . date('F j, Y \a\t g:i A', strtotime($invite['expires_at'])) . ".
                    </p>
                </body>
                </html>
            ";
            
            $textMessage = "You've been invited to join " . ($tenant['name'] ?? 'Dragnet Intelematics') . " on Dragnet Intelematics.\n\n";
            $textMessage .= "Your role will be: " . $role . "\n\n";
            $textMessage .= "Accept your invitation by visiting:\n" . $inviteUrl . "\n\n";
            $textMessage .= "This invitation will expire on " . date('F j, Y \a\t g:i A', strtotime($invite['expires_at'])) . ".";
            
            send_email($email, $subject, $textMessage, $htmlMessage);
            
            json_response([
                'success' => true,
                'message' => 'Invitation sent',
                'invite' => $invite
            ]);
        } catch (Exception $e) {
            json_response(['error' => $e->getMessage()], 400);
        }
        break;
        
    case 'list':
        $context = get_tenant_context();
        $invites = invite_list_all($context['tenant_id']);
        json_response(['invites' => $invites]);
        break;
        
    case 'resend':
        if ($method !== 'POST') {
            json_response(['error' => 'Method not allowed'], 405);
        }
        
        $inviteId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if (!$inviteId) {
            json_response(['error' => 'Invite ID required'], 400);
        }
        
        $context = get_tenant_context();
        
        try {
            $invite = invite_resend($inviteId, $context['tenant_id']);
            
            // Resend invitation email
            $inviteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                . '://' . $_SERVER['HTTP_HOST']
                . '/register.php?token=' . urlencode($invite['token']);
            
            $tenant = db_fetch_one("SELECT name FROM tenants WHERE id = :id", ['id' => $context['tenant_id']]);
            
            $subject = 'Invitation to Dragnet Intelematics (Resent)';
            $htmlMessage = "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <h2>You've been invited to Dragnet Intelematics</h2>
                    <p>You have been invited to join <strong>" . htmlspecialchars($tenant['name'] ?? 'Dragnet Intelematics') . "</strong> on Dragnet Intelematics.</p>
                    <p>Your role will be: <strong>" . htmlspecialchars($invite['role']) . "</strong></p>
                    <p>Click the button below to accept your invitation and create your account:</p>
                    <p style='margin: 30px 0;'>
                        <a href='" . htmlspecialchars($inviteUrl) . "' 
                           style='background-color: #1a1a1a; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold;'>
                            Accept Invitation
                        </a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; color: #666;'>" . htmlspecialchars($inviteUrl) . "</p>
                    <p style='color: #999; font-size: 12px; margin-top: 30px;'>
                        This invitation will expire on " . date('F j, Y \a\t g:i A', strtotime($invite['expires_at'])) . ".
                    </p>
                </body>
                </html>
            ";
            
            $textMessage = "You've been invited to join " . ($tenant['name'] ?? 'Dragnet Intelematics') . " on Dragnet Intelematics.\n\n";
            $textMessage .= "Your role will be: " . $invite['role'] . "\n\n";
            $textMessage .= "Accept your invitation by visiting:\n" . $inviteUrl . "\n\n";
            $textMessage .= "This invitation will expire on " . date('F j, Y \a\t g:i A', strtotime($invite['expires_at'])) . ".";
            
            send_email($invite['email'], $subject, $textMessage, $htmlMessage);
            
            json_response([
                'success' => true,
                'message' => 'Invitation resent'
            ]);
        } catch (Exception $e) {
            json_response(['error' => $e->getMessage()], 400);
        }
        break;
        
    case 'delete':
        if ($method !== 'DELETE' && $method !== 'POST') {
            json_response(['error' => 'Method not allowed'], 405);
        }
        
        $inviteId = (int)($_POST['id'] ?? $_GET['id'] ?? input('id') ?? 0);
        if (!$inviteId) {
            json_response(['error' => 'Invite ID required'], 400);
        }
        
        $context = get_tenant_context();
        
        if (invite_delete($inviteId, $context['tenant_id'])) {
            json_response(['success' => true, 'message' => 'Invitation deleted']);
        } else {
            json_response(['error' => 'Failed to delete invitation'], 400);
        }
        break;
        
    default:
        json_response(['error' => 'Invalid action'], 400);
}

