<?php
require_once('../db_connect.php');

header('Content-Type: application/json');
session_start();

// Turn off error display to prevent HTML in JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (!isset($_SESSION['USER_EMAIL'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
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

    if (!isset($conn) || !$conn instanceof mysqli) {
        throw new Exception('Database connection not available');
    }
    
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT deck_id, title, description, created_at, updated_at FROM decks WHERE user_id = ? AND is_deleted = 0 ORDER BY updated_at DESC");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $decks = [];
    while ($row = $result->fetch_assoc()) {
        // Use prepared statement for card count too
        $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM flashcards WHERE deck_id = ? AND is_deleted = 0");
        $countStmt->bind_param("i", $row['deck_id']);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $cardCount = $countResult->fetch_assoc()['count'];
        $countStmt->close();
        
        $row['card_count'] = $cardCount;
        $decks[] = $row;
    }
    
    $stmt->close();
    
    echo json_encode(['success' => true, 'decks' => $decks]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>