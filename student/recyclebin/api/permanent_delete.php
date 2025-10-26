<?php
require_once('../db_connect.php');
session_start();

// Initialize response array
$response = ['success' => false, 'message' => ''];

try {
    // Check if user is logged in
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }

    // Get user ID from session
    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

    // Prepare and execute query to get user ID
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

    // Get item ID and type from request
    $item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $item_type = isset($_GET['type']) ? $_GET['type'] : '';

    if ($item_id <= 0) {
        throw new Exception('Invalid item ID');
    }

    // Delete based on item type with user verification
    if ($item_type === 'quiz') {
        $stmt = $conn->prepare("DELETE FROM quizzes WHERE quiz_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $item_id, $user_id);
    } elseif ($item_type === 'file') {
        $stmt = $conn->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $item_id, $user_id);
    } elseif ($item_type === 'note') {
        $stmt = $conn->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $item_id, $user_id);
    } else {
        throw new Exception('Invalid item type');
    }

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'Item permanently deleted successfully';
        } else {
            $response['message'] = 'Item not found or access denied';
        }
    } else {
        throw new Exception($stmt->error);
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400); // Bad request for other errors
}

header('Content-Type: application/json');
echo json_encode($response);

// Close connections if they exist
if (isset($stmt)) $stmt->close();
if (isset($conn)) $conn->close();
?>