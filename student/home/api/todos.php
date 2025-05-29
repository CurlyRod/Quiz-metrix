<?php
// Database connection
require_once '../db_connect.php';
session_start();

// Set headers
header('Content-Type: application/json');

// Get action from request
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle different actions
switch ($action) {
    case 'getTasks':
        getTasks($conn);
        break;
    case 'addTask':
        addTask($conn);
        break;
    case 'updateTask':
        updateTask($conn);
        break;
    case 'deleteTask':
        deleteTask($conn);
        break;
    case 'deleteCompletedTasks':
        deleteCompletedTasks($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getTasks($conn) {
    // Initialize response array
    $response = [
        'success' => false,
        'message' => '',
        'data' => []
    ];

    try {
        // Set content type header
        header('Content-Type: application/json');

        // Get user ID from session
        if (!isset($_SESSION['USER_EMAIL'])) {
            throw new Exception('User not authenticated', 401);
        }
        
        $email = $_SESSION['USER_EMAIL'];
        $user_id = null;

        // Get user ID
        $stmt = $conn->prepare("SELECT id FROM user_credential WHERE email = ?");
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error, 500);
        }

        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            throw new Exception('Execution error: ' . $stmt->error, 500);
        }

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception('User not found', 404);
        }
        
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
        $stmt->close();

        // Get tasks for this user
        $stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error, 500);
        }

        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error, 500);
        }

        $result = $stmt->get_result();
        $tasks = [];
        
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
        
        $stmt->close();

        // Successful response
        $response = [
            'success' => true,
            'data' => $tasks
        ];
        
    } catch (Exception $e) {
        // Error response
        http_response_code($e->getCode() ?: 500);
        $response['message'] = $e->getMessage();
    }
    
    // Single JSON output
    echo json_encode($response);
    exit;
}

// Function to add a new task
function addTask($conn) {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    try {
        // Get user ID from session
        if (!isset($_SESSION['USER_EMAIL'])) {
            throw new Exception('User not authenticated');
        }
        
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

        // Get JSON data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required field (only content needed now)
        if (!isset($data['content'])) {
            echo json_encode(['success' => false, 'message' => 'Missing task content']);
            return;
        }

        // Sanitize input
        $content = trim($data['content']);

        $stmt = $conn->prepare("INSERT INTO tasks (user_id, content) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $content);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Task added successfully', 
                'task_id' => $conn->insert_id
            ]);
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error adding task: ' . $e->getMessage()
        ]);
    }
}

// Function to update a task
function updateTask($conn) {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['task_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    $taskId = intval($data['task_id']);
    $isCompleted = isset($data['is_completed']) ? intval($data['is_completed']) : 0;
    
    $stmt = $conn->prepare("UPDATE tasks SET is_completed = ? WHERE task_id = ?");
    $stmt->bind_param("ii", $isCompleted, $taskId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Task updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating task: ' . $conn->error]);
    }
}

// Function to delete a task
function deleteTask($conn) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    $stmt = $conn->prepare("DELETE FROM tasks WHERE task_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Task deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting task: ' . $conn->error]);
    }
}

// Function to delete all completed tasks
function deleteCompletedTasks($conn) {
    $stmt = $conn->prepare("DELETE FROM tasks WHERE is_completed = 1");
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Completed tasks deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting completed tasks: ' . $conn->error]);
    }
}
?>