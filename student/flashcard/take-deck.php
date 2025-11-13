<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Flashcards</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <?php include '../../shared-student/header.php'; ?>
    <link rel="stylesheet" href="css/take-deck.css">
</head>
<body>
    <?php
        session_start();
        if (!isset($_SESSION['user'])) {
            header("Location: ../../landing-page/");
            exit();
        } 
    ?>

    <div class="main-content">
        <div class="deck-player">
            <!-- Deck Header with Progress (Sticky) -->
            <div class="deck-header">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <button class="btn btn-outline-secondary" id="exitBtn">
                        Exit
                    </button>
                    <h2 id="deckTitle" class="deck-title text-center m-0 flex-grow-1">
                        Loading...
                    </h2>
                    <div class="d-flex align-items-center gap-3">
                        <!-- Optional: Add speak button or other controls here if needed -->
                        <div style="width: 40px;"></div> <!-- Spacer for balance -->
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="progress-container mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span id="cardCount" class="text-muted">Card 1 of 10</span>
                        <span id="progressPercent" class="fw-bold" style="color: #6366f1;">0%</span>
                    </div>
                    <div class="progress">
                        <div id="progressFill" class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <!-- Flashcard Display -->
            <div class="flashcard-display mb-4">
                <div class="flip-card">
                    <div class="flip-card-inner" id="flashcardInner">
                        <div class="flip-card-front" id="flashcardFront">
                            <p>Loading...</p>
                        </div>
                        <div class="flip-card-back" id="flashcardBack">
                            <p>Loading...</p>
                        </div>
                    </div>
                </div>
                <p class="text-center text-muted mt-3">
                    <i class="bi bi-cursor-fill"></i> Click card or press <kbd>Space</kbd> to flip
                </p>
            </div>

            <!-- Navigation and Controls -->
            <div class="controls-section">
                <!-- Track Progress Mode Controls -->
                <div id="trackProgressControls" class="d-none">
                    <div class="d-flex gap-3 justify-content-center">
                        <button class="btn btn-lg btn-outline-danger action-btn" id="dontKnowBtn">
                            <i class="bi bi-x-circle"></i> Don't Know
                        </button>
                        <button class="btn btn-lg btn-outline-success action-btn" id="knowBtn">
                            <i class="bi bi-check-circle"></i> Know
                        </button>
                    </div>
                </div>

                <!-- Standard Navigation Mode Controls -->
                <div id="standardControls">
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-lg btn-outline-primary nav-btn" id="prevBtn">
                            <i class="bi bi-chevron-left"></i> Previous
                        </button>
                        <button class="btn btn-lg btn-primary nav-btn" id="nextBtn">
                            Next <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exit Warning Modal -->
    <div class="modal fade" id="exitWarningModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leaving Quiz?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to leave? Your progress will not be saved.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Stay</button>
                    <button type="button" class="btn btn-primary" id="confirmExitBtn" style="
  background: #6366f1 ;
  color: white ;
  font-weight: 600 ;
">Leave</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Modal (for trackProgress = true) -->
    <div class="modal fade" id="resultsModal" tabindex="-1" aria-labelledby="resultsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title w-100 text-center" id="resultsModalLabel">Study Session Complete! 🎉</h5>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="results-percent mb-2" id="percentKnown">75%</div>
                    <p class="text-muted mb-4">Cards Known</p>
                    
                    <div class="results-stats">
                        <div class="stat-item">
                            <span class="stat-label">Total Cards</span>
                            <span class="stat-value" id="totalCardsResult">10</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Known</span>
                            <span class="stat-value text-success" id="knownCountResult">7</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Unknown</span>
                            <span class="stat-value text-danger" id="unknownCountResult">3</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary" id="closeResultsBtn">Close</button>
                    <button type="button" class="btn btn-outline-primary" id="unknownOnlyBtn">
                        <i class="bi bi-arrow-repeat"></i> Unknown Only
                    </button>
                    <button type="button" class="btn btn-primary" id="retryBtn">
                        <i class="bi bi-arrow-clockwise"></i> Retry Deck
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Completion Modal (for trackProgress = false) -->
    <div class="modal fade" id="completionModal" tabindex="-1" aria-labelledby="completionModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title w-100 text-center" id="completionModalLabel">Study Session Complete! 🎉</h5>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="completion-icon mb-3">
                        <i class="bi bi-check-circle-fill" style="font-size: 4rem; color: #10b981;"></i>
                    </div>
                    <h4 class="mb-3">Great job!</h4>
                    <p class="text-muted">You've completed all the flashcards in this deck.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary" id="closeCompletionBtn">Close</button>
                    <button type="button" class="btn btn-primary" id="retryCompletionBtn">
                        <i class="bi bi-arrow-clockwise"></i> Study Again
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/take-deck.js"></script>
</body>
</html>