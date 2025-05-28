<?php
// Include database connection
require_once '../db.php';

// Get parameters from request
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Calculate offset
$offset = ($page - 1) * $limit;

// Prepare the base query
$query = "SELECT id, name, email, date_created FROM user_credential";

// Add search condition if search parameter is provided
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " WHERE name LIKE '%$search%' OR email LIKE '%$search%'";
}

// Add sorting and pagination
$query .= " ORDER BY date_created DESC LIMIT $limit OFFSET $offset";

// Execute the query
$result = $conn->query($query);

// Prepare response array
$response = [
    'success' => false,
    'message' => '',
    'users' => [],
    'pagination' => [
        'currentPage' => $page,
        'totalPages' => 0,
        'totalRecords' => 0,
        'limit' => $limit
    ]
];

// Check for query errors
if (!$result) {
    $response['message'] = 'Database error: ' . $conn->error;
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Process results
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Format the date
        $date = new DateTime($row['date_created']);
        $row['date_created'] = $date->format('M d, Y h:i A');
        
        $response['users'][] = $row;
    }
    $response['success'] = true;
} else {
    $response['message'] = 'No users found';
    $response['success'] = true;
}

// Get total number of records for pagination
$countQuery = "SELECT COUNT(*) as total FROM user_credential";
if (!empty($search)) {
    $countQuery .= " WHERE name LIKE '%$search%' OR email LIKE '%$search%'";
}

$countResult = $conn->query($countQuery);
if ($countResult) {
    $totalRecords = $countResult->fetch_assoc()['total'];
    $response['pagination']['totalRecords'] = $totalRecords;
    $response['pagination']['totalPages'] = ceil($totalRecords / $limit);
} else {
    $response['message'] = 'Error counting records: ' . $conn->error;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

// Close the connection
$conn->close();
?>