<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycle Bin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <?php include '../../shared-student/header.php'; ?>
    <link rel="stylesheet" href="css/style.css">

    
</head>
<body>
    <!-- Sidebar and Navbar -->
    <?php
        session_start();
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ../../landing-page/");
            exit();
        } 
    
        $userData = $_SESSION['user']; 
        $_SESSION['USER_NAME'] = $userData['displayName'];

        $currentPage = 'recyclebin'; 

        include '../../shared-student/sidebar.php';
        include '../../shared-student/navbar.php';
    ?>

<div class="container py-4">
        <!-- Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="bx bx-trash icon"></i> Recycle Bin
            </h1>
            <p class="text-muted mb-0">
                Deleted items are stored here for 30 days before permanent deletion
            </p>
        </div>

        <!-- Filters -->
        <div class="filter-card d-flex justify-content-between align-items-start flex-wrap gap-4">
            <!-- Date Filters -->
            <div class="filter-section">
                <div class="filter-title">Filter by Date</div>
                <div class="d-flex flex-wrap gap-2">
                    <button class="filter-btn active" data-date-filter="all">All Time</button>
                    <button class="filter-btn" data-date-filter="today">Today</button>
                    <button class="filter-btn" data-date-filter="week">This Week</button>
                    <button class="filter-btn" data-date-filter="month">This Month</button>
                </div>
            </div>

            <!-- Study Tool Filters -->
            <div class="filter-section">
                <div class="filter-title">Filter by Study Tools</div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="study-tool-badge badge-files" data-type-filter="Files">
                        <i class="bi bi-file-earmark"></i> Files
                    </span>
                    <span class="study-tool-badge badge-flashcards" data-type-filter="Flashcards">
                        <i class="bx bx-card icon"></i> Flashcards
                    </span>
                    <span class="study-tool-badge badge-quiz" data-type-filter="Quiz">
                        <i class="bx bx-check-double icon"></i> Quiz
                    </span>
                    <span class="study-tool-badge badge-notes" data-type-filter="Notes">
                        <i class="bx bx-notepad icon"></i> Notes
                    </span>
                </div>
            </div>
        </div>

        <!-- Items Count -->
        <div class="mb-3">
            <small class="text-muted" id="itemsCount">Loading...</small>
        </div>

        <!-- Deleted Items Grid -->
        <div id="itemsContainer">
            <div class="row g-3" id="itemsGrid"></div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="deleteModalTitle">Permanently Delete</h3>
                <button type="button" class="btn-close" data-dismiss="modal">
                    <svg class="quiz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteModalBody">Are you sure you want to permanently delete this item? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Permanently</button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Loading deleted items...</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="js/recyclebin.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../../shared-student/script.php'; ?>
</body>
</html>