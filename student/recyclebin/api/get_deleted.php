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

    // Rest of your code remains the same...
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

    // Get DELETED quizzes for this user
    $quizzes = [];
    $stmt = $conn->prepare("SELECT quiz_id, title, updated_at FROM quizzes WHERE user_id = ? AND is_deleted = 1 ORDER BY updated_at DESC");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $quizzes[] = $row;
            }
        }
        $stmt->close();
    }

    // Get DELETED files for this user
    $files = [];
    $stmt = $conn->prepare("SELECT id, name, type, size, deleted_at FROM files WHERE user_id = ? AND is_deleted = 1 ORDER BY deleted_at DESC");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $files[] = $row;
            }
        }
        $stmt->close();
    }

    // Get DELETED notes for this user - use updated_at since deleted_at doesn't exist
    $notes = [];
    $stmt = $conn->prepare("SELECT id, title, created_at, updated_at FROM notes WHERE user_id = ? AND is_deleted = 1 ORDER BY updated_at DESC");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $notes[] = $row;
            }
        }
        $stmt->close();
    }

    // Get DELETED flashcards for this user
    $flashcards = [];
    $stmt = $conn->prepare("SELECT deck_id, title, created_at, updated_at FROM decks WHERE user_id = ? AND is_deleted = 1 ORDER BY updated_at DESC");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $flashcards[] = $row;
            }
        }
        $stmt->close();
    }

    $response = [
        'success' => true,
        'quizzes' => $quizzes,
        'files' => $files,
        'notes' => $notes,
        'flashcards' => $flashcards
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