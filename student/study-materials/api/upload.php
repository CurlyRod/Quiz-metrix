<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }

    // Get user ID from session
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }
    
    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

    // Prepare and execute query to get user ID
    $stmt = $conn->prepare("SELECT id FROM user_credential WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Execution error: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
    } else {
        throw new Exception('User not found');
    }
    $stmt->close();

    // Get file information
    $file = $_FILES['file'];
    $fileName = sanitizeFilename($file['name']);
    $fileSize = $file['size'];
    $fileTmpPath = $file['tmp_name'];
    $fileExtension = getFileExtension($fileName);

    // Check if file type is allowed
    if (!isAllowedFileType($fileExtension)) {
        throw new Exception('File type not allowed. Only PDF, DOCX, and TXT files are allowed.');
    }

    // Create unique filename to prevent overwriting
    $uniqueFileName = time() . '_' . $fileName;
    $uploadPath = '../uploads/' . $uniqueFileName;

    // Get folder ID
    $folderId = isset($_POST['folder_id']) && !empty($_POST['folder_id']) ? intval($_POST['folder_id']) : null;

    // Move uploaded file to destination
    if (move_uploaded_file($fileTmpPath, $uploadPath)) {
        // Insert file information into database (including user_id)
        $sql = "INSERT INTO files (name, type, size, folder_id, file_path, user_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }
        
        $stmt->bind_param('ssissi', $fileName, $fileExtension, $fileSize, $folderId, $uniqueFileName, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'File uploaded successfully',
                'file_id' => $stmt->insert_id
            ]);
        } else {
            // Delete the uploaded file if database insert fails
            unlink($uploadPath);
            throw new Exception('Error saving file information to database: ' . $stmt->error);
        }
    } else {
        throw new Exception('Error moving uploaded file');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>