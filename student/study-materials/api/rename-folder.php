<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if required parameters are provided
if (!isset($_POST['folder_id']) || !isset($_POST['name']) || empty($_POST['name'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Folder ID and name are required'
    ]);
    exit;
}

// Get parameters
$folderId = intval($_POST['folder_id']);
$folderName = trim($_POST['name']);

// Update folder name in database
$sql = "UPDATE folders SET name = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $folderName, $folderId);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Folder renamed successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error renaming folder'
    ]);
}
?>
