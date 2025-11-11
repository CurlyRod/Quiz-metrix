
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
    <link rel="stylesheet" href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="../../assets/img/logo/apple-touch-icon.png">

</head>

<body>
    <?php
    session_start();
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

    include '../../shared-student/sidebar.php';
    include '../../shared-student/navbar.php';
?>
    <input type="hidden" name="user-current-id" id="user-current-id">


    <div class="container-fluid px-0">
        <!-- Main content -->
        <main class="container-fluid py-4" style="height: 100vh; background-color: #f5f7fb; padding: 20px 20px 20px 20px;">
          <!-- Action buttons + Search bar + Filters -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-nowrap">
                
                <!-- Action buttons -->
                <div class="action-buttons d-flex align-items-center">
                    <button type="button" class="btn primary-btn" id="uploadBtn" data-bs-toggle="modal" data-bs-target="#uploadModal" style="background-color: #6366f1; color: white;">
                        <i class="bx bx-upload me-1"></i> Upload
                    </button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newFolderModal">
                        <i class="bx bx-folder-plus me-1"></i> Folder
                    </button>
                </div>

                <!-- Search bar -->
                <div class="search-container flex-grow-1 mx-3" style="min-width: 500px; max-width: 800px;">
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

                <!-- Filters -->
                <div class="filters d-flex align-items-center">
                    <div class="view-toggle btn-group">
                        <button type="button" class="btn btn-outline-primary view-toggle active" data-view="grid">
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

            
                
            <!-- Folders section -->
            <?php if (!empty($items['folders'])): ?>
                <div class="section mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1 class="section-title">Folders</h1>
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
                                    <div class="card-actions dropstart">
                                        <button class="btn btn-sm dropdown-toggle no-arrow" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu">
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
                    <!-- Left side: Title + Action buttons -->
                    <div class="d-flex align-items-center gap-2">
                        <h1 class="section-title mb-0 me-3">Files</h1>
                        <div class="d-flex gap-2">
                            <button id="selectItemsBtn" class="btn primary-btn">
                                <i class="bx bx-check-circle"></i> Select Files
                            </button>
                            <button class="btn btn-outline-info select-all-btn d-none" id="selectAllBtn">
                                <i class="bx bx-select-multiple"></i> Select All
                            </button>
                            <button id="bulkDeleteBtn" class="upload-btn d-none" style="border-color:#ea4335; color:#ea4335;">
                                <i class="bx bx-trash"></i> Delete Selected
                            </button>
                        </div>
                    </div>

                    <!-- Right side: Filter dropdowns -->
                    <div class="d-flex gap-2">
                        <!-- Sort by Date -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortDateDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-calendar me-1"></i> Date
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="sortDateDropdown">
                                <li><a class="dropdown-item sort-option" href="#" data-target="sortDateDropdown" data-filter="newest">Newest First</a></li>
                                <li><a class="dropdown-item sort-option" href="#" data-target="sortDateDropdown" data-filter="oldest">Oldest First</a></li>
                            </ul>
                        </div>

                        <!-- Sort by File Type -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortTypeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-file me-1"></i> File Type
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="sortTypeDropdown">
                                <li><a class="dropdown-item sort-option" href="#" data-target="sortTypeDropdown" data-filter="pdf">PDF</a></li>
                                <li><a class="dropdown-item sort-option" href="#" data-target="sortTypeDropdown" data-filter="docx">DOCX</a></li>
                                <li><a class="dropdown-item sort-option" href="#" data-target="sortTypeDropdown" data-filter="txt">TXT</a></li>
                            </ul>
                        </div>

                        <!-- Sort by Name -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortNameDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-sort-a-z me-1"></i> Name
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="sortNameDropdown">
                                <li><a class="dropdown-item sort-option" href="#" data-target="sortNameDropdown" data-filter="az">A → Z</a></li>
                                <li><a class="dropdown-item sort-option" href="#" data-target="sortNameDropdown" data-filter="za">Z → A</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Grid view -->
                <div class="row file-container grid-view">
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
                                        <div class="file-checkbox">
                                             <input type="checkbox" class="select-file" data-id="<?= $file['id'] ?>">
                                        </div>

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
                                                <small class="text-muted" data-upload="<?php echo $file['upload_date']; ?>">
                                                    <?php echo strtoupper($file['type']); ?> - <?php echo formatDate($file['upload_date']); ?>
                                                </small>
                                            </p>
                                        </div>
                                        <div class="card-actions dropdown">
                                            <button class="btn btn-sm dropdown-toggle no-arrow" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item preview-file" href="#" data-id="<?php echo $file['id']; ?>" data-type="<?php echo $file['type']; ?>" data-path="<?php echo htmlspecialchars($file['file_path']); ?>" data-name="<?php echo htmlspecialchars($file['name']); ?>">Preview</a></li>
                                               
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
                                        <th>  Name</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Modified</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items['files'] as $file): ?>
                                        <tr data-id="<?php echo $file['id']; ?>" data-type="<?php echo strtolower($file['type']); ?>">
                                        
                                            <td>
                                                <div class="d-flex align-items-center">
                                                      <div class="file-checkbox">
                                                            <input type="checkbox" class="select-file" data-id="<?= $file['id'] ?>">
                                                         </div>
                                                    <i class="<?php echo getFileIcon($file['type']); ?> me-2"></i>
                                                    <span><?php echo htmlspecialchars($file['name']); ?></span>
                                                </div>
                                            </td>
                                            
                                            <td><?php echo strtoupper($file['type']); ?></td>
                                            <td><?php echo formatFileSize($file['size']); ?></td>
                                            <td data-upload="<?php echo $file['upload_date']; ?>">
                                                <?php echo formatDate($file['upload_date']); ?>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"> 
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item preview-file" href="#" data-id="<?php echo $file['id']; ?>" data-type="<?php echo $file['type']; ?>" data-path="<?php echo htmlspecialchars($file['file_path']); ?>" data-name="<?php echo htmlspecialchars($file['name']); ?>">
                                                                <i class="bx bx-show me-2"></i> Preview
                                                            </a>
                                                        </li>
                                                      
                                                        <li>
                                                            <a class="dropdown-item text-danger delete-file" href="#" data-id="<?php echo $file['id']; ?>">
                                                                <i class="bx bx-trash me-2"></i> Delete
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                                            <!--Pagination Controls -->
                            <div id="paginationControls" class="d-flex justify-content-center align-items-center mt-3 mb-4">
                            <button id="prevPage" class="btn btn-outline-primary me-2" disabled>Previous</button>
                            <span id="pageInfo" class="fw-bold text-primary"></span>
                            <button id="nextPage" class="btn btn-outline-primary ms-2">Next</button>
                            </div>                    
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <div id="noResultsMessage" class="empty-state d-none">
  <i class="bx bx-search"></i> No results found
