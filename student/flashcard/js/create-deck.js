let exitWarningModal
let exitDestination = ""
let isDeckSubmitted = false
let hasUnsavedChanges = false
let draggedCard = null
let deckSettingsModal
let deckSettings = {
  studyMode: "sequence",
  trackProgress: true,
  showBackOnLoad: false
}

let deckData = {
  title: "",
  description: "",
  flashcards: [],
  settings: deckSettings,
}

document.addEventListener("DOMContentLoaded", () => {
  const saveDeckBtn = document.getElementById("saveDeckBtn")
  const startDeckBtn = document.getElementById("startDeckBtn")
  const addCardBtn = document.getElementById("addCardBtn")
  const flashcardContainer = document.getElementById("flashcardContainer")
  const importCardsBtn = document.getElementById("importCardsBtn")
  const deckSettingsBtn = document.getElementById("deckSettingsBtn")
  const saveDeckSettingsBtn = document.getElementById("saveDeckSettingsBtn")
  const successAlert = document.getElementById("successAlert")
  const errorAlert = document.getElementById("errorAlert")

  deckData.user_current_id = document.getElementById("user-current-id").value

  const urlParams = new URLSearchParams(window.location.search)
  const deckId = urlParams.get("id")
  const importFlag = urlParams.get("import")

  if (deckId) {
    loadDeck(deckId)
  } else if (importFlag === "true") {
    processImportedCards()
  } else {
    for (let i = 0; i < 5; i++) {
      addNewCard()
    }
  }

  initExitWarningModal()
  setupNavigationInterception()
  setupChangeTracking()

  saveDeckBtn.addEventListener("click", () => saveDeck(false))
  startDeckBtn.addEventListener("click", () => saveDeck(true))
  addCardBtn.addEventListener("click", addNewCard)
  importCardsBtn.addEventListener("click", () => {
    window.location.href = "import-flashcards.php"
  })
  deckSettingsBtn.addEventListener("click", openDeckSettings)
  saveDeckSettingsBtn.addEventListener("click", saveDeckSettings)

  function addNewCard() {
    const cardCount = document.querySelectorAll(".flashcard-card").length
    const newCardId = cardCount + 1

    const newCard = document.createElement("div")
    newCard.className = "card flashcard-card mb-3"
    newCard.setAttribute("data-card-id", newCardId)
    newCard.setAttribute("draggable", "true")

    newCard.innerHTML = `
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-number">${newCardId}</span>
                <div class="d-flex align-items-center">
                    <button class="btn btn-sm btn-light move-card-btn" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></button>
                    <button class="btn btn-sm btn-light delete-card-btn"><i class="bi bi-trash"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="form-label">Front</label>
                        <textarea class="form-control front-input auto-resize" placeholder="Front of card" rows="1"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Back</label>
                        <textarea class="form-control back-input auto-resize" placeholder="Back of card" rows="1"></textarea>
                    </div>
                </div>
            </div>
        `

    flashcardContainer.appendChild(newCard)
    addCardEventListeners(newCard)
    initCardDragAndDrop(newCard)
    initAutoResizeForCard(newCard)
    updateCardNumbers()
    hasUnsavedChanges = true
  }

  function addCardEventListeners(card) {
    card.querySelector(".delete-card-btn").addEventListener("click", () => {
      card.remove()
      updateCardNumbers()
      hasUnsavedChanges = true
    })

    const inputs = card.querySelectorAll("textarea")
    inputs.forEach((input) => {
      input.addEventListener("input", () => {
        hasUnsavedChanges = true
      })
    })
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
      hasUnsavedChanges = true
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
        const allCards = Array.from(document.querySelectorAll(".flashcard-card"))
        const draggedIndex = allCards.indexOf(draggedCard)
        const targetIndex = allCards.indexOf(this)

        if (draggedIndex < targetIndex) {
          this.parentNode.insertBefore(draggedCard, this.nextSibling)
        } else {
          this.parentNode.insertBefore(draggedCard, this)
        }

        document.querySelectorAll(".flashcard-card").forEach((card) => {
          initAutoResizeForCard(card)
        })
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
    const cards = document.querySelectorAll(".flashcard-card")
    cards.forEach((card, index) => {
      card.setAttribute("data-card-id", index + 1)
      card.querySelector(".card-number").textContent = index + 1
    })
  }

  function autoResizeTextarea(textarea) {
    textarea.style.height = "auto"
    textarea.style.height = textarea.scrollHeight + "px"
  }

  function initAutoResizeForCard(card) {
    const textareas = card.querySelectorAll("textarea.auto-resize")
    textareas.forEach((textarea) => {
      autoResizeTextarea(textarea)
      textarea.addEventListener("input", () => autoResizeTextarea(textarea))
      textarea.addEventListener("focus", () => autoResizeTextarea(textarea))
    })
  }

  function processImportedCards() {
    const importedCardsJson = localStorage.getItem("importedCards")
    if (importedCardsJson) {
      try {
        const importedCards = JSON.parse(importedCardsJson)

        if (importedCards.length > 0) {
          flashcardContainer.innerHTML = ""
        }

        importedCards.forEach((card, index) => {
          const newCard = document.createElement("div")
          newCard.className = "card flashcard-card mb-3"
          newCard.setAttribute("data-card-id", index + 1)
          newCard.setAttribute("draggable", "true")

          newCard.innerHTML = `
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="card-number">${index + 1}</span>
                            <div class="d-flex align-items-center">
                                <button class="btn btn-sm btn-light move-card-btn" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></button>
                                <button class="btn btn-sm btn-light delete-card-btn"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="form-label">Front</label>
                                    <textarea class="form-control front-input auto-resize" placeholder="Front of card" rows="1">${card.term}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Back</label>
                                    <textarea class="form-control back-input auto-resize" placeholder="Back of card" rows="1">${card.description}</textarea>
                                </div>
                            </div>
                        </div>
                    `

          flashcardContainer.appendChild(newCard)
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

  function openDeckSettings() {
    document.getElementById("sequenceMode").checked = deckSettings.studyMode === "sequence"
    document.getElementById("randomizedMode").checked = deckSettings.studyMode === "randomized"
    document.getElementById("trackProgressSwitch").checked = deckSettings.trackProgress
    document.getElementById("showBackOnLoadSwitch").checked = deckSettings.showBackOnLoad

    const deckSettingsModalElement = document.getElementById("deckSettingsModal");
    deckSettingsModal = new bootstrap.Modal(deckSettingsModalElement);
    deckSettingsModal.show();
  }

  function saveDeckSettings() {
    deckSettings.studyMode = document.querySelector("input[name='studyMode']:checked").value
    deckSettings.trackProgress = document.getElementById("trackProgressSwitch").checked
    deckSettings.showBackOnLoad = document.getElementById("showBackOnLoadSwitch").checked

    deckData.settings = deckSettings
    deckSettingsModal.hide();
    showSuccess("Deck settings updated successfully!")
  }

  function saveDeck(shouldStart = false) {
  deckData.title = document.getElementById("deckTitle").value || "Untitled Deck"
  deckData.description = document.getElementById("deckDescription").value || "No description"
  deckData.user_current_id = document.getElementById("user-current-id").value
  deckData.settings = deckSettings // This line ensures settings are included

  deckData.flashcards = []
  document.querySelectorAll(".flashcard-card").forEach((card) => {
    const front = card.querySelector(".front-input").value
    const back = card.querySelector(".back-input").value

    if (front || back) {
      deckData.flashcards.push({
        front: front || "No front provided",
        back: back || "No back provided",
      })
    }
  })

  if (deckData.flashcards.length < 4) {
    showError("Please add at least 4 flashcards to your deck.")
    return
  }

  fetch("api/save_deck.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(deckData),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        hasUnsavedChanges = false
        isDeckSubmitted = true

        if (shouldStart) {
          window.location.href = "take-deck.php?id=" + data.deck_id
        } else {
          showSuccess("Deck saved successfully!")
          setTimeout(() => {
            window.location.href = "index.php"
          }, 1500)
        }
      } else {
        showError("Error saving deck: " + data.message)
      }
    })
}

  function loadDeck(deckId) {
    fetch(`api/get_deck.php?id=${deckId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          populateDeckForm(data.deck)
        } else {
          showError("Error loading deck: " + data.message)
        }
      })
  }

  function populateDeckForm(deck) {
    document.getElementById("deckTitle").value = deck.title
    document.getElementById("deckDescription").value = deck.description
    flashcardContainer.innerHTML = ""

    if (deck.settings) {
      deckSettings = deck.settings
    }

    deck.flashcards.forEach((card, index) => {
      const newCard = document.createElement("div")
      newCard.className = "card flashcard-card mb-3"
      newCard.setAttribute("data-card-id", index + 1)
      newCard.setAttribute("draggable", "true")

      newCard.innerHTML = `
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="card-number">${index + 1}</span>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-sm btn-light move-card-btn" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></button>
                        <button class="btn btn-sm btn-light delete-card-btn"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label">Front</label>
                            <textarea class="form-control front-input auto-resize" placeholder="Front of card" rows="1">${card.front}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Back</label>
                            <textarea class="form-control back-input auto-resize" placeholder="Back of card" rows="1">${card.back}</textarea>
                        </div>
                    </div>
                </div>
            `

      flashcardContainer.appendChild(newCard)
      addCardEventListeners(newCard)
      initCardDragAndDrop(newCard)
      initAutoResizeForCard(newCard)
    })

    deckData = deck
    hasUnsavedChanges = false
  }

  function showSuccess(message) {
    if (successAlert) {
      successAlert.textContent = message
      successAlert.classList.remove("d-none")
      if (errorAlert) errorAlert.classList.add("d-none")
      setTimeout(() => {
        successAlert.classList.add("d-none")
      }, 3000)
    }
  }

  function showError(message) {
    if (errorAlert) {
      errorAlert.textContent = message
      errorAlert.classList.remove("d-none")
      if (successAlert) successAlert.classList.add("d-none")
      setTimeout(() => {
        errorAlert.classList.add("d-none")
      }, 5000)
    }
  }
})

function hasDeckChanges() {
  const title = document.getElementById("deckTitle").value
  const description = document.getElementById("deckDescription").value

  if (title || description) {
    return true
  }

  const cards = document.querySelectorAll(".flashcard-card")
  for (const card of cards) {
    const front = card.querySelector(".front-input").value
    const back = card.querySelector(".back-input").value

    if (front || back) {
      return true
    }
  }

  return false
}

function setupNavigationInterception() {
  document.body.addEventListener("click", (e) => {
    if (isDeckSubmitted) return

    const link = e.target.closest("a")
    if (!link || link.id === "confirmExitBtn") return

    const href = link.getAttribute("href")
    if (!href || href === "#" || href.startsWith("javascript:")) return

    if (hasDeckChanges()) {
      e.preventDefault()
      exitDestination = href
      exitWarningModal.show()
    }
  })

  window.addEventListener("beforeunload", (e) => {
    if (!isDeckSubmitted && hasDeckChanges()) {
      e.preventDefault()
      e.returnValue = ""
      return ""
    }
  })
}

function setupChangeTracking() {
  const formInputs = document.querySelectorAll("#deckTitle, #deckDescription, .front-input, .back-input")

  formInputs.forEach((input) => {
    input.addEventListener("input", () => {
      hasUnsavedChanges = true
    })
  })

  const observer = new MutationObserver(() => {
    hasUnsavedChanges = true
  })

  observer.observe(document.getElementById("flashcardContainer"), {
    childList: true,
    subtree: true,
  })
}

function initExitWarningModal() {
  const exitWarningModalElement = document.getElementById('exitWarningModal');
  
  exitWarningModal = new bootstrap.Modal(exitWarningModalElement, {
    backdrop: 'static',
    keyboard: false
  });

  document.getElementById("confirmExitBtn").addEventListener("click", (e) => {
    e.preventDefault()
    isDeckSubmitted = true
    window.location.href = exitDestination
  })
}