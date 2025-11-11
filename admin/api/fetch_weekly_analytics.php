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
    
    // Get total days in month
    $totalDays = $date->format('t');
    $daysPerWeek = ceil($totalDays / 4);

    // Create 4 weekly periods
    $weeklyData = [];
    for ($week = 1; $week <= 4; $week++) {
        $weekStartDay = (($week - 1) * $daysPerWeek) + 1;
        $weekEndDay = min($week * $daysPerWeek, $totalDays);
        
        $weekStartDate = $date->format('Y-m-') . str_pad($weekStartDay, 2, '0', STR_PAD_LEFT);
        $weekEndDate = $date->format('Y-m-') . str_pad($weekEndDay, 2, '0', STR_PAD_LEFT);
        
        // Query to get registrations for this weekly period
        $query = "SELECT COUNT(*) as count 
                  FROM user_credential 
                  WHERE DATE(date_created) BETWEEN ? AND ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $weekStartDate, $weekEndDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $weeklyData[] = [
            'week_number' => $week,
            'week_start' => $weekStartDate,
            'week_end' => $weekEndDate,
            'week_display' => "Week $week: " . date('M j', strtotime($weekStartDate)) . ' - ' . date('M j', strtotime($weekEndDate)),
            'count' => (int)$row['count']
        ];
        
        $stmt->close();
    }

    $response = [
        'success' => true,
        'data' => $weeklyData
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