<?php
// view.php
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    header('Location: index.php');
    exit;
}

$set = getFlashcardSet($id);
$cards = getFlashcards($id);

if (!$set) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Flashcard Set - <?php echo htmlspecialchars($set['title']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .flashcard-container {
            max-width: 100%;
            padding: 0 15px;
            margin: 0 auto;
            height: calc(100vh - 150px);
            display: flex;
            flex-direction: column;
        }
        
        .flashcards-view {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 20px 0;
        }
        
        .flashcard-view {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            height: 400px;
            perspective: 1000px;
        }
        
        .flashcard-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.6s;
            transform-style: preserve-3d;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .flashcard-view.flipped .flashcard-inner {
            transform: rotateY(180deg);
        }
        
        .flashcard-front, .flashcard-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            padding: 30px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-radius: 10px;
            background: white;
        }
        
        .flashcard-back {
            transform: rotateY(180deg);
        }
        
        .card-content {
            font-size: 1.5rem;
            text-align: center;
            margin: 20px 0;
            overflow-y: auto;
            max-height: 70%;
            width: 100%;
        }
        
        .card-flip-hint {
            margin-top: auto;
            font-size: 0.9rem;
            color: white;
        }
        
        .flashcard-navigation {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            padding: 20px 0;
        }
        
       
        
        @media (max-width: 768px) {
            .flashcard-view {
                height: 300px;
            }
            
            .card-content {
                font-size: 1.2rem;
            }
        }
    </style>
    <?php include '../../shared-student/header.php'; ?>
</head>

<body style="min-height: 100vh; padding: 0; margin: 0; overflow-x: hidden;">
    <?php
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
    
    <div class="flashcard-container">
        <header style="padding: 20px 0;">
            <h1 style="margin: 0;"><?php echo htmlspecialchars($set['title']); ?></h1>
            <div class="header-actions" style="margin-top: 10px;">
                <a href="index.php" class="btn-secondary" style="background-color: #f8f9fa;
            color: #333; border: 1px solid #ddd; text-decoration: none;">Back to All Sets</a>
                <a href="edit.php?id=<?php echo $id; ?>"  class="btn-secondary" style="background-color: #f8f9fa;
            color: #333; border: 1px solid #ddd; text-decoration: none;">Edit Set</a>
            </div>
        </header>

        <div class="set-details">
            <p class="set-meta">
                <span class="card-count"><?php echo count($cards); ?> cards</span>
            </p>
        </div>

        <div class="flashcards-view">
            <?php if (count($cards) > 0): ?>
                <?php foreach ($cards as $index => $card): ?>
                    <div class="flashcard-view" data-index="<?php echo $index; ?>" style="<?php echo $index > 0 ? 'display: none;' : ''; ?>">
                        <div class="flashcard-inner">
                            <div class="flashcard-front">
                                <div class="card-number"><?php echo $index + 1; ?></div>
                                <div class="card-content"><?php echo htmlspecialchars($card['question']); ?></div>
                                <div class="card-flip-hint">Click to see answer</div>
                            </div>
                            <div class="flashcard-back">
                                <div class="card-number"><?php echo $index + 1; ?></div>
                                <div class="card-content"><?php echo htmlspecialchars($card['answer']); ?></div>
                                <div class="card-flip-hint">Click to see question</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>This flashcard set doesn't have any cards yet.</p>
                    <a href="edit.php?id=<?php echo $id; ?>" class="btn-primary">Add Cards</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (count($cards) > 0): ?>
            <div class="flashcard-navigation">
                <button id="prev-card" class="btn-secondary" style="background-color: #6366f1;" disabled>Previous</button>
                <span id="card-counter">Card 1 of <?php echo count($cards); ?></span>
                <button id="next-card" class="btn-secondary" style="background-color: #6366f1" <?php echo count($cards) <= 1 ? 'disabled' : ''; ?>>Next</button>
            </div>
        <?php endif; ?>
    </div>

    <div id="toast-container"></div>

    <script src="assets/js/toast.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.flashcard-view');
            let currentIndex = 0;

            // Show only the first card initially
            if (cards.length > 0) {
                // Add click event to flip cards
                cards.forEach(card => {
                    card.addEventListener('click', function() {
                        this.classList.toggle('flipped');
                    });
                });

                // Navigation buttons
                const prevBtn = document.getElementById('prev-card');
                const nextBtn = document.getElementById('next-card');
                const counter = document.getElementById('card-counter');

                prevBtn.addEventListener('click', function() {
                    if (currentIndex > 0) {
                        cards[currentIndex].classList.remove('flipped');
                        currentIndex--;
                        updateCardVisibility();
                        updateNavigationState();
                    }
                });

                nextBtn.addEventListener('click', function() {
                    if (currentIndex < cards.length - 1) {
                        cards[currentIndex].classList.remove('flipped');
                        currentIndex++;
                        updateCardVisibility();
                        updateNavigationState();
                    }
                });

                function updateCardVisibility() {
                    cards.forEach((card, index) => {
                        card.style.display = index === currentIndex ? 'block' : 'none';
                    });
                }

                function updateNavigationState() {
                    counter.textContent = `Card ${currentIndex + 1} of ${cards.length}`;
                    prevBtn.disabled = currentIndex === 0;
                    nextBtn.disabled = currentIndex === cards.length - 1;
                }
                
                // Handle keyboard navigation
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowLeft' && currentIndex > 0) {
                        prevBtn.click();
                    } else if (e.key === 'ArrowRight' && currentIndex < cards.length - 1) {
                        nextBtn.click();
                    } else if (e.key === ' ') {
                        e.preventDefault();
                        cards[currentIndex].click();
                    }
                });
            }
        });
    </script>
    <?php include '../../shared-student/script.php'; ?>
</body>
</html>