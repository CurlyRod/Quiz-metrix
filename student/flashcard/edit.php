<?php
// edit.php
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
    <title>Edit Flashcard Set</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php include '../../shared-student/header.php'; ?>
</head>

<body style="padding: 0;">
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
    <div class="container" style="margin-top: 50px;">
        <header>
            <h1>Edit Flashcard Set</h1>
            <a href="index.php" class="btn-secondary">Back to All Sets</a>
        </header>

        <form id="edit-set-form" data-id="<?php echo $id; ?>">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($set['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($set['description']); ?></textarea>
            </div>

            <h2>Flashcards</h2>

            <div id="flashcards-container">
                <?php foreach ($cards as $index => $card): ?>
                    <div class="flashcard-item" data-position="<?php echo $index; ?>" data-id="<?php echo $card['id']; ?>">
                        <div class="flashcard-header">
                            <h3>Card <?php echo $index + 1; ?></h3>
                            <div class="card-actions">
                                <button type="button" class="btn-icon move-card">≡</button>
                                <button type="button" class="btn-icon delete-card">🗑️</button>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Term (Front)</label>
                                <input type="text" name="cards[<?php echo $index; ?>][answer]" value="<?php echo htmlspecialchars($card['answer']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Definition (Back)</label>
                                <input type="text" name="cards[<?php echo $index; ?>][question]" value="<?php echo htmlspecialchars($card['question']); ?>" required>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (count($cards) === 0): ?>
                    <div class="flashcard-item" data-position="0">
                        <div class="flashcard-header">
                            <h3>Card 1</h3>
                            <div class="card-actions">
                                <button type="button" class="btn-icon move-card">≡</button>
                                <button type="button" class="btn-icon delete-card">🗑️</button>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Term (Front)</label>
                                <input type="text" name="cards[0][answer]" placeholder="Enter the answer" required>
                            </div>

                            <div class="form-group">
                                <label>Definition (Back)</label>
                                <input type="text" name="cards[0][question]" placeholder="Enter the question" required>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <button type="button" id="add-card" class="btn-secondary">+ Add Card</button>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>

    <div id="toast-container"></div>

    <script src="assets/js/toast.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let cardCount = document.querySelectorAll('.flashcard-item').length;

            // Add new card
            document.getElementById('add-card').addEventListener('click', function() {
                cardCount++;
                const container = document.getElementById('flashcards-container');
                const newCard = document.createElement('div');
                newCard.className = 'flashcard-item';
                newCard.dataset.position = cardCount - 1;

                newCard.innerHTML = `
                    <div class="flashcard-header">
                        <h3>Card ${cardCount}</h3>
                        <div class="card-actions">
                            <button type="button" class="btn-icon move-card">≡</button>
                            <button type="button" class="btn-icon delete-card">🗑️</button>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Term (Answer)</label>
                            <input type="text" name="cards[${cardCount - 1}][answer]" placeholder="Enter the answer" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Definition (Question)</label>
                            <input type="text" name="cards[${cardCount - 1}][question]" placeholder="Enter the question" required>
                        </div>
                    </div>
                `;

                container.appendChild(newCard);

                // Add event listeners to the new card
                addCardEventListeners(newCard);
            });

            // Add event listeners to all cards
            document.querySelectorAll('.flashcard-item').forEach(card => {
                addCardEventListeners(card);
            });

            function addCardEventListeners(card) {
                // Delete card
                const deleteBtn = card.querySelector('.delete-card');
                if (deleteBtn) {
                    deleteBtn.addEventListener('click', function() {
                        if (document.querySelectorAll('.flashcard-item').length > 1) {
                            card.remove();
                            updateCardNumbers();
                        } else {
                            showToast('You must have at least one card', 'error');
                        }
                    });
                }

                // Move card functionality would be implemented here
                // This would require a more complex drag and drop implementation
            }

            function updateCardNumbers() {
                const cards = document.querySelectorAll('.flashcard-item');
                cards.forEach((card, index) => {
                    card.dataset.position = index;
                    card.querySelector('h3').textContent = `Card ${index + 1}`;

                    const answerInput = card.querySelector('input[name^="cards"][name$="[answer]"]');
                    const questionInput = card.querySelector('input[name^="cards"][name$="[question]"]');

                    if (answerInput) {
                        answerInput.name = `cards[${index}][answer]`;
                    }

                    if (questionInput) {
                        questionInput.name = `cards[${index}][question]`;
                    }
                });
            }

            // Form submission
            document.getElementById('edit-set-form').addEventListener('submit', function(e) {
                e.preventDefault();

                const setId = this.dataset.id;
                const title = document.getElementById('title').value;
                const description = document.getElementById('description').value;

                if (!title) {
                    showToast('Title is required', 'error');
                    return;
                }

                // Update set first
                fetch('api/flashcards.php?action=update_set', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: setId,
                            title: title,
                            description: description
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Handle existing cards (update or delete)
                            const existingCardIds = [];
                            document.querySelectorAll('.flashcard-item[data-id]').forEach(card => {
                                existingCardIds.push(card.dataset.id);
                            });

                            // Collect all cards to update or create
                            const cardsToUpdate = [];
                            const cardsToCreate = [];

                            document.querySelectorAll('.flashcard-item').forEach((card, index) => {
                                const answer = card.querySelector('input[name$="[answer]"]').value;
                                const question = card.querySelector('input[name$="[question]"]').value;

                                if (answer && question) {
                                    if (card.dataset.id) {
                                        cardsToUpdate.push({
                                            id: card.dataset.id,
                                            question: question,
                                            answer: answer,
                                            position: index
                                        });
                                    } else {
                                        cardsToCreate.push({
                                            set_id: setId,
                                            question: question,
                                            answer: answer,
                                            position: index
                                        });
                                    }
                                }
                            });

                            // Update existing cards
                            const updatePromises = cardsToUpdate.map(card => {
                                return fetch('api/flashcards.php?action=update_card', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify(card)
                                    })
                                    .then(response => response.json());
                            });

                            // Create new cards
                            const createPromises = cardsToCreate.map(card => {
                                return fetch('api/flashcards.php?action=create_card', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify(card)
                                    })
                                    .then(response => response.json());
                            });

                            Promise.all([...updatePromises, ...createPromises])
                                .then(() => {
                                    showToast('Flashcard set updated successfully', 'success');
                                    setTimeout(() => {
                                        window.location.href = 'index.php';
                                    }, 1500);
                                })
                                .catch(error => {
                                    console.error('Error updating cards:', error);
                                    showToast('Error updating cards', 'error');
                                });
                        } else {
                            showToast(data.message || 'Error updating flashcard set', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred', 'error');
                    });
            });
        });
    </script>
    <?php include '../../shared-student/script.php'; ?>
</body>

</html>