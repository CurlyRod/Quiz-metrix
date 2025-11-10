<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Check if name is provided
    if (!isset($_POST['name']) || empty($_POST['name'])) {
        throw new Exception('Folder name is required');
    }

    // Get user ID from session
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }
    
     $status_stmt = $conn->prepare("SELECT status FROM user_credential WHERE email = ?");
    $status_stmt->bind_param("s", $_SESSION['USER_EMAIL']);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();

    if ($status_result->num_rows === 0 || $status_result->fetch_assoc()['status'] !== 'Active') {
        session_destroy();
        throw new Exception('Your account has been deactivated. Please contact administrator.');
    }
    $status_stmt->close();


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

    // Get folder information
    $folderName = trim($_POST['name']);
    $parentId = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

    // Get max position for the new folder
    $sql = "SELECT MAX(position) as max_pos FROM folders WHERE user_id = ? AND " . 
           ($parentId === null ? "parent_id IS NULL" : "parent_id = ?");
    $stmt = $conn->prepare($sql);
    
    if ($parentId === null) {
        $stmt->bind_param('i', $user_id);
    } else {
        $stmt->bind_param('ii', $user_id, $parentId);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Error getting position: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $position = ($row['max_pos'] !== null) ? $row['max_pos'] + 1 : 0;
    $stmt->close();

    // Insert folder information into database
    $sql = "INSERT INTO folders (name, parent_id, position, user_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    
    $stmt->bind_param('siii', $folderName, $parentId, $position, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Folder created successfully',
            'folder_id' => $stmt->insert_id
        ]);
    } else {
        throw new Exception('Error creating folder: ' . $stmt->error);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>