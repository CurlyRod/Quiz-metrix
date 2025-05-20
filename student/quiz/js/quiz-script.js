document.addEventListener("DOMContentLoaded", () => {
  // DOM Elements
  const quizTitle = document.getElementById("quizTitle")
  const quizDescription = document.getElementById("quizDescription")
  const questionContainer = document.getElementById("questionContainer")
  const questionProgress = document.getElementById("questionProgress")
  const prevBtn = document.getElementById("prevBtn")
  const nextBtn = document.getElementById("nextBtn")
  const submitQuizBtn = document.getElementById("submitQuizBtn")
  const timerDisplay = document.getElementById("timerDisplay")
  const timer = document.getElementById("timer")

  // Quiz state
  let currentQuiz = null
  let currentQuizId = null
  let currentQuestionIndex = 0
  let userAnswers = []
  let timerInterval
  let timeRemaining

  // Store the correct answers for each question
  const correctAnswers = []

  // Check if we have a quiz ID in the URL
  const urlParams = new URLSearchParams(window.location.search)
  const quizId = urlParams.get("id")

  if (quizId) {
    // Load quiz from database
    loadQuizFromDatabase(quizId)
  } else {
    // No quiz ID, redirect to home
    window.location.href = "index.php"
  }

  function loadQuizFromDatabase(quizId) {
    fetch(`api/get_quiz.php?id=${quizId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          currentQuiz = data.quiz
          currentQuizId = currentQuiz.quiz_id
          initQuiz()
        } else {
          console.error("Error loading quiz:", data.message)
          alert("Error loading quiz. Redirecting to home page.")
          window.location.href = "index.php"
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error loading quiz. Redirecting to home page.")
        window.location.href = "index.php"
      })
  }

  function initQuiz() {
    // Set quiz title and description
    quizTitle.textContent = currentQuiz.title
    quizDescription.textContent = currentQuiz.description

    // Initialize user answers array
    userAnswers = Array(currentQuiz.questions.length).fill(null)

    // Prepare the questions based on their answer types
    prepareQuizQuestions()

    // Set up timer if quiz is timed
    if (currentQuiz.settings && currentQuiz.settings.timed) {
      timerDisplay.classList.remove("d-none")
      timeRemaining = currentQuiz.settings.time * 60 // Convert to seconds
      updateTimerDisplay()
      startTimer()
    }

    // Show first question
    showQuestion(0)

    // Set up event listeners
    prevBtn.addEventListener("click", showPreviousQuestion)
    nextBtn.addEventListener("click", showNextQuestion)
    submitQuizBtn.addEventListener("click", submitQuiz)

    // Hide submit button initially
    submitQuizBtn.style.display = "none"

    // Disable previous button on first question
    prevBtn.disabled = true
  }

  function prepareQuizQuestions() {
    // Process each question based on its answer type
    currentQuiz.questions.forEach((question, index) => {
      // Only process the question if its answer type is one of the selected types
      
      if (!currentQuiz.settings.answerTypes.includes(question.answerType)) {
        // If the question's type isn't in the selected types, force it to be one that is
        const availableTypes = currentQuiz.settings.answerTypes.length > 0 
          ? currentQuiz.settings.answerTypes 
          : ['typed']; // fallback
        const randomIndex = Math.floor(Math.random() * availableTypes.length);
        question.answerType = availableTypes[randomIndex];
      }
      switch (question.answerType) {
        case "multiple":
          // For multiple choice, we need to generate options
          question.options = generateMultipleChoiceOptions(question, index)
          correctAnswers[index] = question.term
          break

        case "typed":
          // For typed answers, the correct answer is the term
          correctAnswers[index] = question.term
          break

        case "truefalse":
          // For true/false, we need to decide if it's true or false
          const isTrueStatement = Math.random() < 0.5 // 50% chance of being true

          if (isTrueStatement) {
            // True statement - use the correct term-description pair
            question.statement = `${question.term} – ${question.description}`
            correctAnswers[index] = "true"
          } else {
            // False statement - mismatch term with a different description
            const otherQuestions = currentQuiz.questions.filter((q, i) => i !== index)

            if (otherQuestions.length > 0) {
              const randomQuestion = otherQuestions[Math.floor(Math.random() * otherQuestions.length)]
              // Use current term with a different description
              question.statement = `${question.term} – ${randomQuestion.description}`
            } else {
              // If there are no other questions, modify the description slightly
              question.statement = `${question.term} – ${question.description} (modified)`
            }
            correctAnswers[index] = "false"
          }
          break
      }
    })
  }

  function generateMultipleChoiceOptions(question, questionIndex) {
    // Create an array with the correct answer
    const options = [question.term]

    // Get terms from other questions to use as distractors
    const otherTerms = currentQuiz.questions.filter((q, i) => i !== questionIndex).map((q) => q.term)

    // Shuffle the other terms
    shuffleArray(otherTerms)

    // Add 3 distractors (or fewer if not enough other terms)
    const numberOfOptions = Math.min(4, otherTerms.length + 1)
    while (options.length < numberOfOptions && otherTerms.length > 0) {
      options.push(otherTerms.pop())
    }

    // If we still need more options (not enough other terms)
    while (options.length < 4) {
      options.push(`Option ${options.length + 1}`)
    }

    // Shuffle the options
    shuffleArray(options)

    return options
  }

  function showQuestion(index) {
    // Update current question index
    currentQuestionIndex = index

    // Update question progress
    questionProgress.textContent = `Question ${index + 1} of ${currentQuiz.questions.length}`

    // Clear question container
    questionContainer.innerHTML = ""

    // Get current question
    const question = currentQuiz.questions[index]

    // Create question element
    const questionElement = document.createElement("div")
    questionElement.className = "card mb-4"

    // Create question header
    let questionContent = `
            <div class="card-header">
                <h5 class="mb-0">Question ${index + 1}</h5>
            </div>
            <div class="card-body">
        `

    // Create question content based on answer type
    switch (question.answerType) {
      case "multiple":
        questionContent += `
                    <div class="question-description mb-4">${question.description}</div>
                    ${createMultipleChoiceInterface(index, question)}
                `
        break

      case "typed":
        questionContent += `
                    <div class="question-description mb-4">${question.description}</div>
                    ${createTypedAnswerInterface(index, question)}
                `
        break

      case "truefalse":
        questionContent += `
                    <div class="question-description mb-4">${question.statement}</div>
                    ${createTrueFalseInterface(index, question)}
                `
        break
    }

    questionContent += `</div>`
    questionElement.innerHTML = questionContent
    questionContainer.appendChild(questionElement)

    // Add event listeners to save answers
    if (question.answerType === "multiple") {
      const radioInputs = questionContainer.querySelectorAll('input[type="radio"]')
      radioInputs.forEach((input) => {
        input.addEventListener("change", function () {
          userAnswers[currentQuestionIndex] = this.value
        })

        // Check the radio button if it matches the saved answer
        if (input.value === userAnswers[currentQuestionIndex]) {
          input.checked = true
        }
      })
    } else if (question.answerType === "typed") {
      const typedInput = questionContainer.querySelector("#typedAnswer")
      typedInput.value = userAnswers[currentQuestionIndex] || ""
      typedInput.addEventListener("input", function () {
        userAnswers[currentQuestionIndex] = this.value
      })
    } else if (question.answerType === "truefalse") {
      const radioInputs = questionContainer.querySelectorAll('input[type="radio"]')
      radioInputs.forEach((input) => {
        input.addEventListener("change", function () {
          userAnswers[currentQuestionIndex] = this.value
        })

        // Check the radio button if it matches the saved answer
        if (input.value === userAnswers[currentQuestionIndex]) {
          input.checked = true
        }
      })
    }

    // Update navigation buttons
    updateNavigationButtons()
  }

function createMultipleChoiceInterface(index, question) {
  let content = `<div class="multiple-choice-container mt-3">`

  question.options.forEach((option, i) => {
    const checked = userAnswers[index] === option ? "checked" : ""
    content += `
      <div class="quiz-option">
        <label for="q${index}opt${i}">
          <input type="radio" name="q${index}" id="q${index}opt${i}" value="${option}" ${checked}>
          ${option}
        </label>
      </div>
    `
    document.addEventListener("click", function (e) {
  const option = e.target.closest(".quiz-option");
  if (option && option.querySelector("input[type='radio']")) {
    option.querySelector("input[type='radio']").checked = true;

    // Optional: trigger change event if needed
    option.querySelector("input[type='radio']").dispatchEvent(new Event("change"));
  }
});

})

  content += `</div>`
  return content
}

  function createTypedAnswerInterface(index, question) {
    return `
            <div class="typed-answer-container mt-3">
                <input type="text" class="form-control" id="typedAnswer" placeholder="Type your answer" value="${userAnswers[index] || ""}">
            </div>
        `
  }

  function createTrueFalseInterface(index, question) {
    const trueChecked = userAnswers[index] === "true" ? "checked" : "";
    const falseChecked = userAnswers[index] === "false" ? "checked" : "";
  
    return `
      <div class="true-false-container mt-3">
        <div class="quiz-option true-false-option" onclick="selectOption(this, 'q${index}true')">
          <input type="radio" name="q${index}" id="q${index}true" value="true" ${trueChecked}>
          <label for="q${index}true" class="text-center">True</label>
        </div>
        <div class="quiz-option true-false-option" onclick="selectOption(this, 'q${index}false')">
          <input type="radio" name="q${index}" id="q${index}false" value="false" ${falseChecked}>
          <label for="q${index}false" class="text-center">False</label>
        </div>
      </div>
    `;
  }
  window.selectOption = function(div, inputId) {
    document.getElementById(inputId).checked = true;
  
    const siblings = div.parentElement.querySelectorAll('.quiz-option');
    siblings.forEach(el => el.classList.remove('selected'));
  
    div.classList.add('selected');
  };
  
  function showPreviousQuestion() {
    if (currentQuestionIndex > 0) {
      showQuestion(currentQuestionIndex - 1)
    }
  }

  function showNextQuestion() {
    if (currentQuestionIndex < currentQuiz.questions.length - 1) {
      showQuestion(currentQuestionIndex + 1)
    }
  }

  function updateNavigationButtons() {
    // Disable previous button on first question
    prevBtn.disabled = currentQuestionIndex === 0

    // Show/hide next and submit buttons on last question
    if (currentQuestionIndex === currentQuiz.questions.length - 1) {
      nextBtn.style.display = "none"
      submitQuizBtn.style.display = "block"
    } else {
      nextBtn.style.display = "block"
      submitQuizBtn.style.display = "none"
    }
  }

  function startTimer() {
    timerInterval = setInterval(() => {
      timeRemaining--
      updateTimerDisplay()

      if (timeRemaining <= 0) {
        clearInterval(timerInterval)
        submitQuiz()
      }
    }, 1000)
  }

  function updateTimerDisplay() {
    const minutes = Math.floor(timeRemaining / 60)
    const seconds = timeRemaining % 60
    timer.textContent = `${minutes.toString().padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`
  }

  function submitQuiz() {
    // Stop timer if it's running
    if (timerInterval) {
      clearInterval(timerInterval)
    }

    // Calculate score
    let score = 0
    const results = []

    currentQuiz.questions.forEach((question, index) => {
      let isCorrect = false
      const userAnswer = userAnswers[index]
      const correctAnswer = correctAnswers[index]

      if (userAnswer) {
        // Check if answer is correct based on question type
        switch (question.answerType) {
          case "multiple":
            isCorrect = userAnswer === correctAnswer
            break

          case "typed":
            // Case-insensitive comparison for typed answers
            isCorrect = userAnswer.toLowerCase() === correctAnswer.toLowerCase()
            break

          case "truefalse":
            isCorrect = userAnswer === correctAnswer
            break
        }

        if (isCorrect) {
          score++
        }
      }

      // Format the question text based on question type
      let questionText = ""
      switch (question.answerType) {
        case "multiple":
        case "typed":
          questionText = question.description
          break
        case "truefalse":
          questionText = question.statement
          break
      }

      results.push({
        question: questionText,
        userAnswer: userAnswer || "No answer",
        correctAnswer: correctAnswer,
        answerType: question.answerType,
        isCorrect: isCorrect,
      })
    })

    // Save result to database if we have a quiz ID
    if (currentQuizId) {
      saveResultToDatabase(currentQuizId, score, currentQuiz.questions.length)
    }

    // Show results modal
    showResults(score, currentQuiz.questions.length, results)
  }

  function saveResultToDatabase(quizId, score, totalQuestions) {
    fetch("api/save_result.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        quiz_id: quizId,
        score: score,
        total_questions: totalQuestions,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          console.log("Result saved successfully with ID:", data.result_id)
        } else {
          console.error("Error saving result:", data.message)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
      })
  }

  function showResults(score, totalQuestions, results) {
    const scoreDisplay = document.getElementById("scoreDisplay")
    const percentageDisplay = document.getElementById("percentageDisplay")
    const answerReview = document.getElementById("answerReview")

    // Set score
    scoreDisplay.textContent = `${score}/${totalQuestions}`
    const percentage = Math.round((score / totalQuestions) * 100)
    percentageDisplay.textContent = `${percentage}%`

    // Generate answer review
    answerReview.innerHTML = ""

    results.forEach((result, index) => {
      const reviewItem = document.createElement("div")
      reviewItem.className = "answer-review-item"

      let answerTypeLabel = ""
      switch (result.answerType) {
        case "multiple":
          answerTypeLabel = "Multiple Choice"
          break
        case "typed":
          answerTypeLabel = "Type it"
          break
        case "truefalse":
          answerTypeLabel = "True or False"
          break
      }

      reviewItem.innerHTML = `
                <p><strong>Question ${index + 1} (${answerTypeLabel}):</strong> ${result.question}</p>
                <p>Your answer: <span class="${result.isCorrect ? "correct-answer" : "incorrect-answer"}">${result.userAnswer}</span></p>
                ${!result.isCorrect ? `<p>Correct answer: <span class="correct-answer">${result.correctAnswer}</span></p>` : ""}
            `

      answerReview.appendChild(reviewItem)
    })

    // Show modal
    const resultsModal = new bootstrap.Modal(document.getElementById("resultsModal"))
    resultsModal.show()

    // Set up retake button
    document.getElementById("retakeQuizBtn").addEventListener("click", () => {
      resultsModal.hide()
      resetQuiz()
    })
  }

  function resetQuiz() {
    // Reset user answers
    userAnswers = Array(currentQuiz.questions.length).fill(null)

    // Reset timer if quiz is timed
    if (currentQuiz.settings && currentQuiz.settings.timed) {
      timeRemaining = currentQuiz.settings.time * 60
      updateTimerDisplay()
      if (timerInterval) {
        clearInterval(timerInterval)
      }
      startTimer()
    }

    // Prepare questions again to regenerate options and true/false statements
    prepareQuizQuestions()

    // Show first question
    showQuestion(0)
  }

  // Utility function to shuffle array
  function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1))
      ;[array[i], array[j]] = [array[j], array[i]]
    }
  }
})
