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

    // Get quiz ID from request
    $quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($quiz_id <= 0) {
        throw new Exception('Invalid quiz ID');
    }

    // Get DELETED quiz data - MODIFIED to allow is_deleted = 1
    $stmt = $conn->prepare("SELECT * FROM quizzes WHERE quiz_id = ? AND user_id = ? AND is_deleted = 1");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("ii", $quiz_id, $user_id);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $quiz_result = $stmt->get_result();
    if ($quiz_result->num_rows === 0) {
        throw new Exception('Deleted quiz not found or access denied');
    }
    
    $quiz = $quiz_result->fetch_assoc();
    $quiz['settings'] = json_decode($quiz['settings'], true);
    $stmt->close();

    // Get questions from the deleted quiz
    $stmt = $conn->prepare("SELECT question_id, quiz_id, term, description, answer_type, question_order FROM questions WHERE quiz_id = ? ORDER BY question_order");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $quiz_id);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $questions_result = $stmt->get_result();
    $questions = [];
    while ($row = $questions_result->fetch_assoc()) {
        $questions[] = [
            'term' => $row['term'],
            'description' => $row['description'],
            'answerType' => $row['answer_type'],
            'question_id' => $row['question_id'],
            'question_order' => $row['question_order']
        ];
    }
    $stmt->close();

    $quiz['questions'] = $questions;
    $response = ['success' => true, 'quiz' => $quiz];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(500);
}

ob_clean();
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>