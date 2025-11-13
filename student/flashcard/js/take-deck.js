let currentDeck = null
let currentCardIndex = 0
let flashcards = []
let sessionId = null
let cardProgress = {}
let isFlipped = false
let trackProgress = true
let studyMode = "sequence"
let showBackOnLoad = false
let exitWarningModal = null;

document.addEventListener("DOMContentLoaded", () => {
  const urlParams = new URLSearchParams(window.location.search)
  const deckId = urlParams.get("id")

  if (!deckId) {
    alert("No deck selected")
    window.location.href = "index.php"
    return
  }

  sessionId = "session_" + Date.now() + "_" + Math.random().toString(36).substr(2, 9)
  
  loadDeck(deckId)
  setupEventListeners()
  setupKeyboardShortcuts()
})

function loadDeck(deckId) {
  fetch(`api/get_deck.php?id=${deckId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        currentDeck = data.deck
        flashcards = [...data.deck.flashcards]

        // Load settings from database
        if (data.deck.settings) {
          const settings = typeof data.deck.settings === 'string' 
            ? JSON.parse(data.deck.settings) 
            : data.deck.settings
          
          trackProgress = settings.trackProgress !== false
          studyMode = settings.studyMode || "sequence"
          showBackOnLoad = settings.showBackOnLoad || false
        }

        // Shuffle if randomized - DO THIS BEFORE loading saved progress
        if (studyMode === "randomized") {
          flashcards = shuffleArray(flashcards)
        }

        // Try to load saved progress from localStorage
        const savedProgress = loadProgressFromStorage(deckId)
        if (savedProgress) {
          currentCardIndex = savedProgress.currentCardIndex
          cardProgress = savedProgress.cardProgress
          sessionId = savedProgress.sessionId
          
          // If we have saved progress and the mode is randomized, 
          // we need to maintain the same card order from the saved session
          if (studyMode === "randomized" && savedProgress.flashcards) {
            flashcards = savedProgress.flashcards
          }
        } else {
          // Initialize fresh progress
          flashcards.forEach((card) => {
            cardProgress[card.flashcard_id] = null
          })
        }

        document.getElementById("deckTitle").textContent = currentDeck.title
        
        // Initialize the exit warning modal
        initExitWarningModal();
        
        updateUI()
        setupControlsBasedOnMode()
        applyCardDisplaySetting()
      } else {
        alert("Error loading deck: " + data.message)
        window.location.href = "index.php"
      }
    })
}

// Save progress to localStorage
function saveProgressToStorage(deckId) {
  const progress = {
    currentCardIndex,
    cardProgress,
    sessionId,
    flashcards: studyMode === "randomized" ? flashcards : null,
    timestamp: Date.now()
  }
  localStorage.setItem(`deck_progress_${deckId}`, JSON.stringify(progress))
}

// Load progress from localStorage
function loadProgressFromStorage(deckId) {
  const saved = localStorage.getItem(`deck_progress_${deckId}`)
  if (!saved) return null
  
  const progress = JSON.parse(saved)
  
  // Check if progress is not too old (24 hours)
  const isExpired = Date.now() - progress.timestamp > 24 * 60 * 60 * 1000
  if (isExpired) {
    localStorage.removeItem(`deck_progress_${deckId}`)
    return null
  }
  
  return progress
}

// Clear progress from localStorage
function clearProgressFromStorage(deckId) {
  localStorage.removeItem(`deck_progress_${deckId}`)
}

function setupControlsBasedOnMode() {
  const trackProgressControls = document.getElementById("trackProgressControls")
  const standardControls = document.getElementById("standardControls")

  if (trackProgress) {
    trackProgressControls.classList.remove("d-none")
    standardControls.classList.add("d-none")
  } else {
    trackProgressControls.classList.add("d-none")
    standardControls.classList.remove("d-none")
  }
}

function setupEventListeners() {
  document.getElementById("flashcardInner").addEventListener("click", flipCard)
  document.getElementById("prevBtn")?.addEventListener("click", previousCard)
  document.getElementById("nextBtn")?.addEventListener("click", nextCard)
  document.getElementById("dontKnowBtn")?.addEventListener("click", () => markCard("unknown"))
  document.getElementById("knowBtn")?.addEventListener("click", () => markCard("known"))
  document.getElementById("exitBtn").addEventListener("click", showExitWarning)
  document.getElementById("closeResultsBtn").addEventListener("click", closeDeck)
  document.getElementById("retryBtn").addEventListener("click", retryDeck)
  document.getElementById("unknownOnlyBtn").addEventListener("click", reviewUnknownOnly)
  
  // Completion modal event listeners
  document.getElementById("closeCompletionBtn").addEventListener("click", closeDeck)
  document.getElementById("retryCompletionBtn").addEventListener("click", retryCompletion)
  
  // Exit warning modal event listener
  document.getElementById("confirmExitBtn").addEventListener("click", confirmExit)
}

function shuffleArray(array) {
    const shuffled = [...array];
    for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }
    return shuffled;
}

function initExitWarningModal() {
  exitWarningModal = new bootstrap.Modal(document.getElementById('exitWarningModal'), {
    backdrop: 'static',
    keyboard: false
  });
}

function setupKeyboardShortcuts() {
  document.addEventListener("keydown", (e) => {
    if (e.code === "Space") {
      e.preventDefault()
      flipCard()
    } else if (e.code === "ArrowLeft") {
      e.preventDefault()
      if (trackProgress) {
        markCard("unknown")
      } else {
        previousCard()
      }
    } else if (e.code === "ArrowRight") {
      e.preventDefault()
      if (trackProgress) {
        markCard("known")
      } else {
        nextCard()
      }
    } else if (e.code === "KeyD" && trackProgress) {
      e.preventDefault()
      markCard("unknown")
    } else if (e.code === "KeyK" && trackProgress) {
      e.preventDefault()
      markCard("known")
    }
  })
}

function flipCard() {
  isFlipped = !isFlipped
  const inner = document.getElementById("flashcardInner")
  if (isFlipped) {
    inner.style.transform = "rotateY(180deg)"
  } else {
    inner.style.transform = "rotateY(0deg)"
  }
}

function applyCardDisplaySetting() {
  if (showBackOnLoad) {
    isFlipped = true
    document.getElementById("flashcardInner").style.transform = "rotateY(180deg)"
  } else {
    isFlipped = false
    document.getElementById("flashcardInner").style.transform = "rotateY(0deg)"
  }
}

function markCard(status) {
  const card = flashcards[currentCardIndex]
  
  // Only track progress if trackProgress is enabled
  if (trackProgress) {
    cardProgress[card.flashcard_id] = status

    // Save progress to localStorage
    saveProgressToStorage(currentDeck.deck_id)

    // Save to database
    fetch("api/save_flashcard_progress.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        session_id: sessionId,
        deck_id: currentDeck.deck_id,
        flashcard_id: card.flashcard_id,
        status: status,
      }),
    })
  }

  isFlipped = false
  if (currentCardIndex < flashcards.length - 1) {
    currentCardIndex++
    updateUI()
  } else {
    handleSessionCompletion()
  }
}

function previousCard() {
  if (currentCardIndex > 0) {
    currentCardIndex--
    isFlipped = false
    updateUI()
    saveProgressToStorage(currentDeck.deck_id)
  }
}

function nextCard() {
  if (currentCardIndex < flashcards.length - 1) {
    currentCardIndex++
    isFlipped = false
    updateUI()
    saveProgressToStorage(currentDeck.deck_id)
  } else {
    handleSessionCompletion()
  }
}

function handleSessionCompletion() {
  // Clear progress from storage since session is complete
  clearProgressFromStorage(currentDeck.deck_id)

  // Show different modals based on trackProgress setting
  if (trackProgress) {
    // Show results modal with statistics
    const knownCount = Object.values(cardProgress).filter((s) => s === "known").length
    const unknownCount = Object.values(cardProgress).filter((s) => s === "unknown").length
    const total = flashcards.length
    const percent = Math.round((knownCount / total) * 100)

    // Save results
    fetch("api/save_flashcard_results.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        session_id: sessionId,
        deck_id: currentDeck.deck_id,
        total_cards: total,
        known_count: knownCount,
        unknown_count: unknownCount,
        percent_known: percent,
        mode: studyMode,
      }),
    })

    document.getElementById("percentKnown").textContent = percent + "%"
    document.getElementById("totalCardsResult").textContent = total
    document.getElementById("knownCountResult").textContent = knownCount
    document.getElementById("unknownCountResult").textContent = unknownCount

    const modal = new bootstrap.Modal(document.getElementById("resultsModal"))
    modal.show()
  } else {
    // Show simple completion modal
    const modal = new bootstrap.Modal(document.getElementById("completionModal"))
    modal.show()
  }
}

function updateUI() {
  const card = flashcards[currentCardIndex]
  
  // Update card content with word wrap and overflow handling
  const frontElement = document.getElementById("flashcardFront")
  const backElement = document.getElementById("flashcardBack")
  
  frontElement.textContent = card.front
  backElement.textContent = card.back
  
  // Add CSS classes for text wrapping
  frontElement.style.wordWrap = "break-word"
  frontElement.style.overflowWrap = "break-word"
  frontElement.style.whiteSpace = "normal"
  
  backElement.style.wordWrap = "break-word"
  backElement.style.overflowWrap = "break-word"
  backElement.style.whiteSpace = "normal"
  
  document.getElementById("cardCount").textContent = `Card ${currentCardIndex + 1} of ${flashcards.length}`

  const progress = ((currentCardIndex + 1) / flashcards.length) * 100
  document.getElementById("progressPercent").textContent = Math.round(progress) + "%"
  document.getElementById("progressFill").style.width = progress + "%"

  applyCardDisplaySetting()

  const prevBtn = document.getElementById("prevBtn")
  const nextBtn = document.getElementById("nextBtn")
  const knowBtn = document.getElementById("knowBtn")
  
  // Update standard navigation buttons
  if (prevBtn && nextBtn) {
    prevBtn.disabled = currentCardIndex === 0
    
    if (currentCardIndex === flashcards.length - 1) {
      nextBtn.innerHTML = 'Finish <i class="bi bi-flag-fill"></i>'
      nextBtn.classList.remove('btn-outline-primary')
      nextBtn.classList.add('btn-success')
    } else {
      nextBtn.innerHTML = 'Next <i class="bi bi-chevron-right"></i>'
      nextBtn.classList.remove('btn-success')
      nextBtn.classList.add('btn-outline-primary')
    }
  }
  
  // Update track progress "Know" button to "Submit" on last card
  if (knowBtn && trackProgress) {
    if (currentCardIndex === flashcards.length - 1) {
      knowBtn.innerHTML = '<i class="bi bi-check-circle"></i> Submit'
      knowBtn.classList.remove('btn-outline-success')
      knowBtn.classList.add('btn-success')
    } else {
      knowBtn.innerHTML = '<i class="bi bi-check-circle"></i> Know'
      knowBtn.classList.remove('btn-success')
      knowBtn.classList.add('btn-outline-success')
    }
  }
}


function retryDeck() {
  const modal = bootstrap.Modal.getInstance(document.getElementById("resultsModal"))
  modal.hide()
  resetDeck()
}

function retryCompletion() {
  const modal = bootstrap.Modal.getInstance(document.getElementById("completionModal"))
  modal.hide()
  resetDeck()
}

function resetDeck() {
  currentCardIndex = 0
  cardProgress = {}
  flashcards.forEach((card) => {
    cardProgress[card.flashcard_id] = null
  })

  // Re-shuffle if randomized
  if (studyMode === "randomized") {
    flashcards = shuffleArray([...currentDeck.flashcards])
  }

  sessionId = "session_" + Date.now() + "_" + Math.random().toString(36).substr(2, 9)
  
  // Clear old progress and save new session
  clearProgressFromStorage(currentDeck.deck_id)
  saveProgressToStorage(currentDeck.deck_id)
  
  updateUI()
}

function reviewUnknownOnly() {
  const modal = bootstrap.Modal.getInstance(document.getElementById("resultsModal"))
  modal.hide()
  flashcards = flashcards.filter((card) => cardProgress[card.flashcard_id] === "unknown")

  if (flashcards.length === 0) {
    alert("No unknown cards to review!")
    window.location.href = "index.php"
    return
  }

  currentCardIndex = 0
  cardProgress = {}
  flashcards.forEach((card) => {
    cardProgress[card.flashcard_id] = null
  })

  sessionId = "session_" + Date.now() + "_" + Math.random().toString(36).substr(2, 9)
  
  // Clear old progress and save new session
  clearProgressFromStorage(currentDeck.deck_id)
  saveProgressToStorage(currentDeck.deck_id)
  
  updateUI()
}
function showExitWarning() {
  exitWarningModal.show();
}

function confirmExit() {
  // Clear progress from storage since user is choosing to exit
  clearProgressFromStorage(currentDeck.deck_id);
  exitWarningModal.hide();
  window.location.href = "index.php";
}

function exitDeck() {
  showExitWarning();
}

function closeDeck() {
  window.location.href = "index.php"
}