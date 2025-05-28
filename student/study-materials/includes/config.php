<?php
// Database configuration
$host = 'localhost';
$username = 'root'; // Default XAMPP username
$password = ''; // Default XAMPP password is empty
$database = 'quizmetrix'; // Database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . $database;
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);

// Create tables if they don't exist
$sql = "CREATE TABLE IF NOT EXISTS folders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_id INT NULL,
    position INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating folders table: " . $conn->error);
}

$sql = "CREATE TABLE IF NOT EXISTS files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('pdf', 'docx', 'txt') NOT NULL,
    size INT NOT NULL,
    folder_id INT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    file_path VARCHAR(255) NOT NULL,
    position INT DEFAULT 0,
    is_deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating files table: " . $conn->error);
}

// Create uploads directory if it doesn't exist
if (!file_exists('../uploads')) {
    mkdir('../uploads', 0777, true);
}
?>
