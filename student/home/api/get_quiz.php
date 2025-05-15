<?php
require_once('../db_connect.php');

// Get all quizzes
$result = $conn->query("SELECT quiz_id, title, description, created_at, updated_at FROM quizzes ORDER BY updated_at DESC");

$quizzes = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $quizzes[] = $row;
    }
}

echo json_encode(['success' => true, 'quizzes' => $quizzes]);

$conn->close();
?>
