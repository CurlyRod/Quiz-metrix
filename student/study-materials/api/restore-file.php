<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if file ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'File ID is required'
    ]);
    exit;
}

// Get file ID
$fileId = intval($_POST['id']);

// Restore the file (unmark as deleted)
$sql = "UPDATE files SET is_deleted = 0, deleted_at = NULL WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $fileId);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'File restored successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error restoring file'
    ]);
}
?>
