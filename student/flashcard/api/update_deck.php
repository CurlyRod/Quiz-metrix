<?php
require_once('../includes/db_config.php');

header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['USER_EMAIL'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
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

    if (!isset($data['id']) || !isset($data['title']) || !isset($data['flashcards'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $deckId = $data['id'];
    $title = trim($data['title']);
    $description = trim($data['description'] ?? '');
    $flashcards = $data['flashcards'];
    $settings = $data['settings'] ?? [];

    // Verify deck ownership and check if not soft deleted
    $result = $conn->query("SELECT deck_id FROM decks WHERE deck_id=$deckId AND user_id='$user_id' AND is_deleted=0");
    if (!$result || $result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Deck not found or access denied']);
        exit;
    }
    
    // Handle settings
    $settings_json = $conn->real_escape_string(json_encode([
        'studyMode' => $settings['studyMode'] ?? 'sequence',
        'trackProgress' => $settings['trackProgress'] ?? true,
        'showBackOnLoad' => $settings['showBackOnLoad'] ?? false
    ]));
    
    $title_escaped = $conn->real_escape_string($title);
    $description_escaped = $conn->real_escape_string($description);
    
    // Update deck
    $conn->query("UPDATE decks SET title='$title_escaped', description='$description_escaped', settings='$settings_json', updated_at=NOW() WHERE deck_id=$deckId");
    
    // Soft delete existing flashcards for this deck (we'll recreate them)
    $conn->query("UPDATE flashcards SET is_deleted=1 WHERE deck_id=$deckId");
    
    // Insert updated flashcards
    $insertedCards = 0;
    foreach ($flashcards as $index => $card) {
        $front = $conn->real_escape_string(trim($card['front'] ?? ''));
        $back = $conn->real_escape_string(trim($card['back'] ?? ''));
        
        // Only insert if both front and back have content
        if (!empty($front) && !empty($back)) {
            $conn->query("INSERT INTO flashcards (deck_id, front, back, position, is_deleted) VALUES ($deckId, '$front', '$back', $index, 0)");
            $insertedCards++;
        }
    }
    
    // Check if we have enough valid flashcards
    if ($insertedCards < 4) {
        // If not enough valid cards, rollback by soft deleting the new flashcards and the deck
        $conn->query("UPDATE flashcards SET is_deleted=1 WHERE deck_id=$deckId");
        $conn->query("UPDATE decks SET is_deleted=1 WHERE deck_id=$deckId");
        echo json_encode(['success' => false, 'message' => 'At least 4 valid flashcards (with both front and back) are required']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Deck updated successfully',
        'deck_id' => $deckId
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>