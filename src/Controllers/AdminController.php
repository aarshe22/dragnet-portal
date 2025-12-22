<?php

namespace DragNet\Controllers;

use DragNet\Models\User;

/**
 * Administration Controller
 */
class AdminController extends BaseController
{
    /**
     * Show admin dashboard
     */
    public function index(): string
    {
        $this->requireTenant();
        $this->requireRole('Administrator');
        
        return $this->view('admin/index');
    }
    
    /**
     * Show users management page
     */
    public function users(): string
    {
        $this->requireTenant();
        $this->requireRole('Administrator');
        
        return $this->view('admin/users');
    }
    
    /**
     * List users
     */
    public function listUsers(): array
    {
        $this->requireTenant();
        $this->requireRole('Administrator');
        
        $userModel = new User($this->app);
        $users = $userModel->findAll([], 'email ASC');
        
        return $this->json($users);
    }
    
    /**
     * Create user
     */
    public function createUser(): array
    {
        $this->requireTenant();
        $this->requireRole('Administrator');
        
        $data = [
            'email' => $this->input('email'),
            'role' => $this->input('role', 'Guest'),
        ];
        
        if (empty($data['email'])) {
            return $this->json(['error' => 'Email is required'], 400);
        }
        
        $userModel = new User($this->app);
        
        // Check if user already exists
        $existing = $userModel->findByEmail($data['email']);
        if ($existing) {
            return $this->json(['error' => 'User already exists'], 400);
        }
        
        $id = $userModel->create($data);
        
        return $this->json(['id' => $id, 'message' => 'User created']);
    }
    
    /**
     * Update user
     */
    public function updateUser(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('Administrator');
        
        $id = (int)($params['id'] ?? 0);
        $userModel = new User($this->app);
        
        if (!$userModel->find($id)) {
            return $this->json(['error' => 'User not found'], 404);
        }
        
        $data = [];
        if ($this->input('email') !== null) {
            $data['email'] = $this->input('email');
        }
        if ($this->input('role') !== null) {
            $data['role'] = $this->input('role');
        }
        
        if (empty($data)) {
            return $this->json(['error' => 'No data to update'], 400);
        }
        
        $userModel->update($id, $data);
        
        return $this->json(['message' => 'User updated']);
    }
    
    /**
     * Delete user
     */
    public function deleteUser(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('Administrator');
        
        $id = (int)($params['id'] ?? 0);
        $userModel = new User($this->app);
        
        if (!$userModel->find($id)) {
            return $this->json(['error' => 'User not found'], 404);
        }
        
        $userModel->delete($id);
        
        return $this->json(['message' => 'User deleted']);
    }
    
    /**
     * Get system settings
     */
    public function getSettings(): array
    {
        $this->requireTenant();
        $this->requireRole('Administrator');
        
        // Placeholder - actual settings would come from database
        return $this->json([
            'alert_config' => [],
            'system_defaults' => [],
        ]);
    }
    
    /**
     * Update system settings
     */
    public function updateSettings(): array
    {
        $this->requireTenant();
        $this->requireRole('Administrator');
        
        // Placeholder - actual settings update would go here
        return $this->json(['message' => 'Settings updated']);
    }
}

