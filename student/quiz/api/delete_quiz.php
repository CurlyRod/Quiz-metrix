<?php
require_once('../includes/db_config.php');

// Get quiz ID from request
$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($quiz_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quiz ID']);
    exit;
}

// Soft delete quiz - set is_deleted flag instead of actually deleting (hides the quiz)
$stmt = $conn->prepare("UPDATE quizzes SET is_deleted = 1 WHERE quiz_id = ?");
$stmt->bind_param("i", $quiz_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$conn->close();
?>
