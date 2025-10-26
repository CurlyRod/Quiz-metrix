<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <?php include '../../shared-student/header.php'; ?>

    <link rel="stylesheet" href="css/styles.css">
    
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

        $currentPage = 'quiz'; 

        include '../../shared-student/sidebar.php';
        include '../../shared-student/navbar.php';

    ?>
    
    <input type="hidden" name="user-current-id" id="user-current-id">

    
    <ul class="nav nav-underline " style="padding: 20px;">
                    <li class="nav-item">
                        <a class="nav-link " href="index.php" style="color:rgba(99, 101, 241, 0.8)">Quizzes</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link " href="manage-quizzes.php" style="color:rgba(99, 101, 241, 0.8)">Manage Quizzes</a>
                    </li>
                </ul>


    <div class="main-content">
        <div class="quiz-creator">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Create Quiz</h3>
                <div>
                    <button class="btn me-2" id="createQuizBtn" style="background-color: #6366f1; color: white;">Create Quiz</button>
                    <button class="btn btn-success" id="startQuizBtn">Create and Start Quiz</button>

                </div>
            </div>
            
              <!-- Alert for messages -->
            <div class="alert alert-success d-none" id="successAlert"></div>
            <div class="alert alert-danger d-none" id="errorAlert"></div>
            <hr class="solid">
            <!-- New Quiz Form -->
            <div class="new-quiz-form mb-4">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="quizTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="quizTitle" placeholder="Enter quiz title" maxlength="30">
                    </div>
                    <div class="col-md-6">
                        <label for="quizDescription" class="form-label">Description</label>
                        <input type="text" class="form-control" id="quizDescription" placeholder="Enter quiz description" maxlength="100">
                    </div>
                </div>
            </div>
            
            <!-- Buttons Section -->
            <div class="mb-4">
                <button class="btn" id="importQuestionsBtn" style="background-color: #6366f1; color: white;">
                    <i class="bi bi-upload"></i> Import Questions
                </button>
                <button class="btn btn-outline-secondary ms-2" id="quizSettingsBtn">
                    <i class="bi bi-gear"></i> Quiz Settings
                </button>
            </div>
            
            <!-- Question Cards -->
            <div id="questionCards">
                <!-- Question cards will be dynamically added here -->
            </div>
            <!-- Add Card Button -->
            <button class="btn btn-light w-100 add-card-btn mt-3" id="addCardBtn">
                Add question <i class="bi bi-plus"></i>
            </button>
            <div class="d-flex justify-content-end" style="margin-top: 20px;">
                    <button class="btn me-2" id="createQuizBtn" style="background-color: #6366f1; color: white;">Create Quiz</button>
                    <button class="btn btn-success" id="startQuizBtn">Create and Start Quiz</button>
                </div>

                </div>
        </div>

        
    <!-- Quiz Settings Modal (Combined Timer and Answer Types) -->
    <div class="modal fade" id="quizSettingsModal" tabindex="-1" aria-labelledby="quizSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quizSettingsModalLabel">Quiz Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Timer Settings Section -->
                    <div class="mb-4">
                        <h6 class="mb-3">Timer Settings</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="timedQuizSwitch">
                            <label class="form-check-label" for="timedQuizSwitch">Timed Quiz</label>
                        </div>
                        <div id="timerSettings" class="mt-2 d-none">
                            <label for="quizTime" class="form-label">Time (minutes)</label>
                            <input type="number" class="form-control" id="quizTime" min="1" max="120" value="5">
                            <div class="form-text text-muted">Enter value between 1 and 120 minutes</div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Answer Types Section -->
                    <div class="mb-3">
                        <h6 class="mb-3">Answer Types</h6>
                        <p class="text-muted mb-3">Select one or more answer types. Questions will be automatically assigned these types.</p>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input answer-type-checkbox" type="checkbox" name="answerType" id="multipleChoice" value="multiple" checked>
                                <label class="form-check-label" for="multipleChoice">
                                    Multiple Choice
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input answer-type-checkbox" type="checkbox" name="answerType" id="typedAnswer" value="typed">
                                <label class="form-check-label" for="typedAnswer">
                                    Typed Answer
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input answer-type-checkbox" type="checkbox" name="answerType" id="trueFalse" value="truefalse">
                                <label class="form-check-label" for="trueFalse">
                                    True or False
                                </label>
                            </div>
                        </div>
                        <div id="answerTypeWarning" class="alert alert-warning d-none">
                            Please select at least one answer type.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveQuizSettingsBtn">Save Settings</button>
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
                    <p>You have unsaved changes in your quiz.</p>
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
    <script src="js/script.js"></script>
    <?php include '../../shared-student/script.php'; ?>
     
</body>
</html>