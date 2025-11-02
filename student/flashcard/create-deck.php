<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Deck - Flashcards</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <?php include '../../shared-student/header.php'; ?>
    <link rel="stylesheet" href="css/styles.css">
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
            <a class="nav-link" href="index.php" style="color:rgba(99, 101, 241, 0.8)">Flashcards</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage-decks.php" style="color:rgba(99, 101, 241, 0.8)">Manage Decks</a>
        </li>
    </ul>

    <div class="main-content">
        <div class="deck-creator">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Create Deck</h3>
                <div>
                    <button class="btn me-2" id="saveDeckBtn" style="background-color: #6366f1; color: white;">Create Deck</button>
                    <button class="btn btn-success" id="startDeckBtn">Create and Start</button>
                </div>
            </div>
            
            <div class="alert alert-success d-none" id="successAlert"></div>
            <div class="alert alert-danger d-none" id="errorAlert"></div>
            <hr class="solid">
            
            <div class="new-deck-form mb-4">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="deckTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="deckTitle" placeholder="Enter deck title" maxlength="30">
                    </div>
                    <div class="col-md-6">
                        <label for="deckDescription" class="form-label">Description</label>
                        <input type="text" class="form-control" id="deckDescription" placeholder="Enter deck description" maxlength="100">
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <button class="btn" id="importCardsBtn" href="import-flashcards.php" style="background-color: #6366f1; color: white;">
                    <i class="bi bi-upload"></i> Import Flashcards
                </button>
                <button class="btn btn-outline-secondary ms-2" id="deckSettingsBtn">
                    <i class="bi bi-gear"></i> Deck Settings
                </button>
            </div>
            
            <div id="flashcardContainer">
                <!-- Flashcard containers will be added here -->
            </div>
            
            <button class="btn btn-light w-100 add-card-btn mt-3" id="addCardBtn">
                Add flashcard <i class="bi bi-plus"></i>
            </button>
            
           
        </div>
    </div>

    <!-- Deck Settings Modal -->
<div class="modal fade" id="deckSettingsModal" tabindex="-1" aria-labelledby="deckSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deckSettingsModalLabel">Deck Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="mb-3">Study Mode</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="studyMode" id="sequenceMode" value="sequence" checked>
                        <label class="form-check-label" for="sequenceMode">
                            Sequence (in order)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="studyMode" id="randomizedMode" value="randomized">
                        <label class="form-check-label" for="randomizedMode">
                            Randomized
                        </label>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="trackProgressSwitch" checked>
                        <label class="form-check-label" for="trackProgressSwitch">
                            Track Progress
                        </label>
                    </div>
                    <p class="text-muted small mt-2">When enabled, marks cards as "Known" or "Unknown" and shows results at the end.</p>
                </div>

                <hr>

                <div class="mb-3">
                    <h6 class="mb-3">Card Display</h6>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="showBackOnLoadSwitch">
                        <label class="form-check-label" for="showBackOnLoadSwitch">
                            Show back of card on initial load
                        </label>
                    </div>
                    <p class="text-muted small mt-2">When enabled, cards will show the back side first when studying.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveDeckSettingsBtn">Save Settings</button>
            </div>
        </div>
    </div>
</div>

    <!-- Exit Warning Modal -->
    <div class="modal fade" id="exitWarningModal" tabindex="-1" aria-labelledby="exitWarningModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="exitWarningModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Unsaved Changes
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>You have unsaved changes in your deck.</p>
                    <p class="text-danger"><strong>If you leave now, your changes will not be saved.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Stay on Page</button>
                    <a href="#" id="confirmExitBtn" class="btn btn-danger">Leave Anyway</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/create-deck.js"></script>
    <?php include '../../shared-student/script.php'; ?>
</body>
</html>
