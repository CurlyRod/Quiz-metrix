<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/quiz.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../../assets/img/logo/apple-touch-icon.png">

</head>
<body>
    <div class="container mt-4">
        <div class="quiz-taker">
            <!-- Quiz Header (Sticky) -->
            <div class="quiz-header">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <a href="index.php" class="btn" style="background-color: #6366f1; color: white;">Exit</a>
                        <h2 id="quizTitle" class="quiz-title text-center m-0 flex-grow-1">
                        Quiz Title
                        </h2>
                            <div class="d-flex align-items-center gap-3">
                                <button id="speakQuestionBtn" class="speak-btn" title="Speak Question">
                                    <i class="bx bx-volume-full"></i>
                                </button>
                            <div id="timerDisplay" class="d-none">
                        <span id="timer" class="timer-badge">00:00</span>
                    </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="progress-container mt-2">
                    <div class="d-flex justify-content-between mb-2">
                    <span id="progressText" class="text-muted">0 of 0 answered</span>
                    <span id="progressPercent" class="fw-bold" style="color: var(--primary-color);">0%</span>
                    </div>
                    <div class="progress">
                    <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>


            <!-- All Questions Container -->
            <div id="questionContainer">
                <div class="text-center py-5">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading quiz...</p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="submit-container" id="submitContainer" style="display: none;">
                <button class="btn-submit" id="submitQuizBtn">
                    <i class="bx bx-check-circle me-2"></i>
                    Submit Quiz
                </button>
            </div>
        </div>
    </div>

    <!-- Exit Warning Modal -->
    <div class="modal fade" id="exitWarningModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leaving Quiz?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to leave? Your progress will be saved.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Stay</button>
                    <button type="button" class="btn btn-primary" id="confirmExitBtn">Leave</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Modal -->
    <div class="modal fade" id="resultsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quiz Results</h5>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h2>Your Score: <span id="scoreDisplay">0/0</span></h2>
                        <h3><span id="percentageDisplay">0%</span></h3>
                    </div>
                    <div id="answerReview"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="retakeQuizBtn">Retake Quiz</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">Back to Home</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="js/quiz-script.js"></script>

</body>

</html>