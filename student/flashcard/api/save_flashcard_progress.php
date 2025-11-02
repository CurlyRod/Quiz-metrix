<?php
require_once('../includes/db_config.php');
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['USER_EMAIL'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get user ID from session email
$email = $_SESSION['USER_EMAIL'];
$user_id = null;

try {
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

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['session_id']) || !isset($data['deck_id']) || !isset($data['flashcard_id']) || !isset($data['status'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $session_id = $conn->real_escape_string($data['session_id']);
    $deck_id = $data['deck_id'];
    $flashcard_id = $data['flashcard_id'];
    $status = $data['status'];
    
    $conn->query("INSERT INTO flashcard_progress (user_id, deck_id, session_id, flashcard_id, status) 
                  VALUES ('$user_id', $deck_id, '$session_id', $flashcard_id, '$status')
                  ON DUPLICATE KEY UPDATE status='$status'");
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>