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

    // Get note ID from request
    $note_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($note_id <= 0) {
        throw new Exception('Invalid note ID');
    }

    // Get DELETED note data
    $stmt = $conn->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ? AND is_deleted = 1");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("ii", $note_id, $user_id);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $note_result = $stmt->get_result();
    if ($note_result->num_rows === 0) {
        throw new Exception('Deleted note not found or access denied');
    }
    
    $note = $note_result->fetch_assoc();
    $stmt->close();

    $response = ['success' => true, 'note' => $note];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(500);
}

ob_clean();
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>