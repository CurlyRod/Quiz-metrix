<?php
require_once('../db_connect.php');
session_start();

// Initialize response array
$response = ['success' => false, 'message' => ''];

try {
    // Check if user is logged in
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

    // Get user ID from session
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

    // Get month and year from request
    $month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

    // Calculate weeks for the requested month
    $weeks = [];
    $week_accuracies = [];

    // Get first and last day of the month
    $first_day = date("{$year}-{$month}-01");
    $last_day = date("Y-m-t", strtotime($first_day));

    // Create exactly 4 weeks (Week 1-4) with 7 days each
    for ($week = 1; $week <= 4; $week++) {
        // Calculate week start and end dates
        $week_start_day = (($week - 1) * 7) + 1;
        $week_end_day = $week_start_day + 6;
        
        // Make sure we don't go beyond the last day of the month
        $last_day_of_month = date('t', strtotime($first_day));
        if ($week_end_day > $last_day_of_month) {
            $week_end_day = $last_day_of_month;
        }
        
        $week_start = date('Y-m-d', strtotime("{$year}-{$month}-{$week_start_day}"));
        $week_end = date('Y-m-d', strtotime("{$year}-{$month}-{$week_end_day}"));

        // Get accuracy for this week
        $stmt = $conn->prepare("
            SELECT AVG((r.score / r.total_questions) * 100) as week_accuracy
            FROM results r 
            INNER JOIN quizzes q ON r.quiz_id = q.quiz_id 
            WHERE q.user_id = ? 
            AND DATE(r.completed_at) BETWEEN ? AND ?
        ");
        $stmt->bind_param("iss", $user_id, $week_start, $week_end);
        $stmt->execute();
        $result = $stmt->get_result();
        $accuracy_row = $result->fetch_assoc();
        $week_accuracy = $accuracy_row['week_accuracy'] ? round($accuracy_row['week_accuracy'], 1) : 0;
        $stmt->close();

        $weeks[] = "Week " . $week;
        $week_accuracies[] = $week_accuracy;

        // If we've reached the end of the month, break early
        if ($week_end_day >= $last_day_of_month) {
            break;
        }
    }

    // If we have less than 4 weeks, fill the remaining weeks with 0
    while (count($weeks) < 4) {
        $weeks[] = "Week " . (count($weeks) + 1);
        $week_accuracies[] = 0;
    }

    $response = [
        'success' => true,
        'month' => $month,
        'year' => $year,
        'weeks' => $weeks,
        'accuracy' => $week_accuracies
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(401);
}

// Clear any previous output and set proper headers
if (ob_get_length()) ob_clean();
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>