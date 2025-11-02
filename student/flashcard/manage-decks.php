<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Decks - Flashcards</title>
    <?php include '../../shared-student/header.php'; ?>
    <link rel="stylesheet" href="css/manage-decks.css">
</head>
<body>
    <?php
        session_start();
        if (!isset($_SESSION['user'])) {
            header("Location: ../../landing-page/");
            exit();
        } 
    
        $userData = $_SESSION['user']; 
        $_SESSION['USER_NAME'] = $userData['displayName'];
        $currentPage = 'flashcards'; 

        include '../../shared-student/sidebar.php';
        include '../../shared-student/navbar.php';
    ?>
    <input type="hidden" name="user-current-id" id="user-current-id">

    <ul class="nav nav-underline" style="padding: 20px;">
        <li class="nav-item">
            <a class="nav-link" href="index.php" style="color: #6366f1">Flashcards</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="manage-decks.php" style="color:rgba(99, 101, 241, 0.8)">Manage Decks</a>
        </li>
    </ul>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Manage Decks</h1>
            <a href="create-deck.php" class="btn btn-primary">
                <svg class="quiz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Create New Deck
            </a>
        </div>

        <div class="search-actions-row">
            <button id="deleteSelectedBtn" class="btn btn-danger" disabled>
                <svg class="quiz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                Delete Selected
            </button>
            <div class="search-bar">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <input type="text" class="search-input" id="searchDeck" placeholder="Search decks..." maxlength="30">
                <button class="btn-clear" id="clearSearch" type="button">
                    <svg class="quiz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="alert d-none" id="statusMessage"></div>
        
        <div class="table-container">
            <table class="quiz-table">
                <thead>
                    <tr>
                        <th class="checkbox-column">
                            <input type="checkbox" id="selectAllCheckbox">
                        </th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Created</th>
                        <th>Last Updated</th>
                        <th>Cards</th>
                        <th class="actions-column">Actions</th>
                    </tr>
                </thead>
                <tbody id="deckTable">
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="loading-spinner"></div>
                            <p>Loading decks...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <nav class="pagination-wrapper">
            <div class="pagination-info" id="paginationInfo">Showing 1-10 of 0 decks</div>
            <ul class="pagination" id="paginationControls">
                <li class="page-item disabled">
                    <a class="page-link" href="#" id="prevPage">
                        <svg class="quiz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item disabled">
                    <a class="page-link" href="#" id="nextPage">
                        <svg class="quiz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="deleteModalTitle">Delete Deck</h3>
                <button type="button" class="btn-close" data-dismiss="modal">
                    <svg class="quiz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteModalBody">Are you sure you want to delete this deck?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>

    <!-- Results History Modal -->
    <div class="modal" id="resultsHistoryModal">
        <div class="modal-overlay"></div>
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">Deck Results History</h3>
                <button type="button" class="btn-close" data-dismiss="modal">
                    <svg class="quiz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <h4 id="resultsDeckTitle" class="results-title">Deck Title</h4>
                <div class="table-container">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Mode</th>
                                <th>Known/Total</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody id="resultsTable">
                            <tr>
                                <td colspan="4" class="text-center">Loading results...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>

    <script src="js/manage-decks.js"></script>
    <?php include '../../shared-student/script.php'; ?>
</body>
</html>
