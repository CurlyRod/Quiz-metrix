<?php
session_start(); 
require_once '../../student/home/db_connect.php';
require_once "../auth/UserAuthenticate.php"; 

if (
    !isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(403);
    echo json_encode([
        'isAuthenticate' => false,
        'message' => 'Forbidden: Direct access not allowed.'
    ]);
    exit;
}

header('Content-Type: application/json');  
$userAuthenticate = new UserAuthenticate($conn); 

if (
    isset($_POST['action']) && $_POST['action'] === "check-users" &&
    isset($_SESSION['USER_EMAIL'])
) {
    $email = $_SESSION['USER_EMAIL'];
    $username = $_SESSION['USER_NAME']
    $checkUser = $userAuthenticate->GetUserLogin($email); 
    echo json_encode($checkUser); 
    exit;
} 
else
 {
    echo json_encode([
        'isAuthenticate' => false,
        'message' => 'Invalid request or session not set.'
    ]);
    exit;
}