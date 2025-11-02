<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flashcards</title>
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
            <a class="nav-link active" href="index.php" style="color: #6366f1">Flashcards</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage-decks.php" style="color:rgba(99, 101, 241, 0.8)">Manage Decks</a>
        </li>
    </ul>

    <div class="main-content">
        <div class="flashcard-dashboard">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Flashcards</h3>
                <div>
                    <a href="create-deck.php" class="btn me-2" style="background-color: #6366f1; color: white;">Create Deck</a>
                </div>
            </div>
            
            <div class="alert alert-success d-none" id="successAlert"></div>
            <div class="alert alert-danger d-none" id="errorAlert"></div>
            
            <div class="recent-section mb-4">
                <h6 class="recent-header">Recent Decks</h6>
                <hr>
                <div class="row" id="recentDecks">
                    <div class="col-12 text-center">
                        <p>Loading recent decks...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <?php include '../../shared-student/script.php'; ?>
</body>
</html>
