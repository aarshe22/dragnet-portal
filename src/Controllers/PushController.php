<?php

namespace DragNet\Controllers;

use DragNet\Core\Database;

/**
 * Push Notifications Controller
 */
class PushController extends BaseController
{
    /**
     * Subscribe to push notifications
     */
    public function subscribe(): array
    {
        $this->requireTenant();
        
        $endpoint = $this->input('endpoint');
        $p256dh = $this->input('keys.p256dh');
        $auth = $this->input('keys.auth');
        $platform = $this->input('platform');
        
        if (!$endpoint || !$p256dh || !$auth) {
            return $this->json(['error' => 'Invalid subscription data'], 400);
        }
        
        $context = $this->app->getTenantContext();
        $db = $this->app->getDatabase();
        
        // Check if subscription already exists
        $existing = $db->fetchOne(
            "SELECT id FROM push_subscriptions WHERE user_id = :user_id AND endpoint = :endpoint",
            ['user_id' => $context->getUserId(), 'endpoint' => $endpoint]
        );
        
        if ($existing) {
            // Update existing
            $db->execute(
                "UPDATE push_subscriptions SET p256dh_key = :p256dh, auth_key = :auth, platform = :platform, updated_at = NOW() WHERE id = :id",
                [
                    'id' => $existing['id'],
                    'p256dh' => $p256dh,
                    'auth' => $auth,
                    'platform' => $platform,
                ]
            );
            return $this->json(['message' => 'Subscription updated']);
        }
        
        // Create new subscription
        $db->execute(
            "INSERT INTO push_subscriptions (user_id, endpoint, p256dh_key, auth_key, platform, user_agent) VALUES (:user_id, :endpoint, :p256dh, :auth, :platform, :user_agent)",
            [
                'user_id' => $context->getUserId(),
                'endpoint' => $endpoint,
                'p256dh' => $p256dh,
                'auth' => $auth,
                'platform' => $platform,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ]
        );
        
        return $this->json(['message' => 'Subscribed to push notifications']);
    }
    
    /**
     * Unsubscribe from push notifications
     */
    public function unsubscribe(): array
    {
        $this->requireTenant();
        
        $endpoint = $this->input('endpoint');
        if (!$endpoint) {
            return $this->json(['error' => 'Endpoint required'], 400);
        }
        
        $context = $this->app->getTenantContext();
        $db = $this->app->getDatabase();
        
        $db->execute(
            "DELETE FROM push_subscriptions WHERE user_id = :user_id AND endpoint = :endpoint",
            ['user_id' => $context->getUserId(), 'endpoint' => $endpoint]
        );
        
        return $this->json(['message' => 'Unsubscribed from push notifications']);
    }
}