</div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content upload-modal">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="uploadModalLabel">
                        <i class="bx bx-cloud-upload me-2"></i> Upload Files
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="fileUpload" class="form-label fw-semibold fs-5">Choose or Drop Files</label>

                            <div id="fileInputWrapper" class="upload-dropzone text-center">
                                <input class="form-control" type="file" id="fileUpload" name="files[]" accept=".pdf,.docx,.txt" multiple>
                                <i class="bx bx-cloud-upload fs-1 text-primary mb-2"></i>
                                <p class="m-0 fw-semibold">Click to browse or drag & drop files here</p>
                                <small class="text-muted d-block mt-1">Allowed: 5 MB below (each file).</small>
                                <small class="text-muted d-block mt-1">Allowed: .pdf, .docx, .txt</small> 
                            </div>
                        </div>
                        <input type="hidden" name="folder_id" value="<?php echo $currentFolderId; ?>">
                    </form>

                    <ul id="fileList" class="list-group mb-3"></ul>

                    <!-- Enhanced circular progress with filename and size -->
                    <div class="upload-progress-wrapper d-none" id="uploadProgressWrapper">
                        <div class="upload-file-info" id="uploadFileInfo">
                            <div class="file-name" id="uploadFileName">Uploading...</div>
                            <div class="file-size" id="uploadFileSize">0 KB</div>
                        </div>
                            <div class="percentage" id="progressPercent">0%</div> 
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn primary-btn" id="uploadButton">Upload</button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Folder Modal -->
    <div class="modal fade" id="newFolderModal" tabindex="-1" aria-labelledby="newFolderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content new-folder-modal">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="newFolderModalLabel">
                        <i class="bx bx-folder-plus me-2"></i> Create New Folder
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="newFolderForm">
                        <div class="mb-4">
                            <label for="folderName" class="form-label fw-semibold fs-5">Folder Name</label>
                            <input type="text" class="form-control form-control-lg" id="folderName" name="name" maxlength="15" placeholder="e.g. Documents" required>
                        </div>
                        <input type="hidden" name="parent_id" value="<?php echo $currentFolderId; ?>">
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
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
                    <!-- Added download attribute to preview modal download button -->
                    <a href="#" class="btn btn-primary" id="previewDownloadBtn" download>Download</a>
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

   <div class="view-toggle-group btn-group"> 
  <button type="button" class="btn view-toggle" data-view="grid">
    <i class="bx bx-grid"></i>
  </button>
  <button type="button" class="btn view-toggle" data-view="list">
    <i class="bx bx-list-ul"></i>
  </button>
</div>



    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>




    <!-- Custom JS -->
     <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script src="js/fileManager.js"></script>
    <?php include '../../shared-student/script.php'; ?>

</body>

</html>