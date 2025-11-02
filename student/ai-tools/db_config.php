<?php
// Database configuration
$host = 'localhost';
$db_name = 'quizmetrix'; // Database name
$username = 'root';
$password = ''; // Default XAMPP password is empty

// Create connection
$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");
?>