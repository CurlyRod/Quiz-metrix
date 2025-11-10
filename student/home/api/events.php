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
    case 'getMonthEvents':
        getMonthEvents($conn);
        break;
    case 'getEventsForDate':
        getEventsForDate($conn);
        break;
    case 'getEvent':
        getEvent($conn);
        break;
    case 'addEvent':
        addEvent($conn);
        break;
    case 'updateEvent':
        updateEvent($conn);
        break;
    case 'deleteEvent':
        deleteEvent($conn);
        break;
    case 'getEventsForDateRange':
        getEventsForDateRange($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// Function to get all events for the current month
function getMonthEvents($conn) {
    // Initialize response
    $response = ['success' => false, 'message' => '', 'events' => []];
    
    try {
        
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

        // Get current month and prepare query
        $currentMonth = date('Y-m');
        $stmt = $conn->prepare("SELECT * FROM events WHERE event_date LIKE ? AND user_id = ? ORDER BY event_date ASC");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $likePattern = $currentMonth . '%';
        $stmt->bind_param("si", $likePattern, $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        $stmt->close();
        
        $response = [
            'success' => true,
            'events' => $events
        ];

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        http_response_code(401); // Unauthorized for auth errors
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

// Function to get events for a date range
function getEventsForDateRange($conn) {
    // Initialize response
    $response = ['success' => false, 'message' => '', 'events' => []];

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

        // Get date range from request
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            throw new Exception('Invalid date format');
        }

        // Get events for the date range
        $stmt = $conn->prepare("SELECT * FROM events WHERE event_date BETWEEN ? AND ? AND user_id = ? ORDER BY event_date ASC");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("ssi", $startDate, $endDate, $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        $stmt->close();
        
        $response = [
            'success' => true,
            'events' => $events
        ];

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        http_response_code(401); // Unauthorized for auth errors
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

// Function to get events for a specific date
function getEventsForDate($conn) {
    // Initialize response
    $response = ['success' => false, 'message' => '', 'events' => []];

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

        // Get date from request
        $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new Exception('Invalid date format');
        }

        // Get events for the date
        $stmt = $conn->prepare("SELECT * FROM events WHERE event_date = ? AND user_id = ? ORDER BY event_date ASC");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("si", $date, $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        $stmt->close();
        
        $response = [
            'success' => true,
            'events' => $events
        ];

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        http_response_code(401); // Unauthorized for auth errors
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

// Function to get a single event
function getEvent($conn) {
    // Initialize response
    $response = ['success' => false, 'message' => '', 'event' => null];

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

        // Get event ID from request
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            throw new Exception('Invalid event ID');
        }

        // Get the event
        $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("ii", $id, $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $response = [
                'success' => true,
                'event' => $result->fetch_assoc()
            ];
        } else {
            throw new Exception('Event not found');
        }
        $stmt->close();

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        // Use 404 for not found, 401 for auth errors
        http_response_code($e->getMessage() === 'Event not found' ? 404 : 401);
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function addEvent($conn) {
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
        
        // Validate required fields (no longer need to check for user_id)
        if (!isset($data['title']) || !isset($data['event_date'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        // Sanitize inputs
        $title = trim($data['title']);
        $description = isset($data['description']) ? trim($data['description']) : '';
        $eventDate = $data['event_date'];

        // Validate date format (optional)
        if (!DateTime::createFromFormat('Y-m-d', $eventDate)) {
            echo json_encode(['success' => false, 'message' => 'Invalid date format']);
            return;
        }

        $stmt = $conn->prepare("INSERT INTO events (user_id, title, description, event_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $description, $eventDate);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Event added successfully', 
                'event_id' => $conn->insert_id
            ]);
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error adding event: ' . $e->getMessage()
        ]);
    }
}

// Function to update an event
function updateEvent($conn) {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['event_id']) || !isset($data['title']) || !isset($data['event_date'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    $eventId = $data['event_id'];
    $title = $data['title'];
    $description = isset($data['description']) ? $data['description'] : '';
    $eventDate = $data['event_date'];
    
    $stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, event_date = ? WHERE event_id = ?");
    $stmt->bind_param("sssi", $title, $description, $eventDate, $eventId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating event: ' . $conn->error]);
    }
}

// Function to delete an event
function deleteEvent($conn) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting event: ' . $conn->error]);
    }
}
?>