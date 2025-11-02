let extractedTerms = [];
let extractedFileName = '';

function initializePage() {
    // Get data from localStorage
    const termsJson = localStorage.getItem('extractedTerms');
    extractedFileName = localStorage.getItem('extractedFileName') || 'Extracted_Terms';
    
    if (!termsJson) {
        // If no extracted terms, redirect back to extractor
        window.location.href = '../ai-Tools/index.php';
        return;
    }
    
    try {
        extractedTerms = JSON.parse(termsJson);
        
        // Additional check: if extractedTerms is empty, redirect back
        if (!Array.isArray(extractedTerms) || extractedTerms.length === 0) {
            localStorage.removeItem('extractedTerms');
            localStorage.removeItem('extractedFileName');
            window.location.href = '../ai-Tools/index.php';
            return;
        }
        
        displayTermsPreview();
    } catch (error) {
        console.error('Error parsing extracted terms:', error);
        localStorage.removeItem('extractedTerms');
        localStorage.removeItem('extractedFileName');
        window.location.href = '../ai-Tools/index.php';
    }
}

function displayTermsPreview() {
    const termsList = document.getElementById('termsList');
    termsList.innerHTML = '';
    
    extractedTerms.forEach(term => {
        const termDiv = document.createElement('div');
        termDiv.className = 'term-preview';
        termDiv.innerHTML = `<strong>${escapeHtml(term.term)}</strong> – <span>${escapeHtml(term.definition)}</span>`;
        termsList.appendChild(termDiv);
    });
    
    // Update modal counts
    document.getElementById('quizTermCount').textContent = extractedTerms.length;
    document.getElementById('notesTermCount').textContent = extractedTerms.length;
    document.getElementById('flashcardsTermCount').textContent = extractedTerms.length;
    document.getElementById('notesTitle').textContent = extractedFileName;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function handleDownload() {
    let content = '';
    extractedTerms.forEach((term, index) => {
        content += `${index + 1}. ${term.term} - ${term.definition}\n\n`;
    });
    
    const blob = new Blob([content], { type: 'text/plain; charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${extractedFileName}_extracted_terms.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function openQuizModal() {
    document.getElementById('quizModal').classList.add('show');
}

function closeQuizModal() {
    document.getElementById('quizModal').classList.remove('show');
}

function confirmQuizConversion() {
    // Format terms for quiz import: "Term – Definition" format
    const quizCards = extractedTerms.map(term => ({
        term: term.term,
        description: term.definition,
        answerType: 'multiple'
    }));
    
    // Store in localStorage for quiz creation
    localStorage.setItem('importedCards', JSON.stringify(quizCards));
    
    // CLEAR EXTRACTED TERMS AFTER USE
    localStorage.removeItem('extractedTerms');
    localStorage.removeItem('extractedFileName');
    extractedTerms = []; // Clear the global variable
    
    // Show loading
    document.getElementById('quizLoading').style.display = 'block';
    
    // Redirect to quiz creation with import flag
    setTimeout(() => {
        window.location.href = '../../student/quiz/create-quiz.php?import=true';
    }, 500);
}

function openFlashcardsModal() {
    document.getElementById('flashcardsModal').classList.add('show');
}

function closeFlashcardsModal() {
    document.getElementById('flashcardsModal').classList.remove('show');
}

function confirmFlashcardsConversion() {
    // Format terms for flashcards import: "Term – Definition" format
    const flashcardData = extractedTerms.map(term => ({
        term: term.term,
        description: term.definition
    }));
    
    // Store in localStorage for flashcard creation
    localStorage.setItem('importedCards', JSON.stringify(flashcardData));
    
    // CLEAR EXTRACTED TERMS AFTER USE
    localStorage.removeItem('extractedTerms');
    localStorage.removeItem('extractedFileName');
    extractedTerms = []; // Clear the global variable
    
    // Show loading
    document.getElementById('flashcardsLoading').style.display = 'block';
    document.getElementById('flashcardsLoading').innerHTML = '<div class="spinner"></div><p>Creating flashcards...</p>';
    
    // Redirect to flashcard creation with import flag
    setTimeout(() => {
        window.location.href = '../../student/flashcard/create-deck.php?import=true';
    }, 500);
}

function openNotesModal() {
    document.getElementById('notesModal').classList.add('show');
}

function closeNotesModal() {
    document.getElementById('notesModal').classList.remove('show');
}

function confirmNotesConversion() {
    // Format terms as "Term - Definition" with double newlines
    const noteContent = extractedTerms
        .map(term => `${term.term} - ${term.definition}`)
        .join('\n\n');
    
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('title', extractedFileName);
    formData.append('content', noteContent);
    formData.append('color', 'default');
    
    document.getElementById('notesLoading').innerHTML = '<div class="spinner"></div><p>Creating note...</p>';
    document.getElementById('notesLoading').style.display = 'block';
    
    // CLEAR EXTRACTED TERMS AFTER USE (do it immediately since we're making a fetch request)
    localStorage.removeItem('extractedTerms');
    localStorage.removeItem('extractedFileName');
    extractedTerms = []; // Clear the global variable
    
    // Send to notes API
    fetch('../notes/notes_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to notes page
            setTimeout(() => {
                window.location.href = '../notes/';
            }, 1000);
        } else {
            alert('Error saving note: ' + (data.message || 'Unknown error'));
            document.getElementById('notesLoading').style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving note: ' + error.message);
        document.getElementById('notesLoading').style.display = 'none';
    });
}

// Process imported cards function (similar to your import-flashcards.js)
function processImportedCards() {
    const importedCardsJson = localStorage.getItem("importedCards");
    if (importedCardsJson) {
        try {
            const importedCards = JSON.parse(importedCardsJson);
            
            // Clear existing cards if we have imported ones
            if (importedCards.length > 0) {
                questionCards.innerHTML = "";
            }
            
            // Add each imported card
            importedCards.forEach((card, index) => {
                const answerType = card.answerType || 'multiple';

                const newCard = document.createElement("div");
                newCard.className = "card question-card mb-3";
                newCard.setAttribute("data-card-id", index + 1);
                newCard.setAttribute("draggable", "true");
                
                newCard.innerHTML = `
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="card-number">${index + 1}</span>
                        <div class="d-flex align-items-center">
                            <select class="form-select form-select-sm answer-type-select me-2" style="width: auto;">
                                <option value="multiple" ${answerType === 'multiple' ? 'selected' : ''}>Multiple Choice</option>
                                <option value="typed" ${answerType === 'typed' ? 'selected' : ''}>Typed Answer</option>
                                <option value="truefalse" ${answerType === 'truefalse' ? 'selected' : ''}>True/False</option>
                            </select>
                            <button class="btn btn-sm btn-light move-card-btn" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></button>
                            <button class="btn btn-sm btn-light delete-card-btn"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label">Answer (Term)</label>
                                <textarea class="form-control term-input auto-resize" placeholder="Enter the answer" rows="1">${card.term}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Question (Description)</label>
                                <textarea class="form-control description-input auto-resize" placeholder="Enter the question" rows="1">${card.description}</textarea>
                            </div>
                        </div>
                    </div>
                `;
                
                questionCards.appendChild(newCard);
                addCardEventListeners(newCard);
                initCardDragAndDrop(newCard);
                initAutoResizeForCard(newCard);
            });
            
            updateCardNumbers();
            localStorage.removeItem("importedCards");
            showSuccess(`Successfully imported ${importedCards.length} cards.`);
        } catch (error) {
            console.error("Error processing imported cards:", error);
        }
    }
}

// Helper functions for card management (from your import scripts)
function addCardEventListeners(card) {
    card.querySelector(".delete-card-btn").addEventListener("click", () => {
        card.remove();
        updateCardNumbers();
        hasUnsavedChanges = true;
    });
    
    const inputs = card.querySelectorAll('textarea, select');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            hasUnsavedChanges = true;
        });
    });
}

