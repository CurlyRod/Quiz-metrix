<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get current folder ID from query string
$currentFolderId = isset($_GET['folder']) ? intval($_GET['folder']) : null;

// Get search query if any
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get files and folders
if (!empty($searchQuery)) {
    $items = searchFilesAndFolders($searchQuery);
} else {
    $items = getFilesAndFolders($currentFolderId);
}

// Get folder path for breadcrumb
$folderPath = getFolderPath($currentFolderId);

// Get recent files
$recentFiles = getRecentFiles(5);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons CSS -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/styles.css" rel="stylesheet">
    <link rel="stylesheet" href="../../vendor/student/home/home.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/x-icon" href="../../assets/img/logo/apple-touch-icon.png">

</head>

<body style="padding: 20px background: #f5f7fb;">
    <?php
    // Check if user is logged in
    if (!isset($_SESSION['user'])) {
        header("Location: ../../landing-page/");
        exit();
    }

    $userData = $_SESSION['user'];
    $_SESSION['USER_NAME'] = $userData['displayName'];
    ?>
    <?php
    $currentPage = 'study-materials'; 

    include '../../shared-student/sidebar.php';
    include '../../shared-student/navbar.php';
    ?>
    <input type="hidden" name="user-current-id" id="user-current-id">


    <div class="container-fluid px-0">
        <!-- Main content -->
        <main class="container-fluid py-4" style="height: 100vh; background-color: #f5f7fb; padding: 20px 20px 20px 20px;">
            <!-- Search bar -->
            <div class="search-container mb-4">
                <form action="index.php" method="GET" id="searchForm" class="search-form">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bx bx-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" name="search" placeholder="Search in File Manager" value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <?php if (!empty($searchQuery)): ?>
                            <a href="index.php<?php echo $currentFolderId ? '?folder=' . $currentFolderId : ''; ?>" class="input-group-text bg-transparent border-start-0 text-decoration-none">
                                <i class="bx bx-x"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($currentFolderId): ?>
                            <input type="hidden" name="folder" value="<?php echo $currentFolderId; ?>">
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Action buttons and filters -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <div class="action-buttons mb-3 mb-md-0">
                    <button type="button" class="btn primary-btn" data-bs-toggle="modal" data-bs-target="#uploadModal" style="background-color: #6366f1; color: white;">
                        <i class="bx bx-upload me-1"></i> Upload
                    </button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newFolderModal">
                        <i class="bx bx-folder-plus me-1"></i> New Folder
                    </button>
                </div>
                <div class="filters d-flex flex-wrap">
                    <div class="view-toggle btn-group">
                        <button type="button" class="btn btn-outline-primary view-toggle active" data-view="grid" >
                            <i class="bx bxs-grid-alt"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary view-toggle" data-view="list">
                            <i class="bx bx-list-ul"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Breadcrumb -->
            <?php if (!empty($searchQuery)): ?>
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Search results for "<?php echo htmlspecialchars($searchQuery); ?>"</li>
                    </ol>
                </nav>
            <?php elseif (!empty($folderPath) || $currentFolderId !== null): ?>
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <?php foreach ($folderPath as $folder): ?>
                            <?php if ($folder['id'] == $currentFolderId): ?>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($folder['name']); ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="index.php?folder=<?php echo $folder['id']; ?>"><?php echo htmlspecialchars($folder['name']); ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>

            <?php if (empty($searchQuery) && $currentFolderId === null): ?>
                <!-- Recent files section -->
                <div class="section mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="section-title">Recent files</h2>
                    </div>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 row-cols-xl-5 g-3">
                        <?php foreach ($recentFiles as $file): ?>
                            <div class="col">
                                <div class="card file-card h-100" data-id="<?php echo $file['id']; ?>" data-type="file">
                                    <div class="card-preview">
                                        <?php if ($file['type'] === 'pdf'): ?>
                                            <div class="preview-placeholder pdf">
                                                <i class="bx bxs-file-pdf"></i>
                                            </div>
                                        <?php elseif ($file['type'] === 'docx'): ?>
                                            <div class="preview-placeholder docx">
                                                <i class="bx bxs-file-doc"></i>
                                            </div>
                                        <?php elseif ($file['type'] === 'txt'): ?>
                                            <div class="preview-placeholder txt">
                                                <i class="bx bxs-file-txt"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($file['name']); ?></h5>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <?php echo strtoupper($file['type']); ?> - <?php echo formatDate($file['upload_date']); ?>
                                            </small>
                                        </p>
                                    </div>
                                    <div class="card-actions dropdown">
                                        <button class="btn btn-sm dropdown-toggle no-arrow" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item preview-file" href="#" data-id="<?php echo $file['id']; ?>" data-type="<?php echo $file['type']; ?>" data-path="<?php echo htmlspecialchars($file['file_path']); ?>">Preview</a></li>
                                            <li><a class="dropdown-item" href="api/download.php?id=<?php echo $file['id']; ?>">Download</a></li>
                                            <li><a class="dropdown-item delete-file" href="#" data-id="<?php echo $file['id']; ?>">Delete</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Folders section -->
            <?php if (!empty($items['folders'])): ?>
                <div class="section mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="section-title">Folders</h2>
                    </div>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 row-cols-xl-5 g-3 folders-container sortable-container" data-type="folder">
                        <?php foreach ($items['folders'] as $folder): ?>
                            <div class="col">
                                <div class="card folder-card h-100" data-id="<?php echo $folder['id']; ?>">
                                    <a href="index.php?folder=<?php echo $folder['id']; ?>" class="card-body d-flex align-items-center">
                                        <i class="bx bxs-folder folder-icon me-3"></i>
                                        <div>
                                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($folder['name']); ?></h5>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    Created <?php echo formatDate($folder['created_at']); ?>
                                                </small>
                                            </p>
                                        </div>
                                    </a>
                                    <div class="card-actions dropdown">
                                        <button class="btn btn-sm dropdown-toggle no-arrow" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item rename-folder" href="#" data-id="<?php echo $folder['id']; ?>" data-name="<?php echo htmlspecialchars($folder['name']); ?>">Rename</a></li>
                                            <li><a class="dropdown-item delete-folder" href="#" data-id="<?php echo $folder['id']; ?>">Delete</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Files section -->
            <div class="section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="section-title">Files</h2>
                </div>

                <!-- Grid view -->
                <div class="file-container grid-view">
                    <?php if (empty($items['files'])): ?>
                        <div class="empty-state">
                            <?php if (!empty($searchQuery)): ?>
                                <p>No files found matching "<?php echo htmlspecialchars($searchQuery); ?>"</p>
                            <?php else: ?>
                                <p>No files in this folder</p>
                                <p>Upload files or create a new folder to get started</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 row-cols-xl-5 g-3 files-container sortable-container" data-type="file">
                            <?php foreach ($items['files'] as $file): ?>
                                <div class="col">
                                    <div class="card file-card h-100" data-id="<?php echo $file['id']; ?>">
                                        <div class="card-preview">
                                            <?php if ($file['type'] === 'pdf'): ?>
                                                <div class="preview-placeholder pdf">
                                                    <i class="bx bxs-file-pdf"></i>
                                                </div>
                                            <?php elseif ($file['type'] === 'docx'): ?>
                                                <div class="preview-placeholder docx">
                                                    <i class="bx bxs-file-doc"></i>
                                                </div>
                                            <?php elseif ($file['type'] === 'txt'): ?>
                                                <div class="preview-placeholder txt">
                                                    <i class="bx bxs-file-txt"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($file['name']); ?></h5>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <?php echo strtoupper($file['type']); ?> - <?php echo formatDate($file['upload_date']); ?>
                                                </small>
                                            </p>
                                        </div>
                                        <div class="card-actions dropdown">
                                            <button class="btn btn-sm dropdown-toggle no-arrow" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item preview-file" href="#" data-id="<?php echo $file['id']; ?>" data-type="<?php echo $file['type']; ?>" data-path="<?php echo htmlspecialchars($file['file_path']); ?>">Preview</a></li>
                                                <li><a class="dropdown-item" href="api/download.php?id=<?php echo $file['id']; ?>">Download</a></li>
                                                <li><a class="dropdown-item delete-file" href="#" data-id="<?php echo $file['id']; ?>">Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- List view (hidden by default) -->
                <div class="list-view d-none">
                    <?php if (empty($items['files'])): ?>
                        <div class="empty-state">
                            <?php if (!empty($searchQuery)): ?>
                                <p>No files found matching "<?php echo htmlspecialchars($searchQuery); ?>"</p>
                            <?php else: ?>
                                <p>No files in this folder</p>
                                <p>Upload files or create a new folder to get started</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Modified</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items['files'] as $file): ?>
                                        <tr data-id="<?php echo $file['id']; ?>" data-type="file">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="<?php echo getFileIcon($file['type']); ?> me-2"></i>
                                                    <span><?php echo htmlspecialchars($file['name']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo strtoupper($file['type']); ?></td>
                                            <td><?php echo formatFileSize($file['size']); ?></td>
                                            <td><?php echo formatDate($file['upload_date']); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary preview-file" data-id="<?php echo $file['id']; ?>" data-type="<?php echo $file['type']; ?>" data-path="<?php echo htmlspecialchars($file['file_path']); ?>">
                                                        <i class="bx bx-show"></i>
                                                    </button>
                                                    <a href="api/download.php?id=<?php echo $file['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-download"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-file" data-id="<?php echo $file['id']; ?>">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Upload File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="fileUpload" class="form-label">Select File</label>
                            <input class="form-control" type="file" id="fileUpload" name="file" accept=".pdf,.docx,.txt">
                            <div class="form-text">Only .pdf, .docx, and .txt files are allowed.</div>
                        </div>
                        <input type="hidden" name="folder_id" value="<?php echo $currentFolderId; ?>">
                    </form>
                    <div class="progress d-none" id="uploadProgress">
                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="uploadButton" style="background-color: #6366f1; color: white;">Upload</button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Folder Modal -->
    <div class="modal fade" id="newFolderModal" tabindex="-1" aria-labelledby="newFolderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newFolderModalLabel">Create New Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newFolderForm">
                        <div class="mb-3">
                            <label for="folderName" class="form-label">Folder Name</label>
                            <input type="text" class="form-control" id="folderName" name="name" maxlength="15" required>
                        </div>
                        <input type="hidden" name="parent_id" value="<?php echo $currentFolderId; ?>">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="createFolderButton">Create</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rename Folder Modal -->
    <div class="modal fade" id="renameFolderModal" tabindex="-1" aria-labelledby="renameFolderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="renameFolderModalLabel">Rename Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="renameFolderForm">
                        <div class="mb-3">
                            <label for="newFolderName" class="form-label">New Folder Name</label>
                            <input type="text" class="form-control" id="newFolderName" name="name" maxlength="20" required>
                        </div>
                        <input type="hidden" name="folder_id" id="renameFolderId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="renameFolderButton">Rename</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">File Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="previewContent">
                    <!-- Preview content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-primary" id="previewDownloadBtn">Download</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="confirmationMessage">
                    Are you sure you want to proceed?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmActionButton">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <!-- Toasts will be added here dynamically -->
    </div>


    <!-- Custom JS -->
    <script src="js/fileManager.js"></script>
    <?php include '../../shared-student/script.php'; ?>

</body>

</html>