<?php
// Database connection parameters
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'quizmetrix';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {

    throw new Exception("Connection failed: " . $conn->connect_error);
}

// Set character set to UTF-8
$conn->set_charset("utf8mb4");
?>