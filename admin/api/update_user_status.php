<?php
// Add error handling at the top
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once 'check_admin_session.php'; 
try {
    // Include database connection with proper path
    require_once __DIR__ . '/../home/db.php';

    // Start session and check if admin is logged in
    session_start();
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        throw new Exception('Unauthorized');
    }

    // Get parameters from POST request
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    // Validate inputs
    if ($user_id <= 0 || empty($status)) {
        throw new Exception('Invalid parameters');
    }

    // Validate status value
    if (!in_array($status, ['Active', 'Inactive'])) {
        throw new Exception('Invalid status value');
    }

    // Update user status
    $query = "UPDATE user_credential SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $stmt->bind_param("si", $status, $user_id);
    $result = $stmt->execute();

    if ($result) {
        $response = [
            'success' => true, 
            'message' => 'User status updated successfully'
        ];
    } else {
        throw new Exception('Error updating user status: ' . $stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    $response = [
        'success' => false, 
        'message' => $e->getMessage()
    ];
}

// Return JSON response
echo json_encode($response);

// Close the connection
if (isset($conn)) {
    $conn->close();
}
?>