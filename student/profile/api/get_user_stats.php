<?php
require_once('../db_connect.php');
session_start();

// Initialize response array
$response = ['success' => false, 'message' => ''];

try {
    // Check if user is logged in
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }

    // Get user ID from session
    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

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

    // Get quiz statistics
    // Quizzes created by user
    $stmt = $conn->prepare("SELECT COUNT(*) as created_count FROM quizzes WHERE user_id = ? AND is_deleted = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $quizzes_created = $result->fetch_assoc()['created_count'];
    $stmt->close();

    // Quizzes taken - count ALL result_id for quizzes created by this user
    $stmt = $conn->prepare("
        SELECT COUNT(r.result_id) as taken_count 
        FROM results r 
        INNER JOIN quizzes q ON r.quiz_id = q.quiz_id 
        WHERE q.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $quizzes_taken = $result->fetch_assoc()['taken_count'];
    $stmt->close();

    // Quiz accuracy - calculate percentage for each result first, then average
    $stmt = $conn->prepare("
        SELECT r.score, r.total_questions
        FROM results r 
        INNER JOIN quizzes q ON r.quiz_id = q.quiz_id 
        WHERE q.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total_percentage = 0;
    $result_count = 0;
    
    while ($row = $result->fetch_assoc()) {
        $raw_score = $row['score'];
        $total_questions = $row['total_questions'];
        
        // Calculate percentage: (score / total_questions) * 100
        if ($total_questions > 0) {
            $percentage = ($raw_score / $total_questions) * 100;
            $total_percentage += $percentage;
            $result_count++;
        }
    }
    $stmt->close();
    
    // Calculate average accuracy
    $quiz_accuracy = $result_count > 0 ? round($total_percentage / $result_count, 1) : 0;

    // Files uploaded
    $stmt = $conn->prepare("SELECT COUNT(*) as files_count FROM files WHERE user_id = ? AND is_deleted = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $files_uploaded = $result->fetch_assoc()['files_count'];
    $stmt->close();

    // Notes created - count all notes for this user
    $stmt = $conn->prepare("SELECT COUNT(*) as notes_count FROM notes WHERE user_id = ? AND is_deleted = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notes_created = $result->fetch_assoc()['notes_count'];
    $stmt->close();

    $response = [
        'success' => true,
        'stats' => [
            'quizzes_created' => (int)$quizzes_created,
            'quizzes_taken' => (int)$quizzes_taken,
            'quiz_accuracy' => (float)$quiz_accuracy,
            'files_uploaded' => (int)$files_uploaded,
            'notes_created' => (int)$notes_created
        ]
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(401); // Unauthorized for auth errors
}

// Clear any previous output and set proper headers
if (ob_get_length()) ob_clean();
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>