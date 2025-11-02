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

    $deck_id = $_GET['id'] ?? null;

    if (!$deck_id) {
        echo json_encode(['success' => false, 'message' => 'Deck ID is required']);
        exit;
    }

    // Verify ownership
    $verify = $conn->query("SELECT deck_id FROM decks WHERE deck_id=$deck_id AND user_id='$user_id' AND is_deleted=0");
    if (!$verify || $verify->num_rows === 0) {
        throw new Exception('Deck not found or unauthorized');
    }
    
    // Soft delete the deck
    $conn->query("UPDATE decks SET is_deleted=1 WHERE deck_id=$deck_id");
    
    echo json_encode(['success' => true, 'message' => 'Deck deleted successfully']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>