<?php
/**
 * Helper Functions
 */

// Start secure session
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_strict_mode', 1);
        session_start();
    }
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Generate random string
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length));
}

// Check if user is logged in
function isLoggedIn() {
    startSecureSession();
    return isset($_SESSION['user_id']);
}

// Check if admin is logged in
function isAdminLoggedIn() {
    startSecureSession();
    return isset($_SESSION['admin_id']);
}

// Redirect to login if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Redirect to admin login if not logged in
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ../admin/login.php');
        exit;
    }
}

// Get current user ID
function getCurrentUserId() {
    startSecureSession();
    return $_SESSION['user_id'] ?? null;
}

// Get current admin ID
function getCurrentAdminId() {
    startSecureSession();
    return $_SESSION['admin_id'] ?? null;
}

// Format currency
function formatCurrency($amount, $symbol = 'USDT') {
    return number_format((float)$amount, 2) . ' ' . $symbol;
}

// Format date
function formatDate($date) {
    return date('M d, Y H:i', strtotime($date));
}

// Get status badge class
function getStatusBadgeClass($status) {
    $classes = [
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'processing' => 'info',
        'completed' => 'success',
        'active' => 'success',
        'inactive' => 'secondary'
    ];
    return $classes[strtolower($status)] ?? 'secondary';
}

// Upload file
function uploadFile($file, $directory) {
    $allowedExtensions = ALLOWED_EXTENSIONS;
    $maxFileSize = MAX_FILE_SIZE;
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error occurred'];
    }
    
    // Check file size
    if ($file['size'] > $maxFileSize) {
        return ['success' => false, 'message' => 'File size exceeds limit'];
    }
    
    // Check extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = UPLOAD_DIR . $directory . '/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to save file'];
}

// Send notification (placeholder for email/SMS)
function sendNotification($userId, $type, $message) {
    // Log notification to database
    // Could be extended to send emails or SMS
    return true;
}

// CSRF Token Generation
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateRandomString(32);
    }
    return $_SESSION['csrf_token'];
}

// CSRF Token Verification
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Prevent SQL Injection
function escapeString($pdo, $string) {
    return $pdo->quote($string);
}

// Calculate profit
function calculateProfit($amount, $percentage, $days = 1) {
    return ($amount * $percentage / 100) * $days;
}

// Get referral commission
function getReferralCommission($amount, $type = 'deposit') {
    if ($type === 'deposit') {
        return calculateProfit($amount, DEPOSIT_REFERRAL_COMMISSION);
    } elseif ($type === 'investment') {
        return calculateProfit($amount, INVESTMENT_REFERRAL_COMMISSION);
    }
    return 0;
}
?>
