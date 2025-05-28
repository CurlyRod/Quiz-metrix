<?php
// Include database connection
require_once '../db.php';

// Query to count total users
$query = "SELECT COUNT(*) as total FROM user_credential";
$result = $conn->query($query);

// Get the count
$count = 0;
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $count = $row['total'];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['total' => $count]);

// Close the connection
$conn->close();
?>