<?php
// Include database configuration
require_once '../db_config.php';
session_start();

// Set header to return JSON
header('Content-Type: application/json');

function checkAndUpdateExtractionLimit($conn, $user_id) {
    $EXTRACTION_LIMIT = 2; // Max extractions per week per user ( change this value for testing purposes )
    
    // Check existing limit record
    $stmt = $conn->prepare("SELECT extraction_count, last_reset_date FROM user_extraction_limits WHERE user_id = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception('Execution error: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $current_time = time();
    
    if ($result->num_rows > 0) {
        // User has existing record
        $row = $result->fetch_assoc();
        $extraction_count = $row['extraction_count'];
        $last_reset_date = strtotime($row['last_reset_date']);
        
        // Check if it's past Monday 8:00 AM since last reset
        $last_reset_monday_8am = strtotime('last monday 8:00', $current_time);
        if ($last_reset_date < $last_reset_monday_8am) {
            // Reset the counter (it's a new week)
            $extraction_count = 0;
            $update_stmt = $conn->prepare("UPDATE user_extraction_limits SET extraction_count = 0, last_reset_date = NOW() WHERE user_id = ?");
            $update_stmt->bind_param("i", $user_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
    } else {
        // First time user - create record
        $extraction_count = 0;
        $insert_stmt = $conn->prepare("INSERT INTO user_extraction_limits (user_id, extraction_count) VALUES (?, 0)");
        $insert_stmt->bind_param("i", $user_id);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    $stmt->close();
    
    // Check if user has extractions remaining
    if ($extraction_count >= $EXTRACTION_LIMIT) {
        return [
            'allowed' => false,
            'remaining' => 0,
            'limit' => $EXTRACTION_LIMIT
        ];
    }
    
    return [
        'allowed' => true,
        'remaining' => $EXTRACTION_LIMIT - $extraction_count,
        'limit' => $EXTRACTION_LIMIT
    ];
}

function incrementExtractionCount($conn, $user_id) {
    $stmt = $conn->prepare("UPDATE user_extraction_limits SET extraction_count = extraction_count + 1, updated_at = NOW() WHERE user_id = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception('Execution error: ' . $stmt->error);
    }
    
    $stmt->close();
}

// Handle different actions based on request
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

try {
    // Your existing session check code
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }
    
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
    
    // Handle different actions
    switch($action) {
        case 'check_limit':
            // Check extraction limit only
            $limit_info = checkAndUpdateExtractionLimit($conn, $user_id);
            echo json_encode([
                'success' => true,
                'allowed' => $limit_info['allowed'],
                'remaining' => $limit_info['remaining'],
                'limit' => $limit_info['limit']
            ]);
            break;
            
        case 'increment':
            // Increment extraction count
            incrementExtractionCount($conn, $user_id);
            $limit_info = checkAndUpdateExtractionLimit($conn, $user_id);
            echo json_encode([
                'success' => true,
                'remaining' => $limit_info['remaining'],
                'limit' => $limit_info['limit']
            ]);
            break;
            
        default:
            // Default action: check limit and return info
            $limit_info = checkAndUpdateExtractionLimit($conn, $user_id);
            echo json_encode([
                'success' => true,
                'allowed' => $limit_info['allowed'],
                'remaining' => $limit_info['remaining'],
                'limit' => $limit_info['limit']
            ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>