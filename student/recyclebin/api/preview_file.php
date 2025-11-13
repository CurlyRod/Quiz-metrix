<?php
ob_start();
session_start();

// Initialize response
$response = ['success' => false, 'message' => ''];

try {
    // Check if user is logged in
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }

    // Include database connection
    $db_path = '../db_connect.php';
    if (!file_exists($db_path)) {
        throw new Exception('Database connection file not found');
    }
    
    require_once($db_path);
    
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

    // Get file ID from request
    $file_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($file_id <= 0) {
        throw new Exception('Invalid file ID');
    }

    // Get user ID
    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

    $user_stmt = $conn->prepare("SELECT id FROM user_credential WHERE email = ?");
    $user_stmt->bind_param("s", $email);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_result->num_rows > 0) {
        $row = $user_result->fetch_assoc();
        $user_id = $row['id'];
    } else {
        throw new Exception('User not found');
    }
    $user_stmt->close();

    // Get file data (including deleted files for recycle bin)
    $stmt = $conn->prepare("SELECT * FROM files WHERE id = ? AND user_id = ? AND is_deleted = 1");
    $stmt->bind_param("ii", $file_id, $user_id);
    $stmt->execute();
    $file_result = $stmt->get_result();
    
    if ($file_result->num_rows === 0) {
        throw new Exception('File not found or access denied');
    }
    
    $file = $file_result->fetch_assoc();
    $stmt->close();

    // Correct file path - assuming uploads is at the same level as api folder
    $file_path = '../uploads/' . $file['file_path'];
    $file_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Only allow preview for text files
    if ($file_type !== 'txt') {
        throw new Exception('Preview only available for text files');
    }

    // Check if file exists - if not, return a friendly message
    if (!file_exists($file_path)) {
        throw new Exception('File content no longer available on server. The physical file may have been deleted.');
    }

    // Read and output file content
    $content = file_get_contents($file_path);
    if ($content === false) {
        throw new Exception('Unable to read file content');
    }

    // Set appropriate headers for text content
    header('Content-Type: text/plain; charset=utf-8');
    echo $content;
    exit();

} catch (Exception $e) {
    ob_clean();
    header('Content-Type: text/plain');
    echo 'Error: ' . $e->getMessage();
    exit();
}
?>