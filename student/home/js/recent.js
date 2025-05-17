document.addEventListener("DOMContentLoaded", () => {
  // DOM Elements

  const recentQuizzes = document.getElementById("recentQuizzes")


  
  // Load recent quizzes
  loadRecentQuizzes()



  function loadRecentQuizzes() {
    fetch("api/get_quiz.php")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          displayRecentQuizzes(data.quizzes)
        } else {
          console.error("Error fetching quizzes:", data.message)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
      })
  }

  function displayRecentQuizzes(quizzes) {
    recentQuizzes.innerHTML = ""

    if (quizzes.length === 0) {
      recentQuizzes.innerHTML = '<div class="col-12"><p class="text-center">No saved quizzes found.</p></div>'
      return
    }

    // Display up to 4 most recent quizzes
    const recentQuizzesData = quizzes.slice(0, 4)

    recentQuizzesData.forEach((quiz) => {
      const date = new Date(quiz.updated_at)
      const formattedDate = date.toLocaleDateString()

      const quizCard = document.createElement("div")
      quizCard.className = "col-md-3 col-sm-6 mb-3 "
      quizCard.innerHTML = `
                <div class="recent-quiz-card" data-quiz-id="${quiz.quiz_id}" style="background-color: #f8f9fa;
                    border-radius: 8px;
                    padding: 12px;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                    transition: all 0.2s ease;
                    cursor: pointer;
                    height: 100%;">
                    <div class="label-user">Quiz | ${formattedDate}</div>
                    <div class="title">${quiz.title}</div>
                    <div class="date">${quiz.description.substring(0, 30)}${quiz.description.length > 30 ? "..." : ""}</div>
                </div>
            `

      recentQuizzes.appendChild(quizCard)

      // Add click event to load the quiz
      quizCard.querySelector(".recent-quiz-card").addEventListener("click", function () {
        const quizId = this.getAttribute("data-quiz-id")
        window.location.href = `../quiz/edit.php?id=${quizId}`
      })
    })
  }
})