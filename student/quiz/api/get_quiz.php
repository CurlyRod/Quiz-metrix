<?php
$con = mysqli_connect("localhost", "root", "", "quizmetrix");
require_once('../includes/db_config.php');

// Get quiz ID from request
$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($quiz_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quiz ID']);
    exit;
}

// Get quiz data
$quiz_result = $conn->query("SELECT * FROM quizzes WHERE quiz_id = $quiz_id");

if ($quiz_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Quiz not found']);
    exit;
}

$quiz = $quiz_result->fetch_assoc();
$quiz['settings'] = json_decode($quiz['settings'], true);

// Get questions
$questions_result = $conn->query("SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY question_order");

$questions = [];
if ($questions_result->num_rows > 0) {
    while ($row = $questions_result->fetch_assoc()) {
        $questions[] = [
            'term' => $row['term'],
            'description' => $row['description'],
            'answerType' => $row['answer_type'],
            'options' => [] // Will be populated on the client side
        ];
    }
}

$quiz['questions'] = $questions;

echo json_encode(['success' => true, 'quiz' => $quiz]);

$conn->close();
