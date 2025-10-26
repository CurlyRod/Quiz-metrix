<?php
require_once('../includes/db_config.php');
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

    // Get quizzes for this user
    $stmt = $conn->prepare("SELECT quiz_id, title, description, created_at, updated_at FROM quizzes WHERE user_id = ? AND is_deleted = 0 ORDER BY updated_at DESC");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $quizzes = [];
    while ($row = $result->fetch_assoc()) {
        $quizzes[] = $row;
    }

    $response = [
        'success' => true,
        'quizzes' => $quizzes,
        'user_id' => $user_id // For debugging
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(401); // Unauthorized for auth errors
}

header('Content-Type: application/json');
echo json_encode($response);

// Close connections if they exist
if (isset($stmt)) $stmt->close();
if (isset($conn)) $conn->close();
?>