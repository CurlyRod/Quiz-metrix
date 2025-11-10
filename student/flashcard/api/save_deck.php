<?php
require_once('../includes/db_config.php');

header('Content-Type: application/json');
session_start();

// Validate user session - use the same session check as save_quiz.php
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

// Get user ID from session email - same approach as save_quiz.php
$email = $_SESSION['USER_EMAIL'];
$user_id = null;

try {
    // Prepare and execute query to get user ID - same as save_quiz.php
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

    // Validate input
    if (!isset($data['title']) || empty(trim($data['title']))) {
        echo json_encode(['success' => false, 'message' => 'Title is required']);
        exit;
    }

    if (empty($data['flashcards']) || count($data['flashcards']) < 4) {
        echo json_encode(['success' => false, 'message' => 'At least 4 flashcards are required']);
        exit;
    }

    $title = $conn->real_escape_string(trim($data['title']));
    $description = $conn->real_escape_string(trim($data['description'] ?? ''));
    
    // Handle settings - include showBackOnLoad
    $settings = [
        'studyMode' => $data['settings']['studyMode'] ?? 'sequence',
        'trackProgress' => $data['settings']['trackProgress'] ?? true,
        'showBackOnLoad' => $data['settings']['showBackOnLoad'] ?? false
    ];
    $settings_json = $conn->real_escape_string(json_encode($settings));
    
    $deck_id = $data['id'] ?? $data['deck_id'] ?? null;
    
    if ($deck_id) {
        // Update existing deck - only update if not soft deleted
        $result = $conn->query("SELECT * FROM decks WHERE deck_id=$deck_id AND user_id='$user_id' AND is_deleted=0");
        if ($result->num_rows === 0) {
            throw new Exception('Deck not found or has been deleted');
        }
        
        $conn->query("UPDATE decks SET title='$title', description='$description', settings='$settings_json', updated_at=NOW() WHERE deck_id=$deck_id AND user_id='$user_id' AND is_deleted=0");
        
        // Delete existing flashcards (soft delete)
        $conn->query("UPDATE flashcards SET is_deleted=1 WHERE deck_id=$deck_id");
    } else {
        // Insert new deck
        $conn->query("INSERT INTO decks (user_id, title, description, settings, is_deleted) VALUES ('$user_id', '$title', '$description', '$settings_json', 0)");
        $deck_id = $conn->insert_id;
    }
    
    // Insert flashcards - ensure we don't insert duplicates
    $insertedCards = 0;
    foreach ($data['flashcards'] as $index => $card) {
        $front = $conn->real_escape_string(trim($card['front'] ?? ''));
        $back = $conn->real_escape_string(trim($card['back'] ?? ''));
        
        // Only insert if both front and back have content
        if (!empty($front) && !empty($back)) {
            $conn->query("INSERT INTO flashcards (deck_id, front, back, position, is_deleted) VALUES ($deck_id, '$front', '$back', $index, 0)");
            $insertedCards++;
        }
    }
    
    // Check if we have enough valid flashcards
    if ($insertedCards < 4) {
        // If not enough valid cards, soft delete the deck and return error
        if (!$data['id'] && !$data['deck_id']) {
            $conn->query("UPDATE decks SET is_deleted=1 WHERE deck_id=$deck_id");
        }
        echo json_encode(['success' => false, 'message' => 'At least 4 valid flashcards (with both front and back) are required']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Deck saved successfully',
        'deck_id' => $deck_id
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>