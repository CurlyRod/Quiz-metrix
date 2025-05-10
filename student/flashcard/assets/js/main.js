// assets/js/main.js
document.addEventListener('DOMContentLoaded', function() {
    // Delete flashcard set
    const deleteButtons = document.querySelectorAll('.delete-set');
    
    if (deleteButtons) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const id = this.dataset.id;
                
                if (confirm('Are you sure you want to delete this flashcard set? This action cannot be undone.')) {
                    fetch('api/flashcards.php?action=delete_set', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Flashcard set deleted successfully', 'success');
                            
                            // Remove the card from the UI
                            const card = this.closest('.flashcard-set');
                            if (card) {
                                card.remove();
                                
                                // Check if there are no more cards
                                if (document.querySelectorAll('.flashcard-set').length === 0) {
                                    const container = document.querySelector('.flashcard-grid');
                                    container.innerHTML = `
                                        <div class="empty-state">
                                            <p>You don't have any flashcard sets yet.</p>
                                            <a href="create.php" class="btn-primary">Create your first set</a>
                                        </div>
                                    `;
                                }
                            }
                        } else {
                            showToast(data.message || 'Error deleting flashcard set', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred', 'error');
                    });
                }
            });
        });
    }
});