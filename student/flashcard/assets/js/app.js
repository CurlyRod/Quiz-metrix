// js/app.js
document.addEventListener('DOMContentLoaded', function() {
    const flashcardSystem = new Flashcards();
    
    // DOM Elements
    const flashcardElement = document.getElementById('flashcard');
    const cardTitleElement = document.getElementById('card-title');
    const currentCardElement = document.getElementById('current-card');
    const totalCardsElement = document.getElementById('total-cards');
    const cardContentElement = document.querySelector('.card-content p');
    const backContentElement = document.querySelector('.flashcard-back .card-content p');
    const btnCorrect = document.getElementById('btn-correct');
    const btnWrong = document.getElementById('btn-wrong');
    const hintButton = document.querySelector('.hint-button');
    const stillLearningCountElement = document.querySelector('.still-learning .status-count');
    const knowCountElement = document.querySelector('.know-count');
    
    // Initialize the UI
    function updateUI(card) {
        if (!card) return;
        
        cardTitleElement.textContent = card.title;
        currentCardElement.textContent = flashcardSystem.currentIndex + 1;
        totalCardsElement.textContent = flashcardSystem.totalCards;
        cardContentElement.textContent = card.front;
        backContentElement.textContent = card.back;
        
        // Update counters
        stillLearningCountElement.textContent = flashcardSystem.stillLearningCount;
        knowCountElement.textContent = flashcardSystem.knownCount;
    }
    
    // Initialize with current card
    updateUI(flashcardSystem.getCurrentCard());
    
    // Event Listeners
    flashcardElement.addEventListener('click', function() {
        flashcardElement.classList.toggle('flipped');
    });
    
    btnCorrect.addEventListener('click', function() {
        const nextCard = flashcardSystem.markAsKnown();
        flashcardElement.classList.remove('flipped');
        updateUI(nextCard);
    });
    
    btnWrong.addEventListener('click', function() {
        const nextCard = flashcardSystem.markAsStillLearning();
        flashcardElement.classList.remove('flipped');
        updateUI(nextCard);
    });
    
    hintButton.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent card from flipping
        alert(`Hint: ${flashcardSystem.getHint()}`);
    });
    
    // Prevent sound button from flipping card
    document.querySelector('.sound-button').addEventListener('click', function(e) {
        e.stopPropagation();
        // Text-to-speech functionality would go here
        const utterance = new SpeechSynthesisUtterance(cardContentElement.textContent);
        window.speechSynthesis.speak(utterance);
    });
    
    // Additional buttons
    document.querySelector('.fa-undo').parentElement.addEventListener('click', function() {
        const prevCard = flashcardSystem.prevCard();
        flashcardElement.classList.remove('flipped');
        updateUI(prevCard);
    });
    
    document.querySelector('.fa-random').parentElement.addEventListener('click', function() {
        const shuffledCard = flashcardSystem.shuffleCards();
        flashcardElement.classList.remove('flipped');
        updateUI(shuffledCard);
    });
});