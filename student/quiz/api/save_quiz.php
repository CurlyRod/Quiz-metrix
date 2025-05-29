<?php
require_once('../includes/db_config.php');

// Start session and check authentication
session_start();

if (!isset($_SESSION['USER_EMAIL'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Get the JSON data from the request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data format']);
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

    // Start transaction
    $conn->begin_transaction();

    // Prepare quiz data
    $title = $conn->real_escape_string($data['title']);
    $description = $conn->real_escape_string($data['description']);
    $settings = json_encode($data['settings']);
    
    // Check if we're updating an existing quiz
    $quiz_id = isset($data['quiz_id']) ? intval($data['quiz_id']) : 0;
    
    if ($quiz_id > 0) {
        // First verify the quiz belongs to this user
        $check_stmt = $conn->prepare("SELECT quiz_id FROM quizzes WHERE quiz_id = ? AND user_id = ?");
        $check_stmt->bind_param("ii", $quiz_id, $user_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows === 0) {
            throw new Exception("Quiz not found or you don't have permission to edit it");
        }
        $check_stmt->close();
        
        // Update existing quiz
        $stmt = $conn->prepare("UPDATE quizzes SET title = ?, description = ?, settings = ? WHERE quiz_id = ? AND user_id = ?");
        $stmt->bind_param("sssii", $title, $description, $settings, $quiz_id, $user_id);
        $stmt->execute();
        
        // Delete existing questions to replace with new ones
        $delete_stmt = $conn->prepare("DELETE FROM questions WHERE quiz_id = ?");
        $delete_stmt->bind_param("i", $quiz_id);
        $delete_stmt->execute();
        $delete_stmt->close();
    } else {
        // Insert new quiz with user_id
        $stmt = $conn->prepare("INSERT INTO quizzes (user_id, title, description, settings) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $description, $settings);
        $stmt->execute();
        $quiz_id = $conn->insert_id;
    }
    
    // Insert questions
    if (!empty($data['questions'])) {
        $stmt = $conn->prepare("INSERT INTO questions (quiz_id, term, description, answer_type, question_order) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($data['questions'] as $index => $question) {
            $term = $conn->real_escape_string($question['term']);
            $desc = $conn->real_escape_string($question['description']);
            $answer_type = $conn->real_escape_string($question['answerType'] ?? 'multiple');
            $order = $index + 1;
            
            $stmt->bind_param("isssi", $quiz_id, $term, $desc, $answer_type, $order);
            $stmt->execute();
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'quiz_id' => $quiz_id]);
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && method_exists($conn, 'rollback')) {
        $conn->rollback();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>