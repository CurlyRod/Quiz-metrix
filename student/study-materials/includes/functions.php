<?php
require_once 'config.php';


function checkUserStatus() {
    global $conn;
    
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }
    
    $status_stmt = $conn->prepare("SELECT status FROM user_credential WHERE email = ?");
    $status_stmt->bind_param("s", $_SESSION['USER_EMAIL']);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();

    if ($status_result->num_rows === 0 || $status_result->fetch_assoc()['status'] !== 'Active') {
        session_destroy();
        throw new Exception('Your account has been deactivated. Please contact administrator.');
    }
    $status_stmt->close();
 
}

function getUserId() {
    global $conn;
    
    checkUserStatus(); 
    
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

// Add this function to functions.php
function getUserStorageUsage() {
    global $conn;
    
    try {
        $user_id = getUserId();
        
        // Calculate total file size for user (excluding deleted files)
        $sql = "SELECT COALESCE(SUM(size), 0) as total_size FROM files WHERE user_id = ? AND is_deleted = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $total_size = $row['total_size'];
        } else {
            $total_size = 0;
        }
        
        $stmt->close();
        
        // Constants
        $limit_bytes = 100 * 1024 * 1024; // 100MB in bytes
        $used_percentage = ($total_size / $limit_bytes) * 100;
        
        return [
            'used_bytes' => $total_size,
            'limit_bytes' => $limit_bytes,
            'used_percentage' => round($used_percentage, 1),
            'used_formatted' => formatFileSize($total_size),
            'limit_formatted' => '100 MB',
            'remaining_bytes' => $limit_bytes - $total_size,
            'remaining_formatted' => formatFileSize($limit_bytes - $total_size)
        ];
        
    } catch (Exception $e) {
        return [
            'used_bytes' => 0,
            'limit_bytes' => 100 * 1024 * 1024,
            'used_percentage' => 0,
            'used_formatted' => '0 Bytes',
            'limit_formatted' => '100 MB',
            'remaining_bytes' => 100 * 1024 * 1024,
            'remaining_formatted' => '100 MB'
        ];
    }
}

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

function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function sanitizeFilename($filename) {
    // Remove any path information
    $filename = basename($filename);
    
    // Replace spaces with underscores
    $filename = str_replace(' ', '_', $filename);
    
    // Remove any non-alphanumeric characters except for dots, underscores and hyphens
    $filename = preg_replace('/[^a-zA-Z0-9\._-]/', '', $filename);
    
    return $filename;
}

function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function isAllowedFileType($extension) {
    $allowedTypes = array('pdf', 'docx', 'txt');
    return in_array($extension, $allowedTypes);
}
?>