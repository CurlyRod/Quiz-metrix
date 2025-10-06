<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Get user ID from session
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }

    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

    // Get user ID
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

    // Get folder ID
    $folderId = isset($_POST['folder_id']) && !empty($_POST['folder_id']) ? intval($_POST['folder_id']) : null;

    $uploaded = [];
    $errors = [];

    // Function to get unique filename for DB/user display
    function getUniqueFileName($conn, $fileName, $folderId, $user_id) {
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        $newFileName = $fileName;
        $counter = 1;

        $sql = "SELECT COUNT(*) as count FROM files WHERE name = ? AND folder_id <=> ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }

        while (true) {
            $stmt->bind_param("sii", $newFileName, $folderId, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if ($row['count'] == 0) {
                $stmt->close();
                return $newFileName; // found unique name
            }

            // Append (n)
            $newFileName = $baseName . " (" . $counter . ")";
            if (!empty($extension)) {
                $newFileName .= "." . $extension;
            }
            $counter++;
        }
    }

    // Loop through uploaded files
    foreach ($_FILES['files']['name'] as $key => $name) {
        if ($_FILES['files']['error'][$key] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading file: " . $name;
            continue;
        }

        $fileName = sanitizeFilename($name);
        $fileName = getUniqueFileName($conn, $fileName, $folderId, $user_id); // <-- ensure uniqueness
        $fileSize = $_FILES['files']['size'][$key];
        $fileTmpPath = $_FILES['files']['tmp_name'][$key];
        $fileExtension = getFileExtension($fileName);

        // Validate file type
        if (!isAllowedFileType($fileExtension)) {
            $errors[] = "File type not allowed: " . $fileName;
            continue;
        }

        // Always store physical file uniquely
        $uniqueFileName = time() . '_' . uniqid() . '_' . $fileName;
        $uploadPath = '../uploads/' . $uniqueFileName;

        if (move_uploaded_file($fileTmpPath, $uploadPath)) {
            $sql = "INSERT INTO files (name, type, size, folder_id, file_path, user_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $errors[] = "Database prepare error for file: " . $fileName;
                unlink($uploadPath);
                continue;
            }

            $stmt->bind_param('ssissi', $fileName, $fileExtension, $fileSize, $folderId, $uniqueFileName, $user_id);
            if ($stmt->execute()) {
                $uploaded[] = [
                    'file_id' => $stmt->insert_id,
                    'name' => $fileName
                ];
            } else {
                $errors[] = "Error saving file info: " . $fileName . " (" . $stmt->error . ")";
                unlink($uploadPath);
            }
            $stmt->close();
        } else {
            $errors[] = "Error moving file: " . $fileName;
        }
    }

    echo json_encode([
        'success' => !empty($uploaded),
        'message' => !empty($uploaded) ? 'Files uploaded successfully' : 'No valid files uploaded',
        'uploaded' => $uploaded,
        'errors' => $errors
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
