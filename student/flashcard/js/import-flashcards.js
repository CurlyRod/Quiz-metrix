document.addEventListener("DOMContentLoaded", () => {
  const importDataTextarea = document.getElementById("importData")
  const importBtn = document.getElementById("importBtn")
  const cancelBtn = document.getElementById("cancelBtn")
  const errorAlert = document.getElementById("errorAlert")
  const successAlert = document.getElementById("successAlert")

  const termSeparatorRadios = document.getElementsByName("termSeparator")
  const cardSeparatorRadios = document.getElementsByName("cardSeparator")

  importBtn.addEventListener("click", processImport)
  cancelBtn.addEventListener("click", () => {
    window.location.href = "create-deck.php"
  })

  function processImport() {
    hideAlerts()

    const inputText = importDataTextarea.value.trim()

    if (!inputText) {
      showError("Please enter some data to import.")
      return
    }

    const termSeparator = getSelectedRadioValue(termSeparatorRadios)
    const cardSeparator = getSelectedRadioValue(cardSeparatorRadios)

    const cleanedInput = cleanText(inputText)

    let cards = cleanedInput.split(cardSeparator)
    cards = cards.filter((card) => card.trim() !== "")

    if (cards.length < 4) {
      showError("Please provide at least 4 valid cards to import.")
      return
    }

    if (cards.length > 100) {
      showError("You can import a maximum of 100 cards at once.")
      return
    }

    const processedCards = []
    const invalidLines = []

    cards.forEach((card, index) => {
      const trimmedCard = card.trim()

      if (trimmedCard === "") {
        return
      }

      let parts
      if (termSeparator === "\t") {
        parts = trimmedCard.split(/\t+/)
      } else {
        const separatorIndex = trimmedCard.indexOf(termSeparator)
        if (separatorIndex !== -1) {
          parts = [trimmedCard.substring(0, separatorIndex).trim(), trimmedCard.substring(separatorIndex + 1).trim()]
        } else {
          parts = [trimmedCard]
        }
      }

      if (parts.length !== 2 || !parts[0].trim() || !parts[1].trim()) {
        invalidLines.push(`Line ${index + 1}: "${trimmedCard}"`)
        return
      }

      processedCards.push({
        term: parts[0].trim(),
        description: parts[1].trim(),
      })
    })

    if (processedCards.length === 0) {
      showError("No valid cards found. Please check your input format.")
      return
    }

    if (invalidLines.length > 0) {
      const warningMessage = `${invalidLines.length} invalid line(s) were skipped:<br>${invalidLines.slice(0, 5).join("<br>")}${invalidLines.length > 5 ? "<br>..." : ""}`
      showError(warningMessage)
    }

    localStorage.setItem("importedCards", JSON.stringify(processedCards))

    showSuccess(`Successfully processed ${processedCards.length} cards. Redirecting...`)

    setTimeout(() => {
      window.location.href = "create-deck.php?import=true"
    }, 1500)
  }

  function cleanText(text) {
    let cleaned = text.replace(/\r\n/g, " ").replace(/\n/g, " ").replace(/\r/g, " ")
    cleaned = cleaned.replace(/\s+/g, " ")
    return cleaned.trim()
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
