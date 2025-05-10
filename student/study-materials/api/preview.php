<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if file ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('File ID is required');
}

// Get file ID
$fileId = intval($_GET['id']);

// Get file information
$sql = "SELECT name, type, file_path FROM files WHERE id = ? AND is_deleted = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $fileId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('File not found');
}

$file = $result->fetch_assoc();
$filePath = '../uploads/' . $file['file_path'];

// Check if file exists
if (!file_exists($filePath)) {
    die('File not found on server');
}

// For text files, output the content
if ($file['type'] === 'txt') {
    $content = file_get_contents($filePath);
    echo htmlspecialchars($content);
} else {
    die('Preview not available for this file type');
}
?>
