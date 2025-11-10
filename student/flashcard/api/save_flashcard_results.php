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

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['session_id']) || !isset($data['deck_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $session_id = $conn->real_escape_string($data['session_id']);
    $deck_id = $data['deck_id'];
    $total_cards = $data['total_cards'] ?? 0;
    $known_count = $data['known_count'] ?? 0;
    $unknown_count = $data['unknown_count'] ?? 0;
    $mode = $data['mode'] ?? 'sequence';
    $percent_known = $total_cards > 0 ? round(($known_count / $total_cards) * 100) : 0;
    
    $conn->query("INSERT INTO flashcard_results (user_id, deck_id, session_id, total_cards, known_count, unknown_count, percent_known, mode) 
                  VALUES ('$user_id', $deck_id, '$session_id', $total_cards, $known_count, $unknown_count, $percent_known, '$mode')");
    
    echo json_encode(['success' => true, 'result_id' => $conn->insert_id]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>