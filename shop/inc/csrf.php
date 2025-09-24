<?php
/**
 * CSRF Protection Helper
 * Provides secure CSRF token generation, validation, and session management
 */

/**
 * Initialize secure session with proper cookie flags
 * Call this before any session operations
 */
function csrf_init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session cookie parameters
        session_set_cookie_params([
            'lifetime' => 0, // Session cookie (expires when browser closes)
            'path' => '/',
            'domain' => '', // Use current domain
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // Secure flag if HTTPS
            'httponly' => true, // Prevent XSS access to session cookie
            'samesite' => 'Lax' // CSRF protection via SameSite (Strict for high-security)
        ]);
        
        session_start();
        
        // Initialize CSRF token storage if not exists
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
    }
}

/**
 * Generate CSRF token for a specific form
 * @param string $formName Unique identifier for the form
 * @return string Generated CSRF token
 */
function csrf_token($formName) {
    csrf_init_session();
    
    // Generate cryptographically secure random token
    $token = bin2hex(random_bytes(32));
    
    // Store token in session with form name as key
    $_SESSION['csrf_tokens'][$formName] = $token;
    
    return $token;
}

/**
 * Generate HTML hidden input field with CSRF token
 * @param string $formName Unique identifier for the form
 * @return void Echoes the hidden input field
 */
function csrf_field($formName) {
    $token = csrf_token($formName);
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}

/**
 * Validate CSRF token for a specific form
 * @param string $formName Unique identifier for the form
 * @param string|null $submittedToken Token submitted with the form
 * @return bool True if token is valid, false otherwise
 */
function csrf_validate($formName, $submittedToken) {
    csrf_init_session();
    
    // Check if token exists for this form
    if (!isset($_SESSION['csrf_tokens'][$formName])) {
        return false;
    }
    
    $expectedToken = $_SESSION['csrf_tokens'][$formName];
    
    // Remove token after validation (single-use)
    unset($_SESSION['csrf_tokens'][$formName]);
    
    // Constant-time comparison to prevent timing attacks
    return $submittedToken !== null && hash_equals($expectedToken, $submittedToken);
}

/**
 * Handle CSRF validation failure
 * @param string $message Optional custom error message
 * @return void Sends 403 response and exits
 */
function csrf_fail($message = 'Invalid CSRF token. Please try again.') {
    http_response_code(403);
    header('Content-Type: text/html; charset=UTF-8');
    
    echo '<!DOCTYPE html>
<html>
<head>
    <title>Security Error</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; text-align: center; }
        .error { color: #d32f2f; background: #ffebee; padding: 20px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="error">
        <h1>Security Error</h1>
        <p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>
        <p><a href="javascript:history.back()">Go Back</a></p>
    </div>
</body>
</html>';
    exit;
}

/**
 * Regenerate session ID (call after successful login)
 * @return void
 */
function csrf_regenerate_session() {
    csrf_init_session();
    session_regenerate_id(true);
}

/**
 * Get CSRF token for AJAX requests
 * @param string $formName Unique identifier for the form/action
 * @return string JSON response with token
 */
function csrf_ajax_token($formName) {
    header('Content-Type: application/json');
    return json_encode(['csrf_token' => csrf_token($formName)]);
}
?>