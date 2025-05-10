<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Get JSON data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if (!$data || !isset($data['type']) || !isset($data['positions']) || !is_array($data['positions'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data'
    ]);
    exit;
}

$type = $data['type'];
$positions = $data['positions'];

// Begin transaction
$conn->begin_transaction();

try {
    // Update positions
    if ($type === 'folder') {
        $sql = "UPDATE folders SET position = ? WHERE id = ?";
    } else {
        $sql = "UPDATE files SET position = ? WHERE id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    
    foreach ($positions as $index => $item) {
        $position = $index;
        $id = $item['id'];
        
        $stmt->bind_param('ii', $position, $id);
        $stmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Positions updated successfully'
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error updating positions: ' . $e->getMessage()
    ]);
}
?>
