<?php
/**
 * Logout Handler
 */

session_start();
require_once '../backend/config/app.php';

// Log activity if user is logged in
if (isset($_SESSION['user_id'])) {
    try {
        $db = new Database('owner');
        $compressed = Compression::compress([
            'action' => 'logout',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        $db->query(
            "INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at)
             VALUES (?, 'logout', ?, ?, ?, ?)",
            [
                $_SESSION['user_id'],
                $compressed,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                time()
            ]
        );
    } catch (Exception $e) {
        // Silent fail - don't prevent logout
    }
}

// Destroy session
session_destroy();

// Redirect to login
header('Location: login.php?logged_out=1');
exit;
