document.addEventListener("DOMContentLoaded", () => {
  const quizTable = document.getElementById("quizTable")
  const statusMessage = document.getElementById("statusMessage")
  const searchQuiz = document.getElementById("searchQuiz")
  const clearSearch = document.getElementById("clearSearch")
  const paginationControls = document.getElementById("paginationControls")
  const paginationInfo = document.getElementById("paginationInfo")
  const prevPage = document.getElementById("prevPage")
  const nextPage = document.getElementById("nextPage")
  const selectAllCheckbox = document.getElementById("selectAllCheckbox")
  const deleteSelectedBtn = document.getElementById("deleteSelectedBtn")

  // Modals
  const deleteModal = document.getElementById("deleteModal")
  const resultsHistoryModal = document.getElementById("resultsHistoryModal")
  
  const confirmDeleteBtn = document.getElementById("confirmDeleteBtn")
  const resultsQuizTitle = document.getElementById("resultsQuizTitle")
  const resultsTable = document.getElementById("resultsTable")
  
  // Modal elements
  const deleteModalTitle = document.getElementById("deleteModalTitle")
  const deleteModalBody = document.getElementById("deleteModalBody")

  // Pagination variables
  const itemsPerPage = 10
  let currentPage = 1
  let totalPages = 1

  // Quiz data
  let allQuizzes = []
  let filteredQuizzes = []
  let quizToDelete = null
  let quizzesToDelete = []

  // Load quizzes
  loadQuizzes()

  // Event listeners
  searchQuiz.addEventListener("input", () => {
    filterQuizzes()
    clearSearch.classList.toggle("visible", searchQuiz.value !== "")
  })
  
  clearSearch.addEventListener("click", clearSearchField)
  prevPage.parentElement.addEventListener("click", goToPrevPage)
  nextPage.parentElement.addEventListener("click", goToNextPage)
  selectAllCheckbox.addEventListener("change", toggleSelectAll)
  deleteSelectedBtn.addEventListener("click", deleteSelectedQuizzes)

  // Modal close buttons
  document.querySelectorAll('[data-dismiss="modal"]').forEach(btn => {
    btn.addEventListener("click", (e) => {
      const modal = e.target.closest(".modal")
      if (modal) closeModal(modal)
    })
  })

  // Close modal on overlay click
  document.querySelectorAll(".modal-overlay").forEach(overlay => {
    overlay.addEventListener("click", (e) => {
      const modal = e.target.closest(".modal")
      if (modal) closeModal(modal)
    })
  })

  // Setup dropdown event delegation
  document.addEventListener("click", (e) => {
    // Handle dropdown toggle clicks
    if (e.target && e.target.classList.contains("dropdown-toggle")) {
      e.preventDefault()
      e.stopPropagation()

      // Close all other dropdowns
      document.querySelectorAll(".dropdown-menu.show").forEach((menu) => {
        if (menu !== e.target.nextElementSibling) {
          menu.classList.remove("show")
        }
      })

      // Toggle this dropdown
      e.target.nextElementSibling.classList.toggle("show")
    }

    // Handle action clicks
    if (e.target && e.target.classList.contains("dropdown-item")) {
      const action = e.target.getAttribute("data-action")
      const quizId = e.target.getAttribute("data-quiz-id")
      const quizTitle = e.target.getAttribute("data-quiz-title")

      if (action === "delete") {
        quizToDelete = quizId
        showDeleteConfirmationModal(1)
      } else if (action === "results") {
        viewQuizResults(quizId, quizTitle)
      }

      // Close the dropdown
      const dropdownMenu = e.target.closest(".dropdown-menu")
      if (dropdownMenu) {
        dropdownMenu.classList.remove("show")
      }
    }

    // Close dropdowns when clicking outside
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

  function loadQuizzes() {
    fetch("api/get_quizzes.php")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          allQuizzes = data.quizzes
          filteredQuizzes = [...allQuizzes]

          loadQuestionCounts(allQuizzes).then(() => {
            updatePagination()
            displayQuizzes()
          })
        } else {
          showStatusMessage("Error loading quizzes: " + data.message, "danger")
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        showStatusMessage("Error loading quizzes. Please try again.", "danger")
      })
  }

  async function loadQuestionCounts(quizzes) {
    for (const quiz of quizzes) {
      try {
        const response = await fetch(`api/get_quiz.php?id=${quiz.quiz_id}`)
        const data = await response.json()
        if (data.success) {
          quiz.questionCount = data.quiz.questions.length
        } else {
          quiz.questionCount = 0
        }
      } catch (error) {
        console.error("Error loading question count:", error)
        quiz.questionCount = 0
      }
    }
  }

  function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('#quizTable input[type="checkbox"]')
    checkboxes.forEach(checkbox => {
      checkbox.checked = selectAllCheckbox.checked
    })
    updateDeleteSelectedBtnState()
  }

  function updateDeleteSelectedBtnState() {
    const checkedBoxes = document.querySelectorAll('#quizTable input[type="checkbox"]:checked')
    deleteSelectedBtn.disabled = checkedBoxes.length === 0
  }

  function deleteSelectedQuizzes() {
    const checkedBoxes = document.querySelectorAll('#quizTable input[type="checkbox"]:checked')
    quizzesToDelete = Array.from(checkedBoxes).map(checkbox => checkbox.value)
    
    if (quizzesToDelete.length === 0) {
      showStatusMessage("No quizzes selected for deletion.", "warning")
      return
    }
    
    showDeleteConfirmationModal(quizzesToDelete.length)
  }

  function showDeleteConfirmationModal(quizCount) {
    if (quizCount === 1) {
      deleteModalTitle.textContent = "Delete Quiz"
      deleteModalBody.textContent = "Are you sure you want to move this quiz to recycling bin? You can restore it later."
    } else {
      deleteModalTitle.textContent = "Delete Multiple Quizzes"
      deleteModalBody.textContent = `Are you sure you want to move ${quizCount} selected quizzes to recycling bin? You can restore them later.`
    }
    
    openModal(deleteModal)
  }

  confirmDeleteBtn.addEventListener("click", () => {
    if (quizToDelete) {
      deleteQuiz(quizToDelete)
      quizToDelete = null
    } else if (quizzesToDelete.length > 0) {
      deleteMultipleQuizzes(quizzesToDelete)
      quizzesToDelete = []
    }
    closeModal(deleteModal)
  })

  function deleteMultipleQuizzes(quizIds) {
    deleteSelectedBtn.disabled = true
    deleteSelectedBtn.innerHTML = '<div class="loading-spinner" style="width: 16px; height: 16px; margin: 0;"></div> Moving to Bin...'
    
    let deletePromises = quizIds.map(quizId => {
      return fetch(`api/delete_quiz.php?id=${quizId}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            allQuizzes = allQuizzes.filter((quiz) => quiz.quiz_id != quizId)
            filteredQuizzes = filteredQuizzes.filter((quiz) => quiz.quiz_id != quizId)
            return { success: true, quizId }
          } else {
            return { success: false, quizId, message: data.message }
          }
        })
        .catch(error => {
          console.error("Error moving quiz to bin:", quizId, error)
          return { success: false, quizId, message: "Network error" }
        })
    })
    
    Promise.all(deletePromises).then(results => {
      const successfulDeletes = results.filter(r => r.success).length
      const failedDeletes = results.filter(r => !r.success)
      
      if (failedDeletes.length === 0) {
        showStatusMessage(`${successfulDeletes} quiz(es) moved to recycling bin successfully.`, "success")
      } else if (successfulDeletes === 0) {
        showStatusMessage(`Failed to move ${failedDeletes.length} quiz(es) to recycling bin.`, "danger")
      } else {
        showStatusMessage(
          `${successfulDeletes} quiz(es) moved to recycling bin successfully, ${failedDeletes.length} failed.`, 
          "warning"
        )
      }
      
      updatePagination()
      displayQuizzes()
      selectAllCheckbox.checked = false
      
      deleteSelectedBtn.disabled = true
      deleteSelectedBtn.innerHTML = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> Delete Selected'
    })
  }

  function filterQuizzes() {
    const searchTerm = searchQuiz.value.toLowerCase().trim()

    if (searchTerm === "") {
      filteredQuizzes = [...allQuizzes]
    } else {
      filteredQuizzes = allQuizzes.filter(
        (quiz) => quiz.title.toLowerCase().includes(searchTerm) || quiz.description.toLowerCase().includes(searchTerm)
      )
    }

    currentPage = 1
    updatePagination()
    displayQuizzes()
  }

  function clearSearchField() {
    searchQuiz.value = ""
    clearSearch.classList.remove("visible")
    filterQuizzes()
  }

  function updatePagination() {
    totalPages = Math.max(1, Math.ceil(filteredQuizzes.length / itemsPerPage))

    if (currentPage > totalPages) {
      currentPage = totalPages
    }

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
        displayQuizzes()
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
    const end = Math.min(start + itemsPerPage - 1, filteredQuizzes.length)
    paginationInfo.textContent = `Showing ${filteredQuizzes.length > 0 ? start : 0}-${end} of ${filteredQuizzes.length} quizzes`
  }

  function goToPrevPage(e) {
    e.preventDefault()
    if (currentPage > 1) {
      currentPage--
      displayQuizzes()
      updatePagination()
    }
  }

  function goToNextPage(e) {
    e.preventDefault()
    if (currentPage < totalPages) {
      currentPage++
      displayQuizzes()
      updatePagination()
    }
  }

  function displayQuizzes() {
    if (filteredQuizzes.length === 0) {
      quizTable.innerHTML = '<tr><td colspan="7" class="text-center">No quizzes found. <a href="index.html">Create a new quiz</a>.</td></tr>'
      return
    }

    quizTable.innerHTML = ""

    const startIndex = (currentPage - 1) * itemsPerPage
    const endIndex = Math.min(startIndex + itemsPerPage, filteredQuizzes.length)
    const pageQuizzes = filteredQuizzes.slice(startIndex, endIndex)

    pageQuizzes.forEach((quiz) => {
      const createdDate = new Date(quiz.created_at).toLocaleString()
      const updatedDate = new Date(quiz.updated_at).toLocaleString()

      const row = document.createElement("tr")
      row.innerHTML = `
        <td class="text-center">
          <input type="checkbox" class="quiz-checkbox" value="${quiz.quiz_id}">
        </td>
        <td>${quiz.title}</td>
        <td>${quiz.description.substring(0, 50)}${quiz.description.length > 50 ? "..." : ""}</td>
        <td>${createdDate}</td>
        <td>${updatedDate}</td>
        <td class="text-center">${quiz.questionCount || 0}</td>
        <td class="text-center">
          <div class="dropdown">
            <button class="dropdown-toggle" type="button">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="1"></circle>
                <circle cx="12" cy="5" r="1"></circle>
                <circle cx="12" cy="19" r="1"></circle>
              </svg>
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="quiz.php?id=${quiz.quiz_id}">Take Quiz</a></li>
              <li><a class="dropdown-item" href="edit.php?id=${quiz.quiz_id}">Edit Quiz</a></li>
              <li><a class="dropdown-item" href="#" data-action="results" data-quiz-id="${quiz.quiz_id}" data-quiz-title="${quiz.title}">View Results</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="#" data-action="delete" data-quiz-id="${quiz.quiz_id}">Delete Quiz</a></li>
            </ul>
          </div>
        </td>
      `

      quizTable.appendChild(row)
    })

    document.querySelectorAll('.quiz-checkbox').forEach(checkbox => {
      checkbox.addEventListener('change', updateDeleteSelectedBtnState)
    })
  }

  function deleteQuiz(quizId) {
    fetch(`api/delete_quiz.php?id=${quizId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showStatusMessage("Quiz moved to recycling bin.", "success")
          allQuizzes = allQuizzes.filter((quiz) => quiz.quiz_id != quizId)
          filteredQuizzes = filteredQuizzes.filter((quiz) => quiz.quiz_id != quizId)
          updatePagination()
          displayQuizzes()
        } else {
          showStatusMessage("Error moving quiz to recycling bin: " + data.message, "danger")
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        showStatusMessage("Error moving quiz to recycling bin. Please try again.", "danger")
      })
  }

  function viewQuizResults(quizId, quizTitle) {
    resultsQuizTitle.textContent = quizTitle

    fetch(`api/get_quiz_results.php?id=${quizId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          displayQuizResults(data.results)
        } else {
          resultsTable.innerHTML = '<tr><td colspan="3" class="text-center">Error loading results.</td></tr>'
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        resultsTable.innerHTML = '<tr><td colspan="3" class="text-center">Error loading results.</td></tr>'
      })

    openModal(resultsHistoryModal)
  }

  function displayQuizResults(results) {
    if (results.length === 0) {
      resultsTable.innerHTML = '<tr><td colspan="3" class="text-center">No results found for this quiz.</td></tr>'
      return
    }

    resultsTable.innerHTML = ""

    results.forEach((result) => {
      const date = new Date(result.completed_at).toLocaleString()
      const percentage = Math.round((result.score / result.total_questions) * 100)
      const badgeClass = percentage >= 70 ? "success" : "warning"

      const row = document.createElement("tr")
      row.innerHTML = `
        <td>${date}</td>
        <td>${result.score}/${result.total_questions}</td>
        <td><span class="percentage-badge ${badgeClass}">${percentage}%</span></td>
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
