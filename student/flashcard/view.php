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
    <?php include '../../shared-student/header.php'; ?>
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
        <header>
            <h1><?php echo htmlspecialchars($set['title']); ?></h1>
            <div class="header-actions">
                <a href="index.php" style="text-decoration: none;" class="btn-secondary">Back to All Sets</a>
                <a href="edit.php?id=<?php echo $id; ?>" style="text-decoration: none;" class="btn-secondary">Edit Set</a>
            </div>
        </header>

        <div class="set-details">
            <!-- <p class="set-description"><?php echo htmlspecialchars($set['description']); ?></p> -->
            <p class="set-meta">
                <span class="card-count"><?php echo count($cards); ?> cards</span>
            </p>
        </div>

        <!-- <h2>Flashcards</h2> -->

        <div class="flashcards-view">
            <?php if (count($cards) > 0): ?>
                <?php foreach ($cards as $index => $card): ?>
                    <div class="flashcard-view" data-index="<?php echo $index; ?>">
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
                <button id="prev-card" class="btn-secondary" disabled>Previous</button>
                <span id="card-counter">Card 1 of <?php echo count($cards); ?></span>
                <button id="next-card" class="btn-secondary" <?php echo count($cards) <= 1 ? 'disabled' : ''; ?>>Next</button>
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
                updateCardVisibility();

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
                        currentIndex--;
                        updateCardVisibility();
                        updateNavigationState();
                    }
                });

                nextBtn.addEventListener('click', function() {
                    if (currentIndex < cards.length - 1) {
                        currentIndex++;
                        updateCardVisibility();
                        updateNavigationState();
                    }
                });

                function updateCardVisibility() {
                    cards.forEach((card, index) => {
                        if (index === currentIndex) {
                            card.style.display = 'block';
                            // Reset flip state when changing cards
                            card.classList.remove('flipped');
                        } else {
                            card.style.display = 'none';
                        }
                    });
                }

                function updateNavigationState() {
                    counter.textContent = `Card ${currentIndex + 1} of ${cards.length}`;
                    prevBtn.disabled = currentIndex === 0;
                    nextBtn.disabled = currentIndex === cards.length - 1;
                }
            }
        });
    </script>
    <?php include '../../shared-student/script.php'; ?>
</body>

</html>