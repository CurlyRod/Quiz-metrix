<?php
require_once 'config.php';

/**
 * Get all files and folders for a specific parent folder
 */
function getFilesAndFolders($folderId = null) {
    global $conn;
    
    $result = array(
        'folders' => array(),
        'files' => array()
    );
    
    // Get folders
    $folderSql = "SELECT * FROM folders WHERE parent_id " . 
                 ($folderId === null ? "IS NULL" : "= $folderId") . 
                 " ORDER BY position ASC";
    $folderQuery = $conn->query($folderSql);
    
    if ($folderQuery && $folderQuery->num_rows > 0) {
        while($row = $folderQuery->fetch_assoc()) {
            $result['folders'][] = $row;
        }
    }
    
    // Get files (not deleted)
    $fileSql = "SELECT * FROM files WHERE folder_id " . 
               ($folderId === null ? "IS NULL" : "= $folderId") . 
               " AND is_deleted = 0 ORDER BY position ASC";
    $fileQuery = $conn->query($fileSql);
    
    if ($fileQuery && $fileQuery->num_rows > 0) {
        while($row = $fileQuery->fetch_assoc()) {
            $result['files'][] = $row;
        }
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
    
    // Prepare search query
    $searchTerm = '%' . $conn->real_escape_string($query) . '%';
    
    // Search folders
    $folderSql = "SELECT * FROM folders WHERE name LIKE ? ORDER BY name ASC";
    $stmt = $conn->prepare($folderSql);
    $stmt->bind_param('s', $searchTerm);
    $stmt->execute();
    $folderResult = $stmt->get_result();
    
    if ($folderResult && $folderResult->num_rows > 0) {
        while($row = $folderResult->fetch_assoc()) {
            $result['folders'][] = $row;
        }
    }
    
    // Search files (not deleted)
    $fileSql = "SELECT * FROM files WHERE name LIKE ? AND is_deleted = 0 ORDER BY name ASC";
    $stmt = $conn->prepare($fileSql);
    $stmt->bind_param('s', $searchTerm);
    $stmt->execute();
    $fileResult = $stmt->get_result();
    
    if ($fileResult && $fileResult->num_rows > 0) {
        while($row = $fileResult->fetch_assoc()) {
            $result['files'][] = $row;
        }
    }
    
    return $result;
}

/**
 * Get recent files
 */
function getRecentFiles($limit = 5) {
    global $conn;
    
    $sql = "SELECT * FROM files WHERE is_deleted = 0 ORDER BY upload_date DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $files = array();
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $files[] = $row;
        }
    }
    
    return $files;
}

/**
 * Get folder path (breadcrumb)
 */
function getFolderPath($folderId) {
    global $conn;
    
    if ($folderId === null) {
        return array();
    }
    
    $path = array();
    $currentId = $folderId;
    
    while ($currentId !== null) {
        $sql = "SELECT id, name, parent_id FROM folders WHERE id = $currentId";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $folder = $result->fetch_assoc();
            array_unshift($path, $folder);
            $currentId = $folder['parent_id'];
        } else {
            $currentId = null;
        }
    }
    
    return $path;
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
