<?php
/**
 * Session Management & Authentication Helpers
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    return isLoggedIn() ? (int)$_SESSION['user_id'] : null;
}

/**
 * Get current username
 * @return string|null
 */
function getCurrentUsername() {
    return isLoggedIn() ? $_SESSION['username'] : null;
}

/**
 * Get current user email
 * @return string|null
 */
function getCurrentUserEmail() {
    return isLoggedIn() ? $_SESSION['email'] : null;
}

/**
 * Check if current user is admin
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get current user role
 * @return string|null
 */
function getCurrentUserRole() {
    return isLoggedIn() ? $_SESSION['role'] : null;
}

/**
 * Require user to be logged in - redirects to login if not
 * @param string $redirect_url URL to redirect to after login
 */
function requireLogin($redirect_url = null) {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $redirect_url ?: $_SERVER['REQUEST_URI'];
        header('Location: /login.php');
        exit;
    }
}

/**
 * Require user to be admin - redirects if not
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
        header('Location: /homepage.php');
        exit;
    }
}

/**
 * Log in a user
 * @param array $user User data from database
 */
function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in_at'] = time();
}

/**
 * Log out current user
 */
function logoutUser() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Set flash message
 * @param string $type Message type (success, error, warning, info)
 * @param string $message The message content
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_messages'][$type][] = $message;
}

/**
 * Get and clear flash messages
 * @param string|null $type Specific type or null for all
 * @return array
 */
function getFlashMessages($type = null) {
    $messages = [];
    if ($type) {
        if (isset($_SESSION['flash_messages'][$type])) {
            $messages = $_SESSION['flash_messages'][$type];
            unset($_SESSION['flash_messages'][$type]);
        }
    } else {
        if (isset($_SESSION['flash_messages'])) {
            $messages = $_SESSION['flash_messages'];
            unset($_SESSION['flash_messages']);
        }
    }
    return $messages;
}

/**
 * Display flash messages as HTML
 * @return string HTML output
 */
function displayFlashMessages() {
    $messages = getFlashMessages();
    $html = '';
    
    $alertClasses = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    
    foreach ($messages as $type => $typeMessages) {
        $alertClass = $alertClasses[$type] ?? 'alert-info';
        foreach ($typeMessages as $message) {
            $html .= '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
            $html .= htmlspecialchars($message);
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            $html .= '</div>';
        }
    }
    
    return $html;
}

/**
 * Sanitize input
 * @param string $data
 * @return string
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF Token generation and validation
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}
?>

