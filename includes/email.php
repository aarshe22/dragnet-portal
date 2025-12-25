<?php

/**
 * Email Sending Functions (Procedural)
 * Handles email sending with automatic logging
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/email_log.php';

/**
 * Send an email
 * Always logs the attempt (basic logging), with debug mode providing more detail
 */
function send_email(
    string $to,
    string $subject,
    string $message,
    ?string $htmlMessage = null,
    array $options = []
): bool {
    $settings = get_settings();
    $provider = $settings['email_provider'] ?? 'smtp';
    $fromEmail = $settings['email_from'] ?? 'noreply@example.com';
    $debugMode = !empty($settings['email_debug']) && ($settings['email_debug'] === '1' || $settings['email_debug'] === 1);
    
    // Basic log data (always logged)
    $logData = [
        'recipient' => $to,
        'subject' => $subject,
        'provider' => $provider,
        'status' => 'pending',
        'error_message' => null,
        'response_data' => null,
        'debug_data' => null
    ];
    
    // Debug data (only if debug mode is enabled)
    $debugData = [];
    if ($debugMode) {
        $debugData = [
            'from' => $fromEmail,
            'provider' => $provider,
            'message_length' => strlen($message),
            'html_length' => $htmlMessage ? strlen($htmlMessage) : 0,
            'options' => $options,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    try {
        // Log the attempt first (pending status)
        $logId = log_email_attempt(
            $to,
            $subject,
            $provider,
            'pending',
            null,
            null,
            $debugMode ? $debugData : null
        );
        
        // Attempt to send email based on provider
        $result = false;
        $errorMessage = null;
        $responseData = null;
        
        try {
            if (in_array($provider, ['smtp', 'smtp_com', 'smtp2go', 'gmail', 'outlook', 'yahoo', 'zoho', 'protonmail', 'fastmail', 'mail_com', 'aol'])) {
                // SMTP sending
                $result = send_email_smtp($to, $fromEmail, $subject, $message, $htmlMessage, $settings, $debugMode, $debugData);
                if (!$result && $debugMode) {
                    $errorMessage = $debugData['error'] ?? 'SMTP send failed';
                }
            } elseif ($provider === 'sendgrid') {
                $result = send_email_sendgrid($to, $fromEmail, $subject, $message, $htmlMessage, $settings, $debugMode, $debugData);
            } elseif ($provider === 'mailgun') {
                $result = send_email_mailgun($to, $fromEmail, $subject, $message, $htmlMessage, $settings, $debugMode, $debugData);
            } elseif ($provider === 'ses') {
                $result = send_email_ses($to, $fromEmail, $subject, $message, $htmlMessage, $settings, $debugMode, $debugData);
            } elseif ($provider === 'postmark') {
                $result = send_email_postmark($to, $fromEmail, $subject, $message, $htmlMessage, $settings, $debugMode, $debugData);
            } else {
                $errorMessage = "Unsupported email provider: $provider";
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            if ($debugMode) {
                $debugData['exception'] = $errorMessage;
            }
        }
        
        // Update log with result (always update, even if sending failed)
        if ($result) {
            update_email_log($logId, 'sent', null, $responseData, $debugMode ? $debugData : null);
            return true;
        } else {
            update_email_log($logId, 'failed', $errorMessage ?: 'Unknown error', $responseData, $debugMode ? $debugData : null);
            return false;
        }
        
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        // Try to update log if we have a log ID
        if (isset($logId)) {
            update_email_log($logId, 'failed', $errorMessage, null, $debugMode ? $debugData : null);
        } else {
            // Log as new entry if we don't have a log ID
            log_email_attempt(
                $to,
                $subject,
                $provider,
                'failed',
                $errorMessage,
                null,
                $debugMode ? $debugData : null
            );
        }
        return false;
    }
}

/**
 * Update an existing email log entry
 */
function update_email_log(
    ?int $logId,
    string $status,
    ?string $errorMessage = null,
    ?array $responseData = null,
    ?array $debugData = null
): bool {
    if (!$logId || !email_logs_table_exists()) {
        return false;
    }
    
    try {
        $sql = "UPDATE email_logs 
                SET status = :status,
                    error_message = :error_message,
                    response_data = :response_data,
                    debug_data = :debug_data,
                    sent_at = :sent_at
                WHERE id = :id";
        
        db_execute($sql, [
            'id' => $logId,
            'status' => $status,
            'error_message' => $errorMessage,
            'response_data' => $responseData ? json_encode($responseData) : null,
            'debug_data' => $debugData ? json_encode($debugData) : null,
            'sent_at' => ($status === 'sent') ? date('Y-m-d H:i:s') : null
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log('Failed to update email log: ' . $e->getMessage());
        return false;
    }
}

/**
 * Send email via SMTP
 */
function send_email_smtp(
    string $to,
    string $from,
    string $subject,
    string $message,
    ?string $htmlMessage,
    array $settings,
    bool $debugMode,
    array &$debugData
): bool {
    $host = $settings['smtp_host'] ?? '';
    $port = (int)($settings['smtp_port'] ?? 587);
    $encryption = $settings['smtp_encryption'] ?? 'tls';
    $username = $settings['smtp_username'] ?? '';
    $password = $settings['smtp_password'] ?? '';
    
    if (empty($host)) {
        if ($debugMode) {
            $debugData['error'] = 'SMTP host not configured';
        }
        return false;
    }
    
    try {
        // Use PHP's mail() function with SMTP configuration
        // For production, consider using PHPMailer or SwiftMailer
        $headers = [
            "From: $from",
            "Reply-To: $from",
            "X-Mailer: PHP/" . phpversion()
        ];
        
        if ($htmlMessage) {
            $boundary = uniqid('boundary_');
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: multipart/alternative; boundary=\"$boundary\"";
            
            $body = "--$boundary\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
            $body .= $message . "\r\n\r\n";
            $body .= "--$boundary\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $body .= $htmlMessage . "\r\n\r\n";
            $body .= "--$boundary--";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
            $body = $message;
        }
        
        $result = mail($to, $subject, $body, implode("\r\n", $headers));
        
        if ($debugMode) {
            $debugData['smtp_host'] = $host;
            $debugData['smtp_port'] = $port;
            $debugData['smtp_encryption'] = $encryption;
            $debugData['mail_result'] = $result;
        }
        
        return $result;
        
    } catch (Exception $e) {
        if ($debugMode) {
            $debugData['exception'] = $e->getMessage();
        }
        return false;
    }
}

/**
 * Send email via SendGrid (placeholder - implement with actual API)
 */
function send_email_sendgrid(
    string $to,
    string $from,
    string $subject,
    string $message,
    ?string $htmlMessage,
    array $settings,
    bool $debugMode,
    array &$debugData
): bool {
    // TODO: Implement SendGrid API
    if ($debugMode) {
        $debugData['note'] = 'SendGrid not yet implemented';
    }
    return false;
}

/**
 * Send email via Mailgun (placeholder - implement with actual API)
 */
function send_email_mailgun(
    string $to,
    string $from,
    string $subject,
    string $message,
    ?string $htmlMessage,
    array $settings,
    bool $debugMode,
    array &$debugData
): bool {
    // TODO: Implement Mailgun API
    if ($debugMode) {
        $debugData['note'] = 'Mailgun not yet implemented';
    }
    return false;
}

/**
 * Send email via Amazon SES (placeholder - implement with actual API)
 */
function send_email_ses(
    string $to,
    string $from,
    string $subject,
    string $message,
    ?string $htmlMessage,
    array $settings,
    bool $debugMode,
    array &$debugData
): bool {
    // TODO: Implement SES API
    if ($debugMode) {
        $debugData['note'] = 'SES not yet implemented';
    }
    return false;
}

/**
 * Send email via Postmark (placeholder - implement with actual API)
 */
function send_email_postmark(
    string $to,
    string $from,
    string $subject,
    string $message,
    ?string $htmlMessage,
    array $settings,
    bool $debugMode,
    array &$debugData
): bool {
    // TODO: Implement Postmark API
    if ($debugMode) {
        $debugData['note'] = 'Postmark not yet implemented';
    }
    return false;
}

