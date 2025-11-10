<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

try {
    require_once '../../config/database.php';
    require_once "UserAuthenticate.php";
    
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        throw new Exception('Direct access not allowed');
    }
    
    if (isset($_POST['action']) && $_POST['action'] === "check-users" && isset($_SESSION['USER_EMAIL'])) {
        $database = new Database();
        $conn = $database->getConnection();
        $userAuthenticate = new UserAuthenticate($conn);
        
        $email = $_SESSION['USER_EMAIL'];
        $checkUser = $userAuthenticate->GetUserLogin($email);
        
        if ($checkUser['isAuthenticate'] && isset($checkUser['status']) && $checkUser['status'] === 'inactive') {
            echo json_encode([
                'isAuthenticate' => false,
                'message' => 'Account inactive',
                'redirect' => '../Middleware/auth/403-Forbidden.html'
            ]);
        } else {
            echo json_encode($checkUser);
        }
    } else {
        echo json_encode([
            'isAuthenticate' => false,
            'message' => 'Invalid request'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'isAuthenticate' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>