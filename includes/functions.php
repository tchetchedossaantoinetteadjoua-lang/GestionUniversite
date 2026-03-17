<?php
// includes/functions.php

session_start();

// Utility for formatting dates
function formatDate($dateString) {
    if (!$dateString) return '';
    return date('d/m/Y', strtotime($dateString));
}

// Security: Prevent XSS
function escape($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

// Generate CSRF Token
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        // Fallback for PHP < 7.0 using openssl_random_pseudo_bytes
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF Token
function verifyCsrfToken($token) {
    if (isset($_SESSION['csrf_token'])) {
        // Fallback for PHP < 5.6
        if (function_exists('hash_equals')) {
            return hash_equals($_SESSION['csrf_token'], $token);
        } else {
            return $_SESSION['csrf_token'] === $token;
        }
    }
    return false;
}

// Redirect helper
function redirect($url) {
    header("Location: $url");
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Require a specific role
function requireRole($role) {
    if (!isLoggedIn() || $_SESSION['role'] !== $role) {
        // Log unauthorized access attempt could go here
        $_SESSION['error_message'] = "Accès non autorisé.";
        redirect('/GestionUniversite/index.php');
    }
}

// System Logger function
function logAction($conn, $user_id, $action, $details = null) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $query = "INSERT INTO system_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id, $action, $details, $ip]);
}

// Toast message helper
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'info', 'warning'
        'message' => $message
    ];
}

function displayFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        echo "<div class='alert alert-{$type} flash-message'>{$message}</div>";
        unset($_SESSION['flash']);
    }
}
?>
