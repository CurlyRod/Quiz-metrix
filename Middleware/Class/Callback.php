<?php 
session_start();  

require './Config.php'; 
use Middleware\Class\Config;   
echo (new Config())->VendorConfig();

// Add your database connection and UserAuthenticate class
require_once '../../student/home/db_connect.php';
require_once "../auth/UserAuthenticate.php";

if (!isset($_SESSION['oauth_state'])) { 
    unset($_SESSION['oauth_state']);
    header("Location: ../../403-Forbidden.html") ; 
    die('Access Denied.'); 
} 

// Verify the state parameter to prevent CSRF.
if (empty($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    die('Access Denied.'); 
} 

// Once verified, unset it to prevent reuse
unset($_SESSION['oauth_state']);

// Ensure the authorization code is present.
if (!isset($_GET['code'])) {
    die('Authorization code not found.');
}
 
$code = $_GET['code'];

// Prepare the token request data.
$tokenRequestData = [
    'client_id'     => CLIENT_ID,
    'scope'         => SCOPES,
    'code'          => $code,
    'redirect_uri'  => REDIRECT_URI,
    'grant_type'    => 'authorization_code',
    'client_secret' => CLIENT_SECRET,
];

// Make the token request using cURL.
$ch = curl_init(TOKEN_ENDPOINT);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenRequestData));
$response = curl_exec($ch);
if ($response === false) {
    die('Curl error: ' . curl_error($ch));
}
curl_close($ch);

$tokenData = json_decode($response, true);
if (!isset($tokenData['access_token'])) {
    die('Failed to get access token.');
}

$accessToken = $tokenData['access_token'];

// Use the access token to retrieve user information from Microsoft Graph.
$ch = curl_init("https://graph.microsoft.com/v1.0/me");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
$userResponse = curl_exec($ch);
curl_close($ch);

$userData = json_decode($userResponse, true);

if (isset($userData['error'])) {
    die("Error retrieving user info: " . $userData['error']['message']);
}

// Optional: Restrict access to a specific organization or school domain.
$allowedDomain = 'alabang.sti.edu.ph';
if (strpos($userData['userPrincipalName'], '@' . $allowedDomain) === false) {
    header('Location: 403-Forbidden.html');
    exit;
}

// Save user information in the session.
$_SESSION['user'] = $userData;
$_SESSION['USER_EMAIL'] = $userData['userPrincipalName'];
$_SESSION['USER_NAME'] = $userData['displayName'];

// NEW: Check user status in database before allowing login
$userAuthenticate = new UserAuthenticate($conn);
$userCheck = $userAuthenticate->GetUserLogin($userData['userPrincipalName']);

// If user exists but is inactive, redirect to 403 page
if ($userCheck['isAuthenticate'] && isset($userCheck['status']) && $userCheck['status'] === 'inactive') {
    header('Location: /403-Forbidden.html');
    exit;
}

// If user doesn't exist in database, register them
if (!$userCheck['isAuthenticate']) {
    $registrationResult = $userAuthenticate->RegisterUser($userData['userPrincipalName']);
    
    // Check if the newly registered user should be blocked (if you want to set new users as inactive by default)
    if ($registrationResult['isAuthenticate'] && isset($registrationResult['status']) && $registrationResult['status'] === 'inactive') {
        header('Location: /403-Forbidden.html');
        exit;
    }
}

// Only redirect to home if user is authenticated AND active
if($_SESSION['user']) {
    header("Location: ../../landing-page/loader.php");
    exit();
}