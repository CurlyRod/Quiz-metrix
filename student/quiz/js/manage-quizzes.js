document.addEventListener("DOMContentLoaded", () => {
  const quizTable = document.getElementById("quizTable")
  const statusMessage = document.getElementById("statusMessage")
  const searchQuiz = document.getElementById("searchQuiz")
  const clearSearch = document.getElementById("clearSearch")
  const paginationControls = document.getElementById("paginationControls")
  const paginationInfo = document.getElementById("paginationInfo")
  const prevPage = document.getElementById("prevPage")
  const nextPage = document.getElementById("nextPage")

  // Initialize Bootstrap modals
  const deleteModal = new bootstrap.Modal(document.getElementById("deleteModal"))
  const resultsHistoryModal = new bootstrap.Modal(document.getElementById("resultsHistoryModal"))

  const confirmDeleteBtn = document.getElementById("confirmDeleteBtn")
  const resultsQuizTitle = document.getElementById("resultsQuizTitle")
  const resultsTable = document.getElementById("resultsTable")

  // Pagination variables
  const itemsPerPage = 10
  let currentPage = 1
  let totalPages = 1

  // Quiz data
  let allQuizzes = []
  let filteredQuizzes = []
  let quizToDelete = null

  // Load quizzes
  loadQuizzes()

  // Event listeners
  searchQuiz.addEventListener("input", filterQuizzes)
  clearSearch.addEventListener("click", clearSearchField)
  prevPage.parentElement.addEventListener("click", goToPrevPage)
  nextPage.parentElement.addEventListener("click", goToNextPage)

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
        deleteModal.show()
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

  function loadQuizzes() {
    fetch("api/get_quizzes.php")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          allQuizzes = data.quizzes
          filteredQuizzes = [...allQuizzes]

          // Load question counts for all quizzes
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

  // Load question counts for all quizzes
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

  function filterQuizzes() {
    const searchTerm = searchQuiz.value.toLowerCase().trim()

    if (searchTerm === "") {
      filteredQuizzes = [...allQuizzes]
    } else {
      filteredQuizzes = allQuizzes.filter(
        (quiz) => quiz.title.toLowerCase().includes(searchTerm) || quiz.description.toLowerCase().includes(searchTerm),
      )
    }

    // Reset to first page when filtering
    currentPage = 1
    updatePagination()
    displayQuizzes()
  }

  function clearSearchField() {
    searchQuiz.value = ""
    filterQuizzes()
  }

  function updatePagination() {
    totalPages = Math.max(1, Math.ceil(filteredQuizzes.length / itemsPerPage))

    // Ensure current page is valid
    if (currentPage > totalPages) {
      currentPage = totalPages
    }

    // Update pagination controls
    paginationControls.innerHTML = ""

    // Previous button
    const prevLi = document.createElement("li")
    prevLi.className = `page-item ${currentPage === 1 ? "disabled" : ""}`
    prevLi.innerHTML = `
      <a class="page-link" href="#" aria-label="Previous">
        <span aria-hidden="true">&laquo;</span>
      </a>
    `
    prevLi.addEventListener("click", goToPrevPage)
    paginationControls.appendChild(prevLi)

    // Page numbers
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

    // Next button
    const nextLi = document.createElement("li")
    nextLi.className = `page-item ${currentPage === totalPages ? "disabled" : ""}`
    nextLi.innerHTML = `
      <a class="page-link" href="#" aria-label="Next">
        <span aria-hidden="true">&raquo;</span>
      </a>
    `
    nextLi.addEventListener("click", goToNextPage)
    paginationControls.appendChild(nextLi)

    // Update pagination info text
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
      quizTable.innerHTML =
        '<tr><td colspan="6" class="text-center">No quizzes found. <a href="index.html">Create a new quiz</a>.</td></tr>'
      return
    }

    quizTable.innerHTML = ""

    // Calculate start and end indices for current page
    const startIndex = (currentPage - 1) * itemsPerPage
    const endIndex = Math.min(startIndex + itemsPerPage, filteredQuizzes.length)

    // Get quizzes for current page
    const pageQuizzes = filteredQuizzes.slice(startIndex, endIndex)

    pageQuizzes.forEach((quiz) => {
      // Format dates
      const createdDate = new Date(quiz.created_at).toLocaleString()
      const updatedDate = new Date(quiz.updated_at).toLocaleString()

      const row = document.createElement("tr")
      row.innerHTML = `
        <td>${quiz.title}</td>
        <td>${quiz.description.substring(0, 50)}${quiz.description.length > 50 ? "..." : ""}</td>
        <td>${createdDate}</td>
        <td>${updatedDate}</td>
        <td>${quiz.questionCount || 0}</td>
        <td>
          <div class="dropdown">
            <button class="btn btn-sm btn-light dropdown-toggle" type="button" aria-expanded="false">
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="quiz.php?id=${quiz.quiz_id}" data-action="take">Take Quiz</a></li>
              <li><a class="dropdown-item" href="edit.php?id=${quiz.quiz_id}" data-action="edit">Edit Quiz</a></li>
              <li><a class="dropdown-item dropdown-item-results" href="#" data-action="results" data-quiz-id="${quiz.quiz_id}" data-quiz-title="${quiz.title}">View Results</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger dropdown-item-delete" href="#" data-action="delete" data-quiz-id="${quiz.quiz_id}">Delete Quiz</a></li>
            </ul>
          </div>
        </td>
      `

      quizTable.appendChild(row)
    })
  }

  // Confirm delete button
  confirmDeleteBtn.addEventListener("click", () => {
    if (quizToDelete) {
      deleteQuiz(quizToDelete)
      deleteModal.hide()
    }
  })

  function deleteQuiz(quizId) {
    fetch(`api/delete_quiz.php?id=${quizId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showStatusMessage("Quiz deleted successfully.", "success")

          // Remove quiz from arrays
          allQuizzes = allQuizzes.filter((quiz) => quiz.quiz_id != quizId)
          filteredQuizzes = filteredQuizzes.filter((quiz) => quiz.quiz_id != quizId)

          // Update pagination and display
          updatePagination()
          displayQuizzes()
        } else {
          showStatusMessage("Error deleting quiz: " + data.message, "danger")
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        showStatusMessage("Error deleting quiz. Please try again.", "danger")
      })
  }

  function viewQuizResults(quizId, quizTitle) {
    // Set quiz title in modal
    resultsQuizTitle.textContent = quizTitle

    // Load results
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

    // Show modal
    resultsHistoryModal.show()
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

      const row = document.createElement("tr")
      row.innerHTML = `
        <td>${date}</td>
        <td>${result.score}/${result.total_questions}</td>
        <td>${percentage}%</td>
      `

      resultsTable.appendChild(row)
    })
  }

  function showStatusMessage(message, type) {
    statusMessage.textContent = message
    statusMessage.className = `alert alert-${type}`
    statusMessage.classList.remove("d-none")

    // Hide message after 5 seconds
    setTimeout(() => {
      statusMessage.classList.add("d-none")
    }, 5000)
  }
})
