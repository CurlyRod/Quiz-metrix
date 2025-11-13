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

    // Get deck ID from request
    $deck_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($deck_id <= 0) {
        throw new Exception('Invalid deck ID');
    }

    // Get DELETED deck data
    $stmt = $conn->prepare("SELECT * FROM decks WHERE deck_id = ? AND user_id = ? AND is_deleted = 1");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("ii", $deck_id, $user_id);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $deck_result = $stmt->get_result();
    if ($deck_result->num_rows === 0) {
        throw new Exception('Deleted deck not found or access denied');
    }
    
    $deck = $deck_result->fetch_assoc();
    $stmt->close();

    // Get ALL flashcards from the deck (regardless of is_deleted status)
    // When a deck is deleted, the flashcards might not be individually marked as deleted
    $stmt = $conn->prepare("SELECT * FROM flashcards WHERE deck_id = ? ORDER BY position ASC");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $deck_id);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $flashcards_result = $stmt->get_result();
    $flashcards = [];
    while ($row = $flashcards_result->fetch_assoc()) {
        $flashcards[] = [
            'front' => $row['front'],
            'back' => $row['back'],
            'position' => $row['position'],
            'flashcard_id' => $row['flashcard_id']
        ];
    }
    $stmt->close();

    // Handle settings
    if (isset($deck['settings']) && !empty($deck['settings'])) {
        $deck['settings'] = json_decode($deck['settings'], true);
        $deck['settings'] = [
            'studyMode' => $deck['settings']['studyMode'] ?? 'sequence',
            'trackProgress' => $deck['settings']['trackProgress'] ?? true,
            'showBackOnLoad' => $deck['settings']['showBackOnLoad'] ?? false
        ];
    } else {
        $deck['settings'] = [
            'studyMode' => 'sequence',
            'trackProgress' => true,
            'showBackOnLoad' => false
        ];
    }

    $deck['flashcards'] = $flashcards;
    $response = ['success' => true, 'deck' => $deck];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Error in get_deleted_deck.php: " . $e->getMessage());
    http_response_code(500);
}

ob_clean();
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>