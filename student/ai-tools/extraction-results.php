<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extraction Results</title>
    <?php include '../../shared-student/header.php'; ?>

    <link rel="stylesheet" href="css/extraction-results.css">
   
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
 

    ?>
    <div class="container">
        <a href="index.php" class="back-btn">Back to Extractor</a>
        
        <div class="header">
            <h1>Extraction Complete!</h1>
            <p>Choose what you'd like to do with your extracted terms</p>
        </div>
        
        <div class="cards-grid">
            <!-- Download Card -->
            <div class="card">
                <span class="card-icon">📥</span>
                <h2>Download Results</h2>
                <p>Save your extracted terms as a text file.</p>
                <button class="card-btn" onclick="handleDownload()">Download Now</button>
            </div>
            
            <!-- Convert to Quiz Card -->
            <div class="card">
                <span class="card-icon">🎯</span>
                <h2>Convert to Quiz</h2>
                <p>Transform your extracted terms into an interactive quiz you can study with.</p>
                <button class="card-btn" onclick="openQuizModal()">Create Quiz</button>
            </div>
            
            <!-- Convert to Flashcards Card -->
            <div class="card">
                <span class="card-icon">🃏</span>
                <h2>Convert to Flashcards</h2>
                <p>Transform your extracted terms into interactive flashcards for effective studying.</p>
                <button class="card-btn" onclick="openFlashcardsModal()">Create Flashcards</button>
            </div>
            
            <!-- Convert to Notes Card -->
            <div class="card">
                <span class="card-icon">📝</span>
                <h2>Convert to Notes</h2>
                <p>Save your extracted terms as organized notes for easy reference.</p>
                <button class="card-btn" onclick="openNotesModal()">Create Notes</button>
            </div>
        </div>
        
        <div class="results-preview">
            <h3> Extracted Terms Preview</h3>
            <div class="terms-list" id="termsList"></div>
        </div>
    </div>
    
    <!-- Quiz Modal -->
    <div class="modal" id="quizModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeQuizModal()">✕</span>
            <h2>Create Quiz</h2>
            <p>Your <span id="quizTermCount">0</span> extracted terms will be imported into a new quiz. You can edit them before starting.</p>
            <div id="quizLoading" class="loading"></div>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeQuizModal()">Cancel</button>
                <button class="btn-confirm" onclick="confirmQuizConversion()">Create Quiz</button>
            </div>
        </div>
    </div>
    
    <!-- Flashcards Modal -->
    <div class="modal" id="flashcardsModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeFlashcardsModal()">✕</span>
            <h2>Create Flashcards</h2>
            <p>Your <span id="flashcardsTermCount">0</span> extracted terms will be imported into a new flashcard deck. You can edit them before studying.</p>
            <div id="flashcardsLoading" class="loading"></div>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeFlashcardsModal()">Cancel</button>
                <button class="btn-confirm" onclick="confirmFlashcardsConversion()">Create Flashcards</button>
            </div>
        </div>
    </div>
    
    <!-- Notes Modal -->
    <div class="modal" id="notesModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeNotesModal()">✕</span>
            <h2>Create Notes</h2>
            <p>Your <span id="notesTermCount">0</span> extracted terms will be saved as a new note titled "<span id="notesTitle"></span>".</p>
            <div id="notesLoading" class="loading"></div>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeNotesModal()">Cancel</button>
                <button class="btn-confirm" onclick="confirmNotesConversion()">Create Notes</button>
            </div>
        </div>
    </div>

    <script src="js/extraction-results.js"></script>
</body>
</html>