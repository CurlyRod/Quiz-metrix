<?php
ob_start();
session_start();

// Initialize response array
$response = ['success' => false, 'message' => ''];

try {
    // Check if user is logged in
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }

    // Try to include db_connect with error handling
    $db_path = '../db_connect.php';
    if (!file_exists($db_path)) {
        throw new Exception('Database connection file not found');
    }
    
    require_once($db_path);
    
    // Check if connection is established
    if (!isset($conn) || !$conn || $conn->connect_error) {
        throw new Exception('Database connection not available');
    }

    $status_stmt = $conn->prepare("SELECT status FROM user_credential WHERE email = ?");
    $status_stmt->bind_param("s", $_SESSION['USER_EMAIL']);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();

    if ($status_result->num_rows === 0 || $status_result->fetch_assoc()['status'] !== 'Active') {
        session_destroy();
        throw new Exception('Your account has been deactivated. Please contact administrator.');
    }
    $status_stmt->close();

    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

    $stmt = $conn->prepare("SELECT id FROM user_credential WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Execution error: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
    } else {
        throw new Exception('User not found');
    }
    $stmt->close();

    // Get file ID from request
    $file_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($file_id <= 0) {
        throw new Exception('Invalid file ID');
    }

    // Get DELETED file data
    $stmt = $conn->prepare("SELECT * FROM files WHERE id = ? AND user_id = ? AND is_deleted = 1");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("ii", $file_id, $user_id);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $file_result = $stmt->get_result();
    if ($file_result->num_rows === 0) {
        throw new Exception('Deleted file not found or access denied');
    }
    
    $file = $file_result->fetch_assoc();
    $stmt->close();

    // Correct file path - assuming uploads is at the same level as api folder
    $file_path = '../uploads/' . $file['file_path'];
    $file_exists = file_exists($file_path);

    $response = [
        'success' => true, 
        'file' => $file,
        'file_path' => $file_path,
        'file_exists' => $file_exists
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(500);
}

ob_clean();
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>