// Global variables
let exitWarningModal;
let exitDestination = '';
let isQuizSubmitted = false;
let hasUnsavedChanges = false;
let quizSettingsModal;

// Quiz data
let quizData = {
    title: "",
    description: "",
    questions: [],
    settings: {
        timed: false,
        time: 5,
        answerTypes: ["multiple"]
    },
}

// Global answer types setting
let globalAnswerTypes = ["multiple"];

// Drag and drop variables
let draggedCard = null;

document.addEventListener("DOMContentLoaded", () => {
    // DOM Elements
    const createQuizBtn = document.getElementById("createQuizBtn")
    const clearFormBtn = document.getElementById("clearFormBtn")
    const addCardBtn = document.getElementById("addCardBtn")
    const questionCards = document.getElementById("questionCards")
    const startQuizBtn = document.getElementById("startQuizBtn")
    const successAlert = document.getElementById("successAlert")
    const errorAlert = document.getElementById("errorAlert")
    const importQuestionsBtn = document.getElementById("importQuestionsBtn")
    const quizSettingsBtn = document.getElementById("quizSettingsBtn")
    const saveQuizSettingsBtn = document.getElementById("saveQuizSettingsBtn")
    const answerTypeCheckboxes = document.querySelectorAll(".answer-type-checkbox")
    const answerTypeWarning = document.getElementById("answerTypeWarning")
    const timedQuizSwitch = document.getElementById("timedQuizSwitch")
    const timerSettings = document.getElementById("timerSettings")
    const quizTimeInput = document.getElementById("quizTime")

    quizData.user_current_id = document.getElementById("user-current-id").value;

    // Check if we're editing an existing quiz or coming from import
    const urlParams = new URLSearchParams(window.location.search)
    const quizId = urlParams.get("id")
    const importFlag = urlParams.get("import")

    if (quizId) {
        // Load quiz for editing
        loadQuiz(quizId)
    } else if (importFlag === "true") {
        // Process imported cards
        processImportedCards()
    } else {
        // Add default question cards
        for (let i = 0; i < 5; i++) {
            addNewCard()
        }
    }

    // Initialize exit warning modal
    initExitWarningModal();
    setupNavigationInterception();
    setupChangeTracking();

    // Initialize event listeners
    initEventListeners()

    function initEventListeners() {
        // Add new card button
        addCardBtn.addEventListener("click", addNewCard)

        // Import questions button
        if (importQuestionsBtn) {
            importQuestionsBtn.addEventListener("click", () => {
                window.location.href = "import-questions.php"
            })
        }

        // Quiz Settings button
        quizSettingsBtn.addEventListener("click", () => {
            // Set the checkboxes to match current global answer types
            answerTypeCheckboxes.forEach(checkbox => {
                checkbox.checked = globalAnswerTypes.includes(checkbox.value);
            });
            
            // Set timer settings to match current quiz data
            timedQuizSwitch.checked = quizData.settings.timed || false;
            if (quizData.settings.timed) {
                timerSettings.classList.remove("d-none");
                quizTimeInput.value = quizData.settings.time || 5;
            } else {
                timerSettings.classList.add("d-none");
            }
            
            answerTypeWarning.classList.add("d-none");
            
            quizSettingsModal = new bootstrap.Modal(document.getElementById("quizSettingsModal"))
            quizSettingsModal.show()
        })

        // Timed quiz switch inside modal
        timedQuizSwitch.addEventListener("change", function () {
            if (this.checked) {
                timerSettings.classList.remove("d-none");
            } else {
                timerSettings.classList.add("d-none");
            }
        });

        // Save Quiz Settings button
        saveQuizSettingsBtn.addEventListener("click", saveQuizSettings)

        // Create quiz button
        createQuizBtn.addEventListener("click", () => {
            // First save the quiz
            createQuiz(true)
        })

        // Start quiz button - now directly saves and starts without modal
        startQuizBtn.addEventListener("click", () => {
            startQuiz()
        })
    }

    function saveQuizSettings() {
        const selectedTypes = getSelectedAnswerTypes()
        
        // Validate that at least one type is selected
        if (selectedTypes.length === 0) {
            answerTypeWarning.classList.remove("d-none")
            return
        }

        // Update global answer types
        globalAnswerTypes = selectedTypes
        
        // Update quiz data settings
        quizData.settings.answerTypes = globalAnswerTypes;
        quizData.settings.timed = timedQuizSwitch.checked;
        quizData.settings.time = parseInt(quizTimeInput.value) || 5;
        
        // Apply the new answer types to all existing cards
        applyAnswerTypesToAllCards()
        
        // Close the modal
        quizSettingsModal.hide()
        
        // Show success message
        showSuccess("Quiz settings updated successfully!")
    }

    function getSelectedAnswerTypes() {
        const selectedTypes = []
        answerTypeCheckboxes.forEach((checkbox) => {
            if (checkbox.checked) {
                selectedTypes.push(checkbox.value)
            }
        })
        return selectedTypes
    }

    function applyAnswerTypesToAllCards() {
        const cards = document.querySelectorAll('.question-card');
        
        cards.forEach(card => {
            const answerTypeSelect = card.querySelector('.answer-type-select');
            if (answerTypeSelect) {
                // If only one type is selected globally, set all cards to that type
                if (globalAnswerTypes.length === 1) {
                    answerTypeSelect.value = globalAnswerTypes[0];
                } else {
                    // Randomly assign one of the selected types
                    const randomIndex = Math.floor(Math.random() * globalAnswerTypes.length);
                    answerTypeSelect.value = globalAnswerTypes[randomIndex];
                }
            }
        });
    }

    // Auto-resize function for textareas
    function autoResizeTextarea(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }

    // Initialize auto-resize for all textareas in a card
    function initAutoResizeForCard(card) {
        const textareas = card.querySelectorAll('textarea.auto-resize');
        textareas.forEach(textarea => {
            autoResizeTextarea(textarea);
            textarea.addEventListener('input', () => autoResizeTextarea(textarea));
            textarea.addEventListener('focus', () => autoResizeTextarea(textarea));
        });
    }

    // Process imported cards from localStorage if available
    function processImportedCards() {
        const importedCardsJson = localStorage.getItem("importedCards")
        if (importedCardsJson) {
            try {
                const importedCards = JSON.parse(importedCardsJson)
                
                // Clear existing cards if we have imported ones
                if (importedCards.length > 0) {
                    questionCards.innerHTML = ""
                }
                
                // Add each imported card
                importedCards.forEach((card, index) => {
                    const answerType = card.answerType || 
                        (globalAnswerTypes.length === 1 ? globalAnswerTypes[0] : 
                         globalAnswerTypes[Math.floor(Math.random() * globalAnswerTypes.length)]);

                    const newCard = document.createElement("div")
                    newCard.className = "card question-card mb-3"
                    newCard.setAttribute("data-card-id", index + 1)
                    newCard.setAttribute("draggable", "true")
                    
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
                    `
                    
                    questionCards.appendChild(newCard)
                    addCardEventListeners(newCard)
                    initCardDragAndDrop(newCard)
                    initAutoResizeForCard(newCard)
                })
                
                updateCardNumbers()
                localStorage.removeItem("importedCards")
                showSuccess(`Successfully imported ${importedCards.length} cards.`)
            } catch (error) {
                console.error("Error processing imported cards:", error)
            }
        }
    }

    function addNewCard() {
        const cardCount = document.querySelectorAll(".question-card").length
        const newCardId = cardCount + 1

        // Determine the answer type for this new card
        let answerType;
        if (globalAnswerTypes.length === 1) {
            answerType = globalAnswerTypes[0];
        } else {
            // Randomly assign one of the selected types
            const randomIndex = Math.floor(Math.random() * globalAnswerTypes.length);
            answerType = globalAnswerTypes[randomIndex];
        }

        const newCard = document.createElement("div")
        newCard.className = "card question-card mb-3"
        newCard.setAttribute("data-card-id", newCardId)
        newCard.setAttribute("draggable", "true")

        newCard.innerHTML = `
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-number">${newCardId}</span>
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
                        <textarea class="form-control term-input auto-resize" placeholder="Enter the answer" rows="1"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Question (Description)</label>
                        <textarea class="form-control description-input auto-resize" placeholder="Enter the question" rows="1"></textarea>
                    </div>
                </div>
            </div>
        `

        questionCards.appendChild(newCard)
        addCardEventListeners(newCard)
        initCardDragAndDrop(newCard)
        initAutoResizeForCard(newCard)
        updateCardNumbers()
        
        // Mark as having changes
        hasUnsavedChanges = true;
    }

    function addCardEventListeners(card) {
        card.querySelector(".delete-card-btn").addEventListener("click", () => {
            card.remove()
            updateCardNumbers()
            hasUnsavedChanges = true;
        })
        
        // Add change listeners to inputs
        const inputs = card.querySelectorAll('textarea, select');
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                hasUnsavedChanges = true;
            });
        });
    }

    function initCardDragAndDrop(card) {
        card.addEventListener("dragstart", function (e) {
            draggedCard = this
            setTimeout(() => {
                this.classList.add("dragging")
            }, 0)
        })

        card.addEventListener("dragend", function () {
            this.classList.remove("dragging")
            draggedCard = null
            updateCardNumbers()
            hasUnsavedChanges = true;
        })

        card.addEventListener("dragover", function (e) {
            e.preventDefault()
            if (draggedCard !== this) {
                this.classList.add("drag-over")
            }
        })

        card.addEventListener("dragleave", function () {
            this.classList.remove("drag-over")
        })

        card.addEventListener("drop", function (e) {
            e.preventDefault()
            this.classList.remove("drag-over")

            if (draggedCard !== this) {
                const allCards = Array.from(document.querySelectorAll(".question-card"))
                const draggedIndex = allCards.indexOf(draggedCard)
                const targetIndex = allCards.indexOf(this)

                if (draggedIndex < targetIndex) {
                    this.parentNode.insertBefore(draggedCard, this.nextSibling)
                } else {
                    this.parentNode.insertBefore(draggedCard, this)
                }
                
                document.querySelectorAll('.question-card').forEach(card => {
                    initAutoResizeForCard(card);
                });
            }
        })

        const moveBtn = card.querySelector(".move-card-btn")
        moveBtn.addEventListener("mousedown", (e) => {
            e.preventDefault()
            const event = new MouseEvent("dragstart", {
                bubbles: true,
                cancelable: true,
                view: window,
            })
            card.dispatchEvent(event)
        })
    }

    function updateCardNumbers() {
        const cards = document.querySelectorAll(".question-card")
        cards.forEach((card, index) => {
            card.setAttribute("data-card-id", index + 1)
            card.querySelector(".card-number").textContent = index + 1
        })
    }

    function createQuiz(showSettingsModal = false) {
        quizData.title = document.getElementById("quizTitle").value || "Untitled Quiz";
        quizData.description = document.getElementById("quizDescription").value || "No description provided";
        quizData.user_current_id = document.getElementById("user-current-id").value;

        // Ensure answer types are included in settings
        quizData.settings.answerTypes = globalAnswerTypes;

        // Get questions with their answer types
        quizData.questions = [];
        document.querySelectorAll(".question-card").forEach((card) => {
            const term = card.querySelector(".term-input").value;
            const description = card.querySelector(".description-input").value;
            const answerType = card.querySelector(".answer-type-select").value;

            if (term || description) {
                quizData.questions.push({
                    term: term || "No answer provided",
                    description: description || "No question provided",
                    answerType: answerType
                });
            }
        });

        if (quizData.questions.length <= 3) {
            showError("Please add at least 4 questions to your quiz.");
            return;
        }

        if (quizId) {
            quizData.quiz_id = quizId;
        }

        fetch("api/save_quiz.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(quizData),
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                quizData.quiz_id = data.quiz_id;
                // Reset change tracking after successful save
                hasUnsavedChanges = false;
                isQuizSubmitted = true;
                
                if (showSettingsModal) {
                    clearForm();
                    showSuccess("Quiz saved successfully!");
                    setTimeout(() => {
                        window.location.href = "index.php";
                    }, 1500);
                }
            } else {
                showError("Error saving quiz: " + data.message);
            }
        })
    }

    function startQuiz() {
        // Save the quiz first, then redirect to quiz page
        quizData.title = document.getElementById("quizTitle").value || "Untitled Quiz";
        quizData.description = document.getElementById("quizDescription").value || "No description provided";
        quizData.user_current_id = document.getElementById("user-current-id").value;

        // Ensure settings are up to date
        quizData.settings.answerTypes = globalAnswerTypes;

        // Get questions with their answer types
        quizData.questions = [];
        document.querySelectorAll(".question-card").forEach((card) => {
            const term = card.querySelector(".term-input").value;
            const description = card.querySelector(".description-input").value;
            const answerType = card.querySelector(".answer-type-select").value;

            if (term || description) {
                quizData.questions.push({
                    term: term || "No answer provided",
                    description: description || "No question provided",
                    answerType: answerType
                });
            }
        });

        if (quizData.questions.length <= 3) {
            showError("Please add at least 4 questions to your quiz.");
            return;
        }

        if (quizId) {
            quizData.quiz_id = quizId;
        }

        fetch("api/save_quiz.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(quizData),
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                // Reset change tracking
                hasUnsavedChanges = false;
                isQuizSubmitted = true;
                
                // Redirect directly to quiz page without showing modal
                window.location.href = "quiz.php?id=" + data.quiz_id;
            } else {
                showError("Error starting quiz: " + data.message);
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showError("Error starting quiz. Please try again.");
        });
    }

    function loadQuiz(quizId) {
        fetch(`api/get_quiz.php?id=${quizId}`)
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    populateQuizForm(data.quiz)
                } else {
                    showError("Error loading quiz: " + data.message)
                }
            })
    }

    function populateQuizForm(quiz) {
        document.getElementById("quizTitle").value = quiz.title
        document.getElementById("quizDescription").value = quiz.description
        questionCards.innerHTML = ""

        // Load saved answer types if they exist in settings
        if (quiz.settings && quiz.settings.answerTypes) {
            globalAnswerTypes = quiz.settings.answerTypes;
        } else {
            // Default to multiple choice if no answer types are saved
            globalAnswerTypes = ["multiple"];
        }

        // Load timer settings
        if (quiz.settings) {
            quizData.settings.timed = quiz.settings.timed || false;
            quizData.settings.time = quiz.settings.time || 5;
        }

        quiz.questions.forEach((question, index) => {
            const answerType = question.answerType || 'multiple';
            
            const newCard = document.createElement("div")
            newCard.className = "card question-card mb-3"
            newCard.setAttribute("data-card-id", index + 1)
            newCard.setAttribute("draggable", "true")

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
                            <textarea class="form-control term-input auto-resize" placeholder="Enter the answer" rows="1">${question.term}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Question (Description)</label>
                            <textarea class="form-control description-input auto-resize" placeholder="Enter the question" rows="1">${question.description}</textarea>
                        </div>
                    </div>
                </div>
            `

            questionCards.appendChild(newCard)
            addCardEventListeners(newCard)
            initCardDragAndDrop(newCard)
            initAutoResizeForCard(newCard)
        })

        if (quiz.questions.length === 0) {
            for (let i = 0; i < 5; i++) {
                addNewCard()
            }
        }

        quizData = quiz
        // Reset change tracking when loading an existing quiz
        hasUnsavedChanges = false;
    }

    function clearForm() {
        if (quizData.questions.length === 0) {
            showError("No Form to clear.")
            return
        }
        document.getElementById("quizTitle").value = ""
        document.getElementById("quizDescription").value = ""
        questionCards.innerHTML = ""

        for (let i = 0; i < 5; i++) {
            addNewCard()
        }

        quizData = {
            title: "",
            description: "",
            questions: [],
            settings: {
                timed: false,
                time: 5,
                answerTypes: ["multiple"]
            },
        }

        // Reset global answer types
        globalAnswerTypes = ["multiple"];

        // Reset change tracking
        hasUnsavedChanges = false;

        showSuccess("Form cleared successfully.")
    }

    function showSuccess(message) {
        if (successAlert) {
            successAlert.textContent = message;
            successAlert.classList.remove("d-none");
            if (errorAlert) errorAlert.classList.add("d-none");
            setTimeout(() => {
                successAlert.classList.add("d-none");
            }, 3000);
        }
    }

    function showError(message) {
        if (errorAlert) {
            errorAlert.textContent = message;
            errorAlert.classList.remove("d-none");
            if (successAlert) successAlert.classList.add("d-none");
            setTimeout(() => {
                errorAlert.classList.add("d-none");
            }, 5000);
        }
    }
})

// Exit Warning Functions
function hasQuizChanges() {
    // Check if title or description has been modified
    const title = document.getElementById("quizTitle").value;
    const description = document.getElementById("quizDescription").value;
    
    if (title || description) {
        return true;
    }
    
    // Check if any question card has content
    const cards = document.querySelectorAll('.question-card');
    for (let card of cards) {
        const term = card.querySelector('.term-input').value;
        const description = card.querySelector('.description-input').value;
        
        if (term || description) {
            return true;
        }
    }
    
    return false;
}

function setupNavigationInterception() {
    // Use event delegation instead of adding listeners to each link
    document.body.addEventListener('click', function(e) {
        if (isQuizSubmitted) return;
        
        const link = e.target.closest('a');
        if (!link || link.id === 'confirmExitBtn') return;
        
        const href = link.getAttribute('href');
        if (!href || href === '#' || href.startsWith('javascript:')) return;
        
        // Check if there are unsaved changes
        if (hasQuizChanges()) {
            e.preventDefault();
            exitDestination = href;
            exitWarningModal.show();
        }
    });

    // Handle beforeunload event for browser navigation
    window.addEventListener('beforeunload', function(e) {
        if (!isQuizSubmitted && hasQuizChanges()) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });
}

function setupChangeTracking() {
    const formInputs = document.querySelectorAll('#quizTitle, #quizDescription, .term-input, .description-input, .answer-type-select');
    
    formInputs.forEach(input => {
        input.addEventListener('input', function() {
            hasUnsavedChanges = true;
        });
    });
    
    // track changes when cards are added/deleted/reordered
    const observer = new MutationObserver(function() {
        hasUnsavedChanges = true;
    });
    
    observer.observe(document.getElementById('questionCards'), {
        childList: true,
        subtree: true
    });
}

function initExitWarningModal() {
    exitWarningModal = new bootstrap.Modal(document.getElementById("exitWarningModal"), {
        backdrop: 'static',
        keyboard: false
    });
    
    // Handle confirm exit button click
    document.getElementById('confirmExitBtn').addEventListener('click', function(e) {
        e.preventDefault();
        isQuizSubmitted = true; // Prevent further warnings
        window.location.href = exitDestination;
    });
}