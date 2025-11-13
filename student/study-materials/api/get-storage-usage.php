<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $storage = getUserStorageUsage();
    
    echo json_encode([
        'success' => true,
        'storage' => $storage
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving storage information'
    ]);
}
?>