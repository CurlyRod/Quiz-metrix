<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Get JSON data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if (!$data || !isset($data['type']) || !isset($data['id']) || !isset($data['destination'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data'
    ]);
    exit;
}

$type = $data['type'];
$id = intval($data['id']);
$destination = $data['destination'] === 'null' ? null : intval($data['destination']);

// Begin transaction
$conn->begin_transaction();

try {
    // Update the item's parent/folder
    if ($type === 'folder') {
        // Make sure we're not moving a folder into itself or its children
        if ($destination !== null) {
            $currentId = $destination;
            while ($currentId !== null) {
                $sql = "SELECT parent_id FROM folders WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $currentId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    break;
                }
                
                $folder = $result->fetch_assoc();
                
                if ($folder['parent_id'] == $id) {
                    throw new Exception('Cannot move a folder into itself or its children');
                }
                
                $currentId = $folder['parent_id'];
            }
        }
        
        $sql = "UPDATE folders SET parent_id = ? WHERE id = ?";
    } else {
        $sql = "UPDATE files SET folder_id = ? WHERE id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $destination, $id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Item moved successfully'
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error moving item: ' . $e->getMessage()
    ]);
}
?>
