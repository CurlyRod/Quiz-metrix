document.addEventListener("DOMContentLoaded", () => {
  // DOM Elements
  const importDataTextarea = document.getElementById("importData")
  const importBtn = document.getElementById("importBtn")
  const cancelBtn = document.getElementById("cancelBtn")
  const errorAlert = document.getElementById("errorAlert")
  const successAlert = document.getElementById("successAlert")

  // Get separator radio buttons
  const termSeparatorRadios = document.getElementsByName("termSeparator")
  const cardSeparatorRadios = document.getElementsByName("cardSeparator")

  // Event listeners
  importBtn.addEventListener("click", processImport)
  cancelBtn.addEventListener("click", () => {
    window.location.href = "create-quiz.php"
  })

  function processImport() {
    // Clear previous alerts
    hideAlerts()

    // Get the input text
    const inputText = importDataTextarea.value.trim()

    // Validate input
    if (!inputText) {
      showError("Please enter some data to import.")
      return
    }

    // Get selected separators
    const termSeparator = getSelectedRadioValue(termSeparatorRadios)
    const cardSeparator = getSelectedRadioValue(cardSeparatorRadios)

    // NEW: Clean the input text by removing all \n and extra whitespace
    const cleanedInput = cleanText(inputText)

    // Process the input based on selected separators
    let cards = cleanedInput.split(cardSeparator)

    // Filter out empty lines
    cards = cards.filter((card) => card.trim() !== "")

    // Validate number of cards
    if (cards.length < 4) {
      showError("Please provide at least 4 valid cards to import.")
      return
    }

    if (cards.length > 100) {
      showError("You can import a maximum of 100 cards at once.")
      return
    }

    // Process each card
    const processedCards = []
    const invalidLines = []

    cards.forEach((card, index) => {
      const trimmedCard = card.trim()

      // Skip empty cards
      if (trimmedCard === "") {
        return
      }

      // Split the card into term and description
      let parts
      if (termSeparator === "\t") {
        parts = trimmedCard.split(/\t+/)
      } else {
        // For dash or semicolon, we need to handle the case where the separator might appear in the content
        // We'll split by the first occurrence only
        const separatorIndex = trimmedCard.indexOf(termSeparator)
        if (separatorIndex !== -1) {
          parts = [trimmedCard.substring(0, separatorIndex).trim(), trimmedCard.substring(separatorIndex + 1).trim()]
        } else {
          parts = [trimmedCard]
        }
      }

      // Validate parts
      if (parts.length !== 2 || !parts[0].trim() || !parts[1].trim()) {
        invalidLines.push(`Line ${index + 1}: "${trimmedCard}"`)
        return
      }

      // Add valid card
      processedCards.push({
        term: parts[0].trim(),
        description: parts[1].trim(),
      })
    })

    // Check if we have any valid cards
    if (processedCards.length === 0) {
      showError("No valid cards found. Please check your input format.")
      return
    }

    // Show warning for invalid lines
    if (invalidLines.length > 0) {
      const warningMessage = `${invalidLines.length} invalid line(s) were skipped:<br>${invalidLines.slice(0, 5).join("<br>")}${invalidLines.length > 5 ? "<br>..." : ""}`
      showError(warningMessage)
    }

    // Store the processed cards in localStorage to be used in index.html
    localStorage.setItem("importedCards", JSON.stringify(processedCards))

    // Show success message
    showSuccess(`Successfully processed ${processedCards.length} cards. Redirecting...`)

    // Redirect back to index.html after a short delay
    setTimeout(() => {
      window.location.href = "create-quiz.php?import=true"
    }, 1500)
  }

  // NEW FUNCTION: Clean text by removing all \n and extra whitespace
  function cleanText(text) {
    // Replace all newline characters with spaces
    let cleaned = text.replace(/\r\n/g, ' ').replace(/\n/g, ' ').replace(/\r/g, ' ');
    
    // Replace multiple spaces with single space
    cleaned = cleaned.replace(/\s+/g, ' ');
    
    // Trim the result
    return cleaned.trim();
  }

  function getSelectedRadioValue(radioButtons) {
    for (const radioButton of radioButtons) {
      if (radioButton.checked) {
        return radioButton.value
      }
    }
    return null
  }

  function showError(message) {
    errorAlert.innerHTML = message
    errorAlert.classList.remove("d-none")
  }

  function showSuccess(message) {
    successAlert.innerHTML = message
    successAlert.classList.remove("d-none")
  }

  function hideAlerts() {
    errorAlert.classList.add("d-none")
    successAlert.classList.add("d-none")
  }
})