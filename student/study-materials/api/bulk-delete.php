<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header("Content-Type: application/json");

// Use whatever variable your db.php provides
if (isset($db)) {
    $pdo = $db;
} elseif (isset($conn)) {
    $pdo = $conn;
} elseif (isset($pdo)) {
    $pdo = $pdo;
} else {
    echo json_encode(["success" => false, "message" => "No DB connection found (db.php must set \$db, \$conn or \$pdo)"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$ids = [];

// Support form-data (ids[])
if (isset($_POST['ids']) && is_array($_POST['ids'])) {
    $ids = array_map('intval', $_POST['ids']);
}

// Support JSON body { "ids": [1,2,3] }
if (empty($ids)) {
    $input = json_decode(file_get_contents("php://input"), true);
    if (isset($input['ids']) && is_array($input['ids'])) {
        $ids = array_map('intval', $input['ids']);
    }
}

if (empty($ids)) {
    echo json_encode(["success" => false, "message" => "No files selected"]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));

try {
    $stmt = $pdo->prepare("UPDATE files SET is_deleted = 1, deleted_at = NOW() WHERE id IN ($placeholders)");
    
    $success = $stmt->execute($ids);

    echo json_encode([
        "success" => $success,
        "deleted" => $ids
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}