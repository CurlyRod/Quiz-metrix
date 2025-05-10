<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'message' => 'No file uploaded or upload error'
    ]);
    exit;
}

// Get file information
$file = $_FILES['file'];
$fileName = sanitizeFilename($file['name']);
$fileSize = $file['size'];
$fileTmpPath = $file['tmp_name'];
$fileExtension = getFileExtension($fileName);

// Check if file type is allowed
if (!isAllowedFileType($fileExtension)) {
    echo json_encode([
        'success' => false,
        'message' => 'File type not allowed. Only PDF, DOCX, and TXT files are allowed.'
    ]);
    exit;
}

// Create unique filename to prevent overwriting
$uniqueFileName = time() . '_' . $fileName;
$uploadPath = '../uploads/' . $uniqueFileName;

// Get folder ID
$folderId = isset($_POST['folder_id']) && !empty($_POST['folder_id']) ? intval($_POST['folder_id']) : null;

// Move uploaded file to destination
if (move_uploaded_file($fileTmpPath, $uploadPath)) {
    // Insert file information into database
    $sql = "INSERT INTO files (name, type, size, folder_id, file_path) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssiss', $fileName, $fileExtension, $fileSize, $folderId, $uniqueFileName);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'File uploaded successfully',
            'file_id' => $stmt->insert_id
        ]);
    } else {
        // Delete the uploaded file if database insert fails
        unlink($uploadPath);
        
        echo json_encode([
            'success' => false,
            'message' => 'Error saving file information to database'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error moving uploaded file'
    ]);
}
?>
