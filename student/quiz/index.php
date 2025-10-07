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

    <ul class="nav nav-underline" style="padding: 20px;">
        <li class="nav-item">
            <a class="nav-link active" href="index.php" style="color: #6366f1">Create</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage-quizzes.php" style="color:rgba(99, 101, 241, 0.8)">Manage Quizzes</a>
        </li>
    </ul>

    <div class="main-content">
        <div class="quiz-dashboard">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Quiz</h3>
                <div>
                    <a href="create-quiz.php" class="btn me-2" style="background-color: #6366f1; color: white;">Create Quiz</a>
                </div>
            </div>
            
            <!-- Alert for messages -->
            <div class="alert alert-success d-none" id="successAlert"></div>
            <div class="alert alert-danger d-none" id="errorAlert"></div>
            
            <!-- Recent Quizzes Section -->
            <div class="recent-section mb-4">
                <h6 class="recent-header">Recent Quizzes</h6>
                <hr>
                <div class="row" id="recentQuizzes">
                    <div class="col-12 text-center">
                        <p>Loading recent quizzes...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add minimal quiz settings modal for JavaScript compatibility -->
    <div class="modal fade" id="quizSettingsModal" tabindex="-1" aria-labelledby="quizSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quizSettingsModalLabel">Quiz Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Minimal content to prevent JS errors -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    
    <?php include '../../shared-student/script.php'; ?>
</body>
</html>