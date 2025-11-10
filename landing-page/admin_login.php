<?php
// admin_login.php - DEBUG VERSION
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

function sendResponse($success, $message, $debug = null) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($debug !== null) {
        $response['debug'] = $debug;
    }
    
    echo json_encode($response);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.');
}

// Get input
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// Log the received data
error_log("Login attempt - Username: '$username', Password length: " . strlen($password));

// Basic validation
if (empty($username) || empty($password)) {
    sendResponse(false, 'Username and password are required.');
}

try {
    // Include the AdminAuth class
    $adminAuthPath = __DIR__ . '../../middleware/Class/AdminAuth.php';
    
    // Debug file path
    error_log("AdminAuth path: " . $adminAuthPath);
    error_log("File exists: " . (file_exists($adminAuthPath) ? 'YES' : 'NO'));
    
    if (!file_exists($adminAuthPath)) {
        sendResponse(false, 'Authentication system error.', [
            'file_exists' => false, 
            'path' => $adminAuthPath,
            'current_dir' => __DIR__
        ]);
    }

    require_once $adminAuthPath;
    
    // Debug: Check if class exists
    if (!class_exists('Middleware\Class\AdminAuth')) {
        sendResponse(false, 'AdminAuth class not found.', [
            'loaded_classes' => get_declared_classes()
        ]);
    }
    
    // Create AdminAuth instance
    $adminAuth = new Middleware\Class\AdminAuth();
    
    // Attempt authentication
    $authResult = $adminAuth->authenticate($username, $password);
    error_log("Authentication result: " . ($authResult ? 'SUCCESS' : 'FAILED'));
    
    if ($authResult) {
        sendResponse(true, 'Login successful!', [
            'username' => $username,
            'session_id' => session_id(),
            'session_status' => session_status()
        ]);
    } else {
        sendResponse(false, 'Invalid username or password.', [
            'username' => $username,
            'password_provided' => !empty($password),
            'session_status' => session_status()
        ]);
    }

} catch (Exception $e) {
    error_log("Login exception: " . $e->getMessage());
    sendResponse(false, 'Authentication failed. Please try again.', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>