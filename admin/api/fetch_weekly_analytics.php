<?php
// Add error handling at the top
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    // Include database connection with proper path
    require_once __DIR__ . '/../home/db.php';

    // Get month parameter
    $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

    // Validate month format (YYYY-MM)
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        throw new Exception('Invalid month format');
    }

    // Parse the month to get start and end dates
    $date = DateTime::createFromFormat('Y-m', $month);
    $startDate = $date->format('Y-m-01');
    $endDate = $date->format('Y-m-t');

    // Query to get weekly registration counts
    $query = "SELECT 
                DATE_FORMAT(date_created, '%Y-%m-%d') as week,
                COUNT(*) as count
              FROM user_credential
              WHERE DATE(date_created) BETWEEN ? AND ?
              GROUP BY WEEK(date_created), DATE(date_created)
              ORDER BY date_created ASC";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $response = [
        'success' => true,
        'data' => $data
    ];

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'data' => []
    ];
}

// Return JSON response
echo json_encode($response);

// Close the connection
if (isset($conn)) {
    $conn->close();
}
?>