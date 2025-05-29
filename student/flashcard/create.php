<?php
// create.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title >Create Flashcard Set</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="padding: 0;">
    <div class="container" style="margin-top: 50px;">
        <header>
            <h1 style="color: #6366f1">Create Flashcard Set</h1>
            <a href="index.php" class="btn-secondary">Back to All Sets</a>
        </header>
        
        <form id="create-set-form">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" placeholder="Enter flashcard set title" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Enter flashcard set description"></textarea>
            </div>
            
            <h2>Flashcards</h2>
            
            <div id="flashcards-container">
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
                            <label>Term (Answer)</label>
                            <input type="text" name="cards[0][answer]" placeholder="Enter the answer" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Definition (Question)</label>
                            <input type="text" name="cards[0][question]" placeholder="Enter the question" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="button" id="add-card" class="btn-secondary">+ Add Card</button>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary" style="background-color: #6366f1">Create Flashcard Set</button>
            </div>
        </form>
    </div>
    
    <div id="toast-container"></div>
    
    <script src="assets/js/toast.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let cardCount = 1;
            
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
            
            // Add event listeners to initial card
            const initialCard = document.querySelector('.flashcard-item');
            if (initialCard) {
                addCardEventListeners(initialCard);
            }
            
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
            document.getElementById('create-set-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const title = document.getElementById('title').value;
                const description = document.getElementById('description').value;
                
                if (!title) {
                    showToast('Title is required', 'error');
                    return;
                }
                
                // Create set first
                fetch('api/flashcards.php?action=create_set', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        title: title,
                        description: description
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const setId = data.id;
                        const cards = [];
                        
                        // Collect all cards
                        document.querySelectorAll('.flashcard-item').forEach((card, index) => {
                            const answer = card.querySelector('input[name$="[answer]"]').value;
                            const question = card.querySelector('input[name$="[question]"]').value;
                            
                            if (answer && question) {
                                cards.push({
                                    set_id: setId,
                                    answer: answer,
                                    question: question,
                                    position: index
                                });
                            }
                        });
                        
                        // Create all cards
                        const promises = cards.map(card => {
                            return fetch('api/flashcards.php?action=create_card', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(card)
                            })
                            .then(response => response.json());
                        });
                        
                        Promise.all(promises)
                            .then(() => {
                                showToast('Flashcard set created successfully', 'success');
                                setTimeout(() => {
                                    window.location.href = 'index.php';
                                }, 1500);
                            })
                            .catch(error => {
                                console.error('Error creating cards:', error);
                                showToast('Error creating cards', 'error');
                            });
                    } else {
                        showToast(data.message || 'Error creating flashcard set', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                });
            });
        });
    </script>
</body>
</html>