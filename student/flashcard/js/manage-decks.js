document.addEventListener("DOMContentLoaded", () => {
  const deckTable = document.getElementById("deckTable")
  const statusMessage = document.getElementById("statusMessage")
  const searchDeck = document.getElementById("searchDeck")
  const clearSearch = document.getElementById("clearSearch")
  const deleteModal = document.getElementById("deleteModal")
  const resultsHistoryModal = document.getElementById("resultsHistoryModal")
  const confirmDeleteBtn = document.getElementById("confirmDeleteBtn")
  const deleteModalTitle = document.getElementById("deleteModalTitle")
  const deleteModalBody = document.getElementById("deleteModalBody")
  const selectAllCheckbox = document.getElementById("selectAllCheckbox")
  const deleteSelectedBtn = document.getElementById("deleteSelectedBtn")

  const itemsPerPage = 10
  let currentPage = 1
  let totalPages = 1

  let allDecks = []
  let filteredDecks = []
  let deckToDelete = null
  let decksToDelete = []

  loadDecks()

  searchDeck.addEventListener("input", () => {
    filterDecks()
    clearSearch.classList.toggle("visible", searchDeck.value !== "")
  })

  clearSearch.addEventListener("click", clearSearchField)
  selectAllCheckbox.addEventListener("change", toggleSelectAll)
  deleteSelectedBtn.addEventListener("click", deleteSelectedDecks)

  document.querySelectorAll('[data-dismiss="modal"]').forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const modal = e.target.closest(".modal")
      if (modal) closeModal(modal)
    })
  })

  document.querySelectorAll(".modal-overlay").forEach((overlay) => {
    overlay.addEventListener("click", (e) => {
      const modal = e.target.closest(".modal")
      if (modal) closeModal(modal)
    })
  })

  document.addEventListener("click", (e) => {
    if (e.target && e.target.classList.contains("dropdown-toggle")) {
      e.preventDefault()
      e.stopPropagation()

      document.querySelectorAll(".dropdown-menu.show").forEach((menu) => {
        if (menu !== e.target.nextElementSibling) {
          menu.classList.remove("show")
        }
      })

      e.target.nextElementSibling.classList.toggle("show")
    }

    if (e.target && e.target.classList.contains("dropdown-item")) {
      const action = e.target.getAttribute("data-action")
      const deckId = e.target.getAttribute("data-deck-id")
      const deckTitle = e.target.getAttribute("data-deck-title")

      if (action === "delete") {
        deckToDelete = deckId
        showDeleteConfirmationModal(1)
      } else if (action === "results") {
        viewDeckResults(deckId, deckTitle)
      }

      const dropdownMenu = e.target.closest(".dropdown-menu")
      if (dropdownMenu) {
        dropdownMenu.classList.remove("show")
      }
    }

    if (!e.target.closest(".dropdown")) {
      document.querySelectorAll(".dropdown-menu.show").forEach((menu) => {
        menu.classList.remove("show")
      })
    }
  })

  function openModal(modal) {
    modal.classList.add("show")
    document.body.style.overflow = "hidden"
  }

  function closeModal(modal) {
    modal.classList.remove("show")
    document.body.style.overflow = ""
  }

  function loadDecks() {
    fetch("api/get_decks.php")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          allDecks = data.decks
          filteredDecks = [...allDecks]
          updatePagination()
          displayDecks()
        } else {
          showStatusMessage("Error loading decks: " + data.message, "danger")
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        showStatusMessage("Error loading decks. Please try again.", "danger")
      })
  }

  function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('#deckTable input[type="checkbox"]')
    checkboxes.forEach((checkbox) => {
      checkbox.checked = selectAllCheckbox.checked
    })
    updateDeleteSelectedBtnState()
  }

  function updateDeleteSelectedBtnState() {
    const checkedBoxes = document.querySelectorAll('#deckTable input[type="checkbox"]:checked')
    deleteSelectedBtn.disabled = checkedBoxes.length === 0
  }

  function deleteSelectedDecks() {
    const checkedBoxes = document.querySelectorAll('#deckTable input[type="checkbox"]:checked')
    decksToDelete = Array.from(checkedBoxes).map((checkbox) => checkbox.value)

    if (decksToDelete.length === 0) {
      showStatusMessage("No decks selected for deletion.", "warning")
      return
    }

    showDeleteConfirmationModal(decksToDelete.length)
  }

  function showDeleteConfirmationModal(deckCount) {
    if (deckCount === 1) {
      deleteModalTitle.textContent = "Delete Deck"
      deleteModalBody.textContent = "Are you sure you want to delete this deck?"
    } else {
      deleteModalTitle.textContent = "Delete Multiple Decks"
      deleteModalBody.textContent = `Are you sure you want to delete ${deckCount} selected decks?`
    }

    openModal(deleteModal)
  }

  confirmDeleteBtn.addEventListener("click", () => {
    if (deckToDelete) {
      deleteDeck(deckToDelete)
      deckToDelete = null
    } else if (decksToDelete.length > 0) {
      deleteMultipleDecks(decksToDelete)
      decksToDelete = []
    }
    closeModal(deleteModal)
  })

  function deleteMultipleDecks(deckIds) {
    deleteSelectedBtn.disabled = true
    deleteSelectedBtn.innerHTML =
      '<div class="loading-spinner" style="width: 16px; height: 16px; margin: 0;"></div> Deleting...'

    const deletePromises = deckIds.map((deckId) => {
      return fetch(`api/delete_deck.php?id=${deckId}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            allDecks = allDecks.filter((deck) => deck.deck_id != deckId)
            filteredDecks = filteredDecks.filter((deck) => deck.deck_id != deckId)
            return { success: true, deckId }
          } else {
            return { success: false, deckId, message: data.message }
          }
        })
        .catch((error) => {
          console.error("Error deleting deck:", deckId, error)
          return { success: false, deckId, message: "Network error" }
        })
    })

    Promise.all(deletePromises).then((results) => {
      const successfulDeletes = results.filter((r) => r.success).length
      const failedDeletes = results.filter((r) => !r.success)

      if (failedDeletes.length === 0) {
        showStatusMessage(`${successfulDeletes} deck(s) deleted successfully.`, "success")
      } else if (successfulDeletes === 0) {
        showStatusMessage(`Failed to delete ${failedDeletes.length} deck(s).`, "danger")
      } else {
        showStatusMessage(
          `${successfulDeletes} deck(s) deleted successfully, ${failedDeletes.length} failed.`,
          "warning",
        )
      }

      updatePagination()
      displayDecks()
      selectAllCheckbox.checked = false

      deleteSelectedBtn.disabled = true
      deleteSelectedBtn.innerHTML =
        '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> Delete Selected'
    })
  }

  function filterDecks() {
    const searchTerm = searchDeck.value.toLowerCase().trim()

    if (searchTerm === "") {
      filteredDecks = [...allDecks]
    } else {
      filteredDecks = allDecks.filter(
        (deck) => deck.title.toLowerCase().includes(searchTerm) || deck.description.toLowerCase().includes(searchTerm),
      )
    }

    currentPage = 1
    updatePagination()
    displayDecks()
  }

  function clearSearchField() {
    searchDeck.value = ""
    clearSearch.classList.remove("visible")
    filterDecks()
  }

  function updatePagination() {
    totalPages = Math.max(1, Math.ceil(filteredDecks.length / itemsPerPage))

    if (currentPage > totalPages) {
      currentPage = totalPages
    }

    const paginationControls = document.getElementById("paginationControls")
    paginationControls.innerHTML = ""

    const prevLi = document.createElement("li")
    prevLi.className = `page-item ${currentPage === 1 ? "disabled" : ""}`
    prevLi.innerHTML = `
      <a class="page-link" href="#">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
      </a>
    `
    prevLi.addEventListener("click", goToPrevPage)
    paginationControls.appendChild(prevLi)

    const startPage = Math.max(1, currentPage - 2)
    const endPage = Math.min(totalPages, startPage + 4)

    for (let i = startPage; i <= endPage; i++) {
      const pageLi = document.createElement("li")
      pageLi.className = `page-item ${i === currentPage ? "active" : ""}`
      pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`

      pageLi.addEventListener("click", (e) => {
        e.preventDefault()
        currentPage = i
        displayDecks()
        updatePagination()
      })

      paginationControls.appendChild(pageLi)
    }

    const nextLi = document.createElement("li")
    nextLi.className = `page-item ${currentPage === totalPages ? "disabled" : ""}`
    nextLi.innerHTML = `
      <a class="page-link" href="#">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
      </a>
    `
    nextLi.addEventListener("click", goToNextPage)
    paginationControls.appendChild(nextLi)

    const start = (currentPage - 1) * itemsPerPage + 1
    const end = Math.min(start + itemsPerPage - 1, filteredDecks.length)
    document.getElementById("paginationInfo").textContent =
      `Showing ${filteredDecks.length > 0 ? start : 0}-${end} of ${filteredDecks.length} decks`
  }

  function goToPrevPage(e) {
    e.preventDefault()
    if (currentPage > 1) {
      currentPage--
      displayDecks()
      updatePagination()
    }
  }

  function goToNextPage(e) {
    e.preventDefault()
    if (currentPage < totalPages) {
      currentPage++
      displayDecks()
      updatePagination()
    }
  }

  function displayDecks() {
    if (filteredDecks.length === 0) {
      deckTable.innerHTML =
        '<tr><td colspan="7" class="text-center">No decks found. <a href="index.php">Create a new deck</a>.</td></tr>'
      return
    }

    deckTable.innerHTML = ""

    const startIndex = (currentPage - 1) * itemsPerPage
    const endIndex = Math.min(startIndex + itemsPerPage, filteredDecks.length)
    const pageDecks = filteredDecks.slice(startIndex, endIndex)

    pageDecks.forEach((deck) => {
      const createdDate = new Date(deck.created_at).toLocaleString()
      const updatedDate = new Date(deck.updated_at).toLocaleString()

      const row = document.createElement("tr")
      row.innerHTML = `
        <td class="text-center">
          <input type="checkbox" class="deck-checkbox" value="${deck.deck_id}">
        </td>
        <td>${deck.title}</td>
        <td>${deck.description.substring(0, 50)}${deck.description.length > 50 ? "..." : ""}</td>
        <td>${createdDate}</td>
        <td>${updatedDate}</td>
        <td class="text-center">${deck.card_count || 0}</td>
        <td class="text-center">
          <div class="dropdown">
            <button class="dropdown-toggle" type="button">
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="take-deck.php?id=${deck.deck_id}">Study Deck</a></li>
              <li><a class="dropdown-item" href="edit-deck.php?id=${deck.deck_id}">Edit Deck</a></li>
              <li><a class="dropdown-item" href="#" data-action="results" data-deck-id="${deck.deck_id}" data-deck-title="${deck.title}">View Results</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="#" data-action="delete" data-deck-id="${deck.deck_id}">Delete Deck</a></li>
            </ul>
          </div>
        </td>
      `

      deckTable.appendChild(row)
    })

    document.querySelectorAll(".deck-checkbox").forEach((checkbox) => {
      checkbox.addEventListener("change", updateDeleteSelectedBtnState)
    })
  }

  function deleteDeck(deckId) {
    fetch(`api/delete_deck.php?id=${deckId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showStatusMessage("Deck deleted successfully.", "success")
          allDecks = allDecks.filter((deck) => deck.deck_id != deckId)
          filteredDecks = filteredDecks.filter((deck) => deck.deck_id != deckId)
          updatePagination()
          displayDecks()
        } else {
          showStatusMessage("Error deleting deck: " + data.message, "danger")
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        showStatusMessage("Error deleting deck. Please try again.", "danger")
      })
  }

  function viewDeckResults(deckId, deckTitle) {
    document.getElementById("resultsDeckTitle").textContent = deckTitle

    fetch(`api/get_deck_results.php?id=${deckId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          displayDeckResults(data.results)
        } else {
          document.getElementById("resultsTable").innerHTML =
            '<tr><td colspan="4" class="text-center">Error loading results.</td></tr>'
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        document.getElementById("resultsTable").innerHTML =
          '<tr><td colspan="4" class="text-center">Error loading results.</td></tr>'
      })

    openModal(resultsHistoryModal)
  }

  function displayDeckResults(results) {
    const resultsTable = document.getElementById("resultsTable")
    if (results.length === 0) {
      resultsTable.innerHTML = '<tr><td colspan="4" class="text-center">No results found for this deck.</td></tr>'
      return
    }

    resultsTable.innerHTML = ""

    results.forEach((result) => {
      const date = new Date(result.completed_at).toLocaleString()
      const badgeClass = result.percent_known >= 70 ? "success" : "warning"

      const row = document.createElement("tr")
      row.innerHTML = `
        <td>${date}</td>
        <td>${result.mode}</td>
        <td>${result.known_count}/${result.total_cards}</td>
        <td><span class="percentage-badge ${badgeClass}">${result.percent_known}%</span></td>
      `

      resultsTable.appendChild(row)
    })
  }

  function showStatusMessage(message, type) {
    statusMessage.textContent = message
    statusMessage.className = `alert alert-${type}`
    statusMessage.classList.remove("d-none")

    setTimeout(() => {
      statusMessage.classList.add("d-none")
    }, 5000)
  }
})