function initCardDragAndDrop(card) {
    card.addEventListener("dragstart", function (e) {
        draggedCard = this;
        setTimeout(() => {
            this.classList.add("dragging");
        }, 0);
    });

    card.addEventListener("dragend", function () {
        this.classList.remove("dragging");
        draggedCard = null;
        updateCardNumbers();
        hasUnsavedChanges = true;
    });

    card.addEventListener("dragover", function (e) {
        e.preventDefault();
        if (draggedCard !== this) {
            this.classList.add("drag-over");
        }
    });

    card.addEventListener("dragleave", function () {
        this.classList.remove("drag-over");
    });

    card.addEventListener("drop", function (e) {
        e.preventDefault();
        this.classList.remove("drag-over");

        if (draggedCard !== this) {
            const allCards = Array.from(document.querySelectorAll(".question-card"));
            const draggedIndex = allCards.indexOf(draggedCard);
            const targetIndex = allCards.indexOf(this);

            if (draggedIndex < targetIndex) {
                this.parentNode.insertBefore(draggedCard, this.nextSibling);
            } else {
                this.parentNode.insertBefore(draggedCard, this);
            }
            
            document.querySelectorAll('.question-card').forEach(card => {
                initAutoResizeForCard(card);
            });
        }
    });

    const moveBtn = card.querySelector(".move-card-btn");
    moveBtn.addEventListener("mousedown", (e) => {
        e.preventDefault();
        const event = new MouseEvent("dragstart", {
            bubbles: true,
            cancelable: true,
            view: window,
        });
        card.dispatchEvent(event);
    });
}

function updateCardNumbers() {
    const cards = document.querySelectorAll(".question-card");
    cards.forEach((card, index) => {
        card.setAttribute("data-card-id", index + 1);
        card.querySelector(".card-number").textContent = index + 1;
    });
}

function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}

function initAutoResizeForCard(card) {
    const textareas = card.querySelectorAll('textarea.auto-resize');
    textareas.forEach(textarea => {
        autoResizeTextarea(textarea);
        textarea.addEventListener('input', () => autoResizeTextarea(textarea));
        textarea.addEventListener('focus', () => autoResizeTextarea(textarea));
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initializePage);