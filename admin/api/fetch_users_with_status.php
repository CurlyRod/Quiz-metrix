<?php
// Add error handling at the top
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    // Include database connection with proper path
    require_once __DIR__ . '/../home/db.php';
    
    // Get parameters from request
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : 'both';

    // Calculate offset
    $offset = ($page - 1) * $limit;

    // Prepare the base query
    $query = "SELECT id, name, email, status, date_created FROM user_credential";
    $whereConditions = [];

    // Add search condition if search parameter is provided
    if (!empty($search)) {
        $search = $conn->real_escape_string($search);
        $whereConditions[] = "(name LIKE '%$search%' OR email LIKE '%$search%')";
    }

    // Add status filter condition
    if ($status !== 'both') {
        $status = $conn->real_escape_string($status);
        $whereConditions[] = "status = '$status'";
    }

    // Combine where conditions
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(" AND ", $whereConditions);
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
        throw new Exception('Database error: ' . $conn->error);
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
    if (!empty($whereConditions)) {
        $countQuery .= " WHERE " . implode(" AND ", $whereConditions);
    }

    $countResult = $conn->query($countQuery);
    if ($countResult) {
        $totalRecords = $countResult->fetch_assoc()['total'];
        $response['pagination']['totalRecords'] = $totalRecords;
        $response['pagination']['totalPages'] = ceil($totalRecords / $limit);
    } else {
        throw new Exception('Error counting records: ' . $conn->error);
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'users' => [],
        'pagination' => [
            'currentPage' => $page ?? 1,
            'totalPages' => 0,
            'totalRecords' => 0,
            'limit' => $limit ?? 10
        ]
    ];
}

// Return JSON response
echo json_encode($response);

// Close the connection
if (isset($conn)) {
    $conn->close();
}
?>