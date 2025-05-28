<?php
require_once 'config.php';
session_start();

/**
 * Helper function to get user ID from session
 */
function getUserId() {
    global $conn;
    
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
    
    return $user_id;
}

/**
 * Get all files and folders for a specific parent folder
 */
function getFilesAndFolders($folderId = null) {
    global $conn;
    
    $result = array(
        'folders' => array(),
        'files' => array()
    );
    
    try {
        $user_id = getUserId();
        
        // Get folders
        $folderSql = "SELECT * FROM folders WHERE parent_id " . 
                     ($folderId === null ? "IS NULL" : "= ?") . 
                     " AND user_id = ? ORDER BY position ASC";
        $folderStmt = $conn->prepare($folderSql);
        
        if ($folderStmt) {
            if ($folderId === null) {
                $folderStmt->bind_param("i", $user_id);
            } else {
                $folderStmt->bind_param("ii", $folderId, $user_id);
            }
            
            $folderStmt->execute();
            $folderResult = $folderStmt->get_result();
            
            if ($folderResult && $folderResult->num_rows > 0) {
                while($row = $folderResult->fetch_assoc()) {
                    $result['folders'][] = $row;
                }
            }
            $folderStmt->close();
        }
        
        // Get files (not deleted)
        $fileSql = "SELECT * FROM files WHERE folder_id " . 
                ($folderId === null ? "IS NULL" : "= ?") . 
                " AND is_deleted = 0 AND user_id = ? ORDER BY position ASC";
        $fileStmt = $conn->prepare($fileSql);

        if ($fileStmt) {
            if ($folderId === null) {
                $fileStmt->bind_param("i", $user_id);
            } else {
                $fileStmt->bind_param("ii", $folderId, $user_id);
            }
            
            $fileStmt->execute();
            $fileResult = $fileStmt->get_result();
            
            if ($fileResult->num_rows > 0) {
                while($row = $fileResult->fetch_assoc()) {
                    $result['files'][] = $row;
                }
            }
            $fileStmt->close();
        }
    } catch (Exception $e) {
        // Handle error or rethrow
        throw $e;
    }
    
    return $result;
}

/**
 * Search files and folders by name
 */
function searchFilesAndFolders($query) {
    global $conn;
    
    $result = array(
        'folders' => array(),
        'files' => array()
    );
    
    try {
        $user_id = getUserId();
        
        // Prepare search query
        $searchTerm = '%' . $conn->real_escape_string($query) . '%';
        
        // Search folders
        $folderSql = "SELECT * FROM folders WHERE name LIKE ? AND user_id = ? ORDER BY name ASC";
        $stmt = $conn->prepare($folderSql);
        $stmt->bind_param('si', $searchTerm, $user_id);
        $stmt->execute();
        $folderResult = $stmt->get_result();
        
        if ($folderResult && $folderResult->num_rows > 0) {
            while($row = $folderResult->fetch_assoc()) {
                $result['folders'][] = $row;
            }
        }
        
        // Search files (not deleted)
        $fileSql = "SELECT * FROM files WHERE name LIKE ? AND is_deleted = 0 AND user_id = ? ORDER BY name ASC";
        $stmt = $conn->prepare($fileSql);
        $stmt->bind_param('si', $searchTerm, $user_id);
        $stmt->execute();
        $fileResult = $stmt->get_result();
        
        if ($fileResult && $fileResult->num_rows > 0) {
            while($row = $fileResult->fetch_assoc()) {
                $result['files'][] = $row;
            }
        }
    } catch (Exception $e) {
        // Handle error or rethrow
        throw $e;
    }
    
    return $result;
}

/**
 * Get recent files
 */
function getRecentFiles($limit = 5) {
    global $conn;
    
    try {
        $user_id = getUserId();
        
        $sql = "SELECT * FROM files WHERE is_deleted = 0 AND user_id = ? ORDER BY upload_date DESC LIMIT ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('ii', $user_id, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $files = array();
            
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $files[] = $row;
                }
            }
            
            $stmt->close();
            return $files;
        }
    } catch (Exception $e) {
        // Handle error or rethrow
        throw $e;
    }
    
    return [];
}

/**
 * Get folder path (breadcrumb)
 */
function getFolderPath($folderId) {
    global $conn;
    
    if ($folderId === null) {
        return array();
    }
    
    try {
        $user_id = getUserId();
        
        $path = array();
        $currentId = $folderId;
        
        while ($currentId !== null) {
            $sql = "SELECT id, name, parent_id FROM folders WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $currentId, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $folder = $result->fetch_assoc();
                array_unshift($path, $folder);
                $currentId = $folder['parent_id'];
            } else {
                $currentId = null;
            }
            
            $stmt->close();
        }
        
        return $path;
    } catch (Exception $e) {
        // Handle error or rethrow
        throw $e;
    }
}

/**
 * Get file type icon class
 */
function getFileIcon($type) {
    switch ($type) {
        case 'pdf':
            return 'bx bxs-file-pdf';
        case 'docx':
            return 'bx bxs-file-doc';
        case 'txt':
            return 'bx bxs-file-txt';
        default:
            return 'bx bxs-file';
    }
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Format date
 */
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

/**
 * Sanitize filename
 */
function sanitizeFilename($filename) {
    // Remove any path information
    $filename = basename($filename);
    
    // Replace spaces with underscores
    $filename = str_replace(' ', '_', $filename);
    
    // Remove any non-alphanumeric characters except for dots, underscores and hyphens
    $filename = preg_replace('/[^a-zA-Z0-9\._-]/', '', $filename);
    
    return $filename;
}

/**
 * Get file extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file type is allowed
 */
function isAllowedFileType($extension) {
    $allowedTypes = array('pdf', 'docx', 'txt');
    return in_array($extension, $allowedTypes);
}
?>