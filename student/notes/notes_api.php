<?php
// Include database configuration
require_once 'db_config.php';
session_start();

// Set header to return JSON
header('Content-Type: application/json');

// Handle different actions based on request
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'create':
        createNote();
        break;
    case 'read':
        readNotes();
        break;
    case 'update':
        updateNote();
        break;
    case 'delete':
        deleteNote();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
    

function getUserID($email) {
    global $conn;
    
    // Validate email first
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'isAuthenticate' => false,
            'message' => 'Invalid email format'
        ];
    }

    $stmt = $conn->prepare("SELECT id, email FROM user_credential WHERE email = ?");
    if (!$stmt) {
        return [
            'isAuthenticate' => false,
            'message' => 'Database prepare error: ' . $conn->error
        ];
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        return [
            'isAuthenticate' => false,
            'message' => 'Execution error: ' . $stmt->error
        ];
    }

    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return [
            'isAuthenticate' => true,
            'userinfo' => [
                'email' => $row['email'],
                'id' => $row['id']
            ]
        ];
    } else {
        return [
            'isAuthenticate' => false,
            'message' => 'User not found'
        ];
    }
}

// Create a new note
function createNote() {
    global $conn;
    
    // Get data from POST request
    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $content = isset($_POST['content']) ? $_POST['content'] : '';
    $color = isset($_POST['color']) ? $_POST['color'] : 'default'; // Add color parameter 
    $user_id =  isset($_POST['user-current-id']) ? $_POST['user-current-id'] : '';
    
    // Validate content
    if (empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Note content is required']);
        return;
    }

    // Prepare and execute query
    $stmt = $conn->prepare("INSERT INTO notes (user_id, title, content, color, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $user_id, $title, $content, $color);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Note created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating note: ' . $conn->error]);
    }
    
    $stmt->close();
}

// Read all notes
function readNotes() {
    global $conn;

    $emailUser = $_SESSION['USER_EMAIL'] ?? null;
    $stmt =  getUserID($emailUser);

    $user_id =   $stmt['userinfo']['id'] ?? null;
    if (!$user_id) {
        die("Error: Could not retrieve user ID");
    }


    // Prepare the query correctly
    $stmt = $conn->prepare("SELECT * FROM notes WHERE user_id = ? ORDER BY created_at DESC");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    // Bind and execute
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
        return;
    }
    
    // Get result
    $result = $stmt->get_result();
    $notes = [];
    while ($row = $result->fetch_assoc()) {
        $notes[] = $row;
    }
    
    echo json_encode(['success' => true, 'notes' => $notes]);
    $stmt->close();
}

// Update a note
function updateNote() {
    global $conn;
    
    // Get data from POST request
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $content = isset($_POST['content']) ? $_POST['content'] : '';
    $color = isset($_POST['color']) ? $_POST['color'] : 'default'; // Add color parameter
    
    // Validate data
    if (empty($id) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Note ID and content are required']);
        return;
    }
    
    // Prepare and execute query
    $stmt = $conn->prepare("UPDATE notes SET title = ?, content = ?, color = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("sssi", $title, $content, $color, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Note updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating note: ' . $conn->error]);
    }
    
    $stmt->close();
}

// Delete a note
function deleteNote() {
    global $conn;
    
    // Get data from POST request
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    
    // Validate data
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Note ID is required']);
        return;
    }
    
    // Prepare and execute query
    $stmt = $conn->prepare("DELETE FROM notes WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Note deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting note: ' . $conn->error]);
    }
    
    $stmt->close();
}
?>
