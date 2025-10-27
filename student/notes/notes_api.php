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
    case 'bulk_delete':
        bulkDeleteNotes();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// Create a new note
function createNote() {
    global $conn;
    
    try {
        // Check if user is logged in - same method as get_deleted.php
        if (!isset($_SESSION['USER_EMAIL'])) {
            throw new Exception('User not authenticated');
        }
        
        // Get user ID from session - same method as get_deleted.php
        $email = $_SESSION['USER_EMAIL'];
        $user_id = null;

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

        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $color = isset($_POST['color']) ? trim($_POST['color']) : 'default';
        
        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Note content is required']);
            return;
        }

        $stmt = $conn->prepare("INSERT INTO notes (user_id, title, content, color, created_at, is_deleted) VALUES (?, ?, ?, ?, NOW(), 0)");
        $stmt->bind_param("isss", $user_id, $title, $content, $color);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Note created successfully',
                'note_id' => $conn->insert_id
            ]);
        } else {
            throw new Exception('Database error: ' . $conn->error);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error creating note: ' . $e->getMessage()
        ]);
    } finally {
        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }
    }
}

// Read all notes
function readNotes() {
    global $conn;

    try {
        // Check if user is logged in - same method as get_deleted.php
        if (!isset($_SESSION['USER_EMAIL'])) {
            throw new Exception('User not authenticated');
        }

        // Get user ID from session - same method as get_deleted.php
        $email = $_SESSION['USER_EMAIL'];
        $user_id = null;

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

        // If an ID is provided, fetch a single note
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $note_id = intval($_GET['id']);

            $stmt = $conn->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ? AND is_deleted = 0");
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }

            $stmt->bind_param("ii", $note_id, $user_id);
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }

            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                echo json_encode(['success' => true, 'note' => $row]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Note not found']);
            }
            $stmt->close();
            return;
        }

        // Otherwise, fetch all notes
        $stmt = $conn->prepare("SELECT * FROM notes WHERE user_id = ? AND is_deleted = 0 ORDER BY created_at DESC");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        $notes = [];
        while ($row = $result->fetch_assoc()) {
            $notes[] = $row;
        }

        echo json_encode(['success' => true, 'notes' => $notes]);
        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Update a note
function updateNote() {
    global $conn;
    
    try {
        // Check if user is logged in - same method as get_deleted.php
        if (!isset($_SESSION['USER_EMAIL'])) {
            throw new Exception('User not authenticated');
        }

        // Get user ID from session - same method as get_deleted.php
        $email = $_SESSION['USER_EMAIL'];
        $user_id = null;

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

        $id = isset($_POST['id']) ? $_POST['id'] : '';
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        $content = isset($_POST['content']) ? $_POST['content'] : '';
        $color = isset($_POST['color']) ? $_POST['color'] : 'default';
        
        if (empty($id) || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Note ID and content are required']);
            return;
        }
        
        $stmt = $conn->prepare("UPDATE notes SET title = ?, content = ?, color = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssii", $title, $content, $color, $id, $user_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Note updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Note not found or access denied']);
            }
        } else {
            throw new Exception('Error updating note: ' . $conn->error);
        }
        
        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Delete a note
function deleteNote() {
    global $conn;
    
    try {
        // Check if user is logged in - same method as get_deleted.php
        if (!isset($_SESSION['USER_EMAIL'])) {
            throw new Exception('User not authenticated');
        }

        // Get user ID from session - same method as get_deleted.php
        $email = $_SESSION['USER_EMAIL'];
        $user_id = null;

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

        $id = isset($_POST['id']) ? $_POST['id'] : '';
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Note ID is required']);
            return;
        }
        
        $stmt = $conn->prepare("UPDATE notes SET is_deleted = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Note moved to trash successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Note not found or access denied']);
            }
        } else {
            throw new Exception('Error deleting note: ' . $conn->error);
        }
        
        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Bulk Delete Notes
function bulkDeleteNotes() {
    global $conn;

    try {
        // Check if user is logged in - same method as get_deleted.php
        if (!isset($_SESSION['USER_EMAIL'])) {
            throw new Exception('User not authenticated');
        }

        // Get user ID from session - same method as get_deleted.php
        $email = $_SESSION['USER_EMAIL'];
        $user_id = null;

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

        $ids = isset($_POST['ids']) ? json_decode($_POST['ids'], true) : [];

        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'message' => 'No IDs provided']);
            return;
        }

        $placeholders = implode(",", array_fill(0, count($ids), "?"));
        $stmt = $conn->prepare("UPDATE notes SET is_deleted = 1 WHERE id IN ($placeholders) AND user_id = ?");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $types = str_repeat("i", count($ids)) . "i"; 
        $params = array_merge($ids, [$user_id]);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Notes moved to trash successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No notes found or access denied']);
            }
        } else {
            throw new Exception('Error deleting notes: ' . $stmt->error);
        }

        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>