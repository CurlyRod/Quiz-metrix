<?php
ob_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if file ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('File ID is required');
}

// Get file ID
$fileId = intval($_GET['id']);

// Get file information
$sql = "SELECT name, type, file_path, size FROM files WHERE id = ? AND is_deleted = 0";
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

while (ob_get_level()) {
    ob_end_clean();
}

$mimeTypes = [
    'pdf' => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'txt' => 'text/plain'
];

$mimeType = isset($mimeTypes[$file['type']]) ? $mimeTypes[$file['type']] : 'application/octet-stream';

$filename = $file['name'];
$encodedFilename = rawurlencode($filename);

header('Content-Description: File Transfer');
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $encodedFilename);
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', '1');
}
@ini_set('zlib.output_compression', 'Off');

// Flush system output buffer
flush();

// Read file and output to browser
readfile($filePath);
exit;
?>