<?php
require_once('../includes/db_config.php');

header('Content-Type: application/json');
session_start();

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

    $deck_id = $_GET['id'] ?? null;

    if (!$deck_id) {
        echo json_encode(['success' => false, 'message' => 'Deck ID is required']);
        exit;
    }

    // Only fetch decks that are not soft deleted
    $result = $conn->query("SELECT * FROM decks WHERE deck_id=$deck_id AND user_id='$user_id' AND is_deleted = 0");
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception('Deck not found');
    }
    
    $deck = $result->fetch_assoc();
    
    // Handle settings with showBackOnLoad
    if (isset($deck['settings']) && !empty($deck['settings'])) {
        $deck['settings'] = json_decode($deck['settings'], true);
        // Ensure all settings fields exist with defaults
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
    
    // Only fetch flashcards that are not soft deleted
    $cardsResult = $conn->query("SELECT * FROM flashcards WHERE deck_id=$deck_id AND is_deleted=0 ORDER BY position ASC");
    $deck['flashcards'] = [];
    
    while ($card = $cardsResult->fetch_assoc()) {
        $deck['flashcards'][] = $card;
    }
    
    echo json_encode(['success' => true, 'deck' => $deck]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>