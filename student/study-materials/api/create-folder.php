<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if name is provided
if (!isset($_POST['name']) || empty($_POST['name'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Folder name is required'
    ]);
    exit;
}

// Get folder information
$folderName = trim($_POST['name']);
$parentId = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

// Get max position for the new folder
$sql = "SELECT MAX(position) as max_pos FROM folders WHERE " . 
       ($parentId === null ? "parent_id IS NULL" : "parent_id = $parentId");
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$position = ($row['max_pos'] !== null) ? $row['max_pos'] + 1 : 0;

// Insert folder information into database
$sql = "INSERT INTO folders (name, parent_id, position) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sii', $folderName, $parentId, $position);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Folder created successfully',
        'folder_id' => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error creating folder'
    ]);
}
?>
