<?php
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Generate JWT token
     */
    public function generateJWT($userId, $email) {
        $header = json_encode(['typ' => 'JWT', 'alg' => JWT_ALGORITHM]);
        $payload = json_encode([
            'user_id' => $userId,
            'email' => $email,
            'iat' => time(),
            'exp' => time() + JWT_EXPIRY
        ]);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, JWT_SECRET, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    /**
     * Verify JWT token
     */
    public function verifyJWT($token) {
        error_log("verifyJWT called with token length: " . strlen($token));
        
        $tokenParts = explode('.', $token);
        if (count($tokenParts) !== 3) {
            error_log("JWT verification failed: Invalid token format");
            return false;
        }
        
        $base64Header = $tokenParts[0];
        $base64Payload = $tokenParts[1];
        $base64Signature = $tokenParts[2];
        
        // Verify signature
        $expectedSignature = hash_hmac('sha256', $base64Header . "." . $base64Payload, JWT_SECRET, true);
        $expectedBase64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($expectedSignature));
        
        if (!hash_equals($base64Signature, $expectedBase64Signature)) {
            error_log("JWT verification failed: Invalid signature");
            error_log("Expected: " . $expectedBase64Signature);
            error_log("Received: " . $base64Signature);
            return false;
        }
        
        // Decode payload
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Payload));
        $payloadData = json_decode($payload, true);
        
        if (!$payloadData) {
            error_log("JWT verification failed: Invalid payload JSON");
            return false;
        }
        
        if ($payloadData['exp'] < time()) {
            error_log("JWT verification failed: Token expired");
            return false;
        }
        
        error_log("JWT verification successful");
        return $payloadData;
    }
    
    /**
     * Register new user
     */
    public function register($username, $email, $password) {
        try {
            // Check if user already exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'User already exists'];
            }
            
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
            
            // Insert user
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, email, password_hash) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$username, $email, $passwordHash]);
            
            $userId = $this->pdo->lastInsertId();
            
            // Create user media directory
            $userMediaPath = MEDIA_PATH . $userId;
            if (!file_exists($userMediaPath)) {
                mkdir($userMediaPath, 0755, true);
            }
            
            return [
                'success' => true, 
                'message' => 'User registered successfully',
                'user_id' => $userId
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, email, password_hash, is_active 
                FROM users 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user || !$user['is_active']) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            if (!password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            // Generate JWT token
            $token = $this->generateJWT($user['id'], $user['email']);
            
            // Log the session
            $this->logSession($user['id'], $token);
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'access_token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Login failed'];
        }
    }
    
    /**
     * Get current user from token
     */
    public function getCurrentUser($token) {
        error_log("getCurrentUser called with token: " . substr($token, 0, 20) . "...");
        
        $payload = $this->verifyJWT($token);
        if (!$payload) {
            error_log("JWT verification failed for token");
            return false;
        }
        
        error_log("JWT payload: " . json_encode($payload));
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, email, storage_quota, storage_used, is_active 
                FROM users 
                WHERE id = ? AND is_active = 1
            ");
            $stmt->execute([$payload['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                error_log("User not found in database for ID: " . $payload['user_id']);
            } else {
                error_log("User found: " . $user['username']);
            }
            
            return $user;
        } catch (PDOException $e) {
            error_log("Database error in getCurrentUser: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log user session
     */
    private function logSession($userId, $token) {
        try {
            $tokenHash = hash('sha256', $token);
            $stmt = $this->pdo->prepare("
                INSERT INTO user_sessions (user_id, token_hash, device_info, ip_address, expires_at) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $tokenHash,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                date('Y-m-d H:i:s', time() + JWT_EXPIRY)
            ]);
        } catch (PDOException $e) {
            // Silent fail for session logging
        }
    }
    
    /**
     * Logout user (invalidate token)
     */
    public function logout($token) {
        try {
            $tokenHash = hash('sha256', $token);
            $stmt = $this->pdo->prepare("DELETE FROM user_sessions WHERE token_hash = ?");
            $stmt->execute([$tokenHash]);
            return ['success' => true, 'message' => 'Logged out successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Logout failed'];
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Verify current password
            $stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            // Update password
            $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
            $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$newPasswordHash, $userId]);
            
            return ['success' => true, 'message' => 'Password changed successfully'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Password change failed'];
        }
    }
    
    /**
     * Validate user permissions
     */
    public function validateUserAccess($userId, $resourceUserId = null) {
        if ($resourceUserId === null) {
            return true; // No specific resource user, just validate token
        }
        
        return $userId == $resourceUserId; // User can only access their own resources
    }
} 