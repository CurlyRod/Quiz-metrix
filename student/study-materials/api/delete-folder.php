<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if folder ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Folder ID is required'
    ]);
    exit;
}

// Get folder ID
$folderId = intval($_POST['id']);

// Get folder information
$sql = "SELECT parent_id FROM folders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $folderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Folder not found'
    ]);
    exit;
}

$folder = $result->fetch_assoc();
$parentId = $folder['parent_id'];

// Begin transaction
$conn->begin_transaction();

try {
    // Update files to move them to parent folder
    $sql = "UPDATE files SET folder_id = ? WHERE folder_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $parentId, $folderId);
    $stmt->execute();
    
    // Update subfolders to move them to parent folder
    $sql = "UPDATE folders SET parent_id = ? WHERE parent_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $parentId, $folderId);
    $stmt->execute();
    
    // Delete the folder
    $sql = "DELETE FROM folders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $folderId);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Folder deleted successfully'
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting folder: ' . $e->getMessage()
    ]);
}
?>
