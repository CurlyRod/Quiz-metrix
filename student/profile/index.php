<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <?php include '../../shared-student/header.php'; ?>

     <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">

    <link rel="stylesheet" href="styles.css">
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
include '../../shared-student/sidebar.php';
include '../../shared-student/navbar.php';
?>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1 class="header-title">Student Profile</h1>
            <p class="header-subtitle">Track your learning progress</p>
        </header>

        <!-- Profile Section -->
<div class="grid-layout">
    <div class="profile-section">
        <div class="profile-card">
            <div class="profile-content">
                
                <div class="profile-info">
                    <div class="info-item">
                        <div class="info-label">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span>Name</span>
                        </div>
                        <?php echo $displayName; ?>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                            </svg>
                            <span>Email</span>
                        </div>
                        <?php echo $_SESSION['USER_EMAIL']; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

                <!-- Quick Stats Column for flashcard -->
                            <div class="quick-stats">
                                <div class="stat-card stat-primary quiz-card">
                                    <div class="quiz-card-header">
                                        <p class="quiz-card-title">Flashcard</p>
                                        <div class="stat-icon stat-icon-primary">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="quiz-separator"></div>
                                    <div class="quiz-split">
                                        <div class="quiz-split-item">
                                            <p class="quiz-split-label">Created</p>
                                            <p class="quiz-split-value" id="flashcards-created">5</p>
                                        </div>
                                        <div class="quiz-split-divider"></div>
                                        <div class="quiz-split-item">
                                            <p class="quiz-split-label">Taken</p>
                                            <p class="quiz-split-value" id="flashcards-taken">12</p>
                                        </div>
                                    </div>
                                </div>
                            </div>


            <!-- Quick Stats Column -->
            <div class="quick-stats">
                <div class="stat-card stat-primary quiz-card">
                    <div class="quiz-card-header">
                        <p class="quiz-card-title">Quiz</p>
                        <div class="stat-icon stat-icon-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="quiz-separator"></div>
                    <div class="quiz-split">
                        <div class="quiz-split-item">
                            <p class="quiz-split-label">Created</p>
                            <p class="quiz-split-value" id="quizzes-created">5</p>
                        </div>
                        <div class="quiz-split-divider"></div>
                        <div class="quiz-split-item">
                            <p class="quiz-split-label">Taken</p>
                            <p class="quiz-split-value" id="quizzes-taken">12</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card stat-accent">
                <div class="stat-content">
                    <div class="stat-text">
                        <p class="stat-label">Files Uploaded</p>
                        <p class="stat-value" id="files-uploaded">24</p>
                    </div>
                    <div class="stat-icon stat-icon-accent">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="stat-card stat-info">
                <div class="stat-content">
                    <div class="stat-text">
                        <p class="stat-label">Notes Created</p>
                        <p class="stat-value" id="notes-created">18</p>
                    </div>
                    <div class="stat-icon stat-icon-info">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="stat-card stat-success">
                <div class="stat-content">
                    <div class="stat-text">
                        <p class="stat-label">Quiz Average</p>
                        <p class="stat-value" id="quiz-accuracy">85%</p>
                    </div>
                    <div class="stat-icon stat-icon-success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="6"></circle>
                            <circle cx="12" cy="12" r="2"></circle>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accuracy Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h3 class="chart-title">Quiz Average Trend</h3>
                    <p class="chart-subtitle" id="current-month">January 2024</p>
                </div>
                <div class="chart-controls">
                    <button class="nav-button" id="prev-month" aria-label="Previous month">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <button class="nav-button" id="next-month" aria-label="Next month">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="accuracyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="script.js"></script>
    <?php include '../../shared-student/script.php'; ?>


</body>
</html>
