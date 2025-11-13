<?php
// Get the environment variables provided by Railway
$db_host = getenv('MYSQLHOST');
$db_port = getenv('MYSQLPORT');
$db_name = getenv('MYSQLDATABASE');
$db_user = getenv('MYSQLUSER');
$db_pass = getenv('MYSQLPASSWORD');

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";

// Create uploads directory if it doesn't exist
if (!file_exists('../uploads')) {
    mkdir('../uploads', 0777, true);
}
?>
