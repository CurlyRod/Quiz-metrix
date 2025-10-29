document.addEventListener("DOMContentLoaded", () => {
  // DOM Elements
  const quizTitle = document.getElementById("quizTitle")
  const questionContainer = document.getElementById("questionContainer")
  const submitQuizBtn = document.getElementById("submitQuizBtn")
  const submitContainer = document.getElementById("submitContainer")
  const timerDisplay = document.getElementById("timerDisplay")
  const timer = document.getElementById("timer")
  const speakQuestionBtn = document.getElementById("speakQuestionBtn");
  const progressBar = document.getElementById("progressBar")
  const progressText = document.getElementById("progressText")
  const progressPercent = document.getElementById("progressPercent")

  // Initialize exit warning modal
  let exitWarningModal = new bootstrap.Modal(document.getElementById("exitWarningModal"), {
    backdrop: 'static',
    keyboard: false
  });

  // Quiz state
  let currentQuiz = null
  let currentQuizId = null
  let currentQuestionIndex = 0
  let userAnswers = []
  let timerInterval
  let timeRemaining
  let speechSynthesis = window.speechSynthesis;
  let isQuizSubmitted = false;
  let exitDestination = "index.php";
  let quizStateKey = null;
  let preparedQuestions = [];

  // Store the correct answers for each question
  let correctAnswers = []; // Changed from const to let

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

  // Set up exit confirmation button
  document.getElementById("confirmExitBtn").addEventListener("click", function() {
    window.removeEventListener("beforeunload", beforeUnloadHandler);
    clearQuizState();
    window.location.href = exitDestination;
  });

  // Local Storage Functions
  function saveQuizState() {
    if (!currentQuizId || isQuizSubmitted) return;
    
    const quizState = {
      currentQuestionIndex,
      userAnswers,
      timeRemaining,
      preparedQuestions, // Save the randomized questions
      correctAnswers,    // Save the correct answers
      timestamp: Date.now()
    };
    
    localStorage.setItem(quizStateKey, JSON.stringify(quizState));
  }

  function loadQuizState() {
    if (!quizStateKey) return null;
    
    const savedState = localStorage.getItem(quizStateKey);
    if (!savedState) return null;
    
    const state = JSON.parse(savedState);
    
    // Check if state is not too old (e.g., 24 hours)
    const isExpired = Date.now() - state.timestamp > 24 * 60 * 60 * 1000;
    if (isExpired) {
      localStorage.removeItem(quizStateKey);
      return null;
    }
    
    return state;
  }

  
  function clearQuizState() {
    if (quizStateKey) {
      localStorage.removeItem(quizStateKey);
    }
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
    quizTitle.textContent = currentQuiz.title;

    // Set up unique key for this quiz session
    quizStateKey = `quiz_${currentQuizId}_state`;

    // Try to load saved state
    const savedState = loadQuizState();
    if (savedState) {
      currentQuestionIndex = savedState.currentQuestionIndex;
      userAnswers = savedState.userAnswers;
      timeRemaining = savedState.timeRemaining;
      preparedQuestions = savedState.preparedQuestions;
      correctAnswers = savedState.correctAnswers;
    } else {
      // Initialize fresh state - generate randomized questions once
      userAnswers = Array(currentQuiz.questions.length).fill(null);
      timeRemaining = currentQuiz.settings && currentQuiz.settings.timed ? currentQuiz.settings.time * 60 : 0;
      preparedQuestions = prepareQuizQuestions(); // Generate randomized questions once
    }

    // Set up navigation interception
    setupNavigationInterception();

    // Set up timer if quiz is timed
    if (currentQuiz.settings && currentQuiz.settings.timed) {
      timerDisplay.classList.remove("d-none");
      updateTimerDisplay();
      startTimer();
    } else {
      timerDisplay.classList.add("d-none");
    }

    // Ensure answer types are properly set
    if (!currentQuiz.settings.answerTypes || currentQuiz.settings.answerTypes.length === 0) {
      currentQuiz.settings.answerTypes = ['typed']; // default fallback
    }

    speakQuestionBtn.addEventListener("click", () => speakQuestion(0));

    // Show all questions at once
    showAllQuestions();

    // Set up submit button
    submitQuizBtn.addEventListener("click", validateAndSubmitQuiz);
    
    // Update progress display
    updateProgress();
  }

  function prepareQuizQuestions() {
  const questions = JSON.parse(JSON.stringify(currentQuiz.questions)); // Deep copy
  
  // Create a shuffled copy of questions
  const shuffledQuestions = JSON.parse(JSON.stringify(currentQuiz.questions));
  shuffleArray(shuffledQuestions);
  
  const preparedQuestions = [];
  
  // If we have fewer questions than needed, we'll need to reuse some
  // But try to minimize duplicates in the same quiz
  for (let i = 0; i < questions.length; i++) {
    let questionForSlot;
    
    if (i < shuffledQuestions.length) {
      // Use a unique question from the shuffled array
      questionForSlot = { ...shuffledQuestions[i] };
    } else {
      // If we need more questions than available, reuse from the beginning
      const reuseIndex = i % shuffledQuestions.length;
      questionForSlot = { ...shuffledQuestions[reuseIndex] };
    }
    
    // Assign random answer type if not specified
    if (!currentQuiz.settings.answerTypes.includes(questionForSlot.answerType)) {
      const availableTypes = currentQuiz.settings.answerTypes.length > 0 
        ? currentQuiz.settings.answerTypes 
        : ['typed'];
      const randomIndex = Math.floor(Math.random() * availableTypes.length);
      questionForSlot.answerType = availableTypes[randomIndex];
    }
    
    // Set up the question based on its answer type
    switch (questionForSlot.answerType) {
      case "multiple":
        questionForSlot.options = generateMultipleChoiceOptions(currentQuiz.questions, questionForSlot, i);
        correctAnswers[i] = questionForSlot.term;
        break;

      case "typed":
        correctAnswers[i] = questionForSlot.term;
        break;

      case "truefalse":
        const isTrueStatement = Math.random() < 0.5;
        if (isTrueStatement) {
          questionForSlot.statement = `${questionForSlot.term} – ${questionForSlot.description}`;
          correctAnswers[i] = "true";
        } else {
          const otherQuestions = currentQuiz.questions.filter(q => q.term !== questionForSlot.term);
          if (otherQuestions.length > 0) {
            const randomQuestion = otherQuestions[Math.floor(Math.random() * otherQuestions.length)];
            questionForSlot.statement = `${questionForSlot.term} – ${randomQuestion.description}`;
          } else {
            questionForSlot.statement = `${questionForSlot.term} – ${questionForSlot.description} (modified)`;
          }
          correctAnswers[i] = "false";
        }
        break;
    }
    
    preparedQuestions.push(questionForSlot);
  }
  
  return preparedQuestions;
}

  function speakQuestion(questionIndex) {
    // Stop any ongoing speech
    if (speechSynthesis.speaking) {
      speechSynthesis.cancel();
    }
    
    const question = preparedQuestions[questionIndex];
    let textToSpeak = "";
    
    // Format text based on question type
    switch (question.answerType) {
      case "multiple":
      case "typed":
        textToSpeak = question.description;
        break;
      case "truefalse":
        textToSpeak = "True or False: " + question.statement;
        break;
    }
    
    const speak = () => {
      const utterance = new SpeechSynthesisUtterance(textToSpeak);
      utterance.rate = 1.0;
      utterance.pitch = 1.0;
      utterance.volume = 1.0;
      
      const voices = speechSynthesis.getVoices();
      let selectedVoice = voices.find(voice => 
        (voice.name.includes("Premium") || voice.name.includes("Enhanced")) && 
        voice.lang.includes(navigator.language.split('-')[0])
      );
      
      if (!selectedVoice) {
        selectedVoice = voices.find(voice => 
          voice.localService && 
          voice.lang.includes(navigator.language.split('-')[0])
        );
      }
      
      if (!selectedVoice) {
        selectedVoice = voices.find(voice => 
          voice.lang.includes(navigator.language.split('-')[0])
        );
      }
      
      if (!selectedVoice && voices.length > 0) {
        selectedVoice = voices[0];
      }
      
      if (selectedVoice) {
        utterance.voice = selectedVoice;
      }
      
      speechSynthesis.speak(utterance);
    };

    if (speechSynthesis.getVoices().length === 0) {
      speechSynthesis.onvoiceschanged = speak;
    } else {
      speak();
    }
  }

  function generateMultipleChoiceOptions(allQuestions, question, questionIndex) {
    const options = [question.term];
    const otherTerms = allQuestions.filter((q, i) => i !== questionIndex).map((q) => q.term);
    shuffleArray(otherTerms);

    const numberOfOptions = Math.min(4, otherTerms.length + 1);
    while (options.length < numberOfOptions && otherTerms.length > 0) {
      options.push(otherTerms.pop());
    }

    while (options.length < 4) {
      options.push(`Option ${options.length + 1}`);
    }

    shuffleArray(options);
    return options;
  }

  function showAllQuestions() {
    // Clear question container
    questionContainer.innerHTML = "";

    // Create all question cards
    preparedQuestions.forEach((question, index) => {
      const questionCard = createQuestionCard(question, index);
      questionContainer.appendChild(questionCard);
    });

    // Show submit button
    submitContainer.style.display = "block";

    // Add event listeners to all answers
    addAllAnswerEventListeners();
  }

  function createQuestionCard(question, index) {
    const questionCard = document.createElement("div");
    questionCard.className = "question-card";
    questionCard.id = `question-${index}`;
    
    let questionContent = `
      <div class="d-flex justify-content-between align-items-start mb-3">
        <span class="question-badge">
          <i class='bx bx-question-mark'></i>
          Question ${index + 1} of ${preparedQuestions.length}
        </span>
        <button class="speak-btn" onclick="speakQuestionAt(${index})" title="Speak this question">
          <i class='bx bx-volume-full'></i>
        </button>
      </div>
    `;

    // Create question content based on answer type
    switch (question.answerType) {
      case "multiple":
        questionContent += `
          <div class="question-description">${question.description}</div>
          ${createMultipleChoiceInterface(index, question)}
        `;
        break;

      case "typed":
        questionContent += `
          <div class="question-description">${question.description}</div>
          <p class="text-muted mb-3">Define: <strong>${question.term}</strong></p>
          ${createTypedAnswerInterface(index, question)}
        `;
        break;

      case "truefalse":
        questionContent += `
          <div class="question-description">${question.statement}</div>
          ${createTrueFalseInterface(index, question)}
        `;
        break;
    }

    questionCard.innerHTML = questionContent;
    return questionCard;
  }

  // Global function for speak buttons
  window.speakQuestionAt = function(index) {
    speakQuestion(index);
  }

  function addAllAnswerEventListeners() {
    preparedQuestions.forEach((question, index) => {
      if (question.answerType === "multiple" || question.answerType === "truefalse") {
        const radioInputs = document.querySelectorAll(`input[name="q${index}"]`);
        radioInputs.forEach((input) => {
          input.addEventListener("change", function() {
            userAnswers[index] = this.value;
            markQuestionAsAnswered(index);
            saveQuizState();
            updateProgress();
            autoScrollToNext(index);
          });

          // Check the radio button if it matches the saved answer
          if (input.value === userAnswers[index]) {
            input.checked = true;
            markQuestionAsAnswered(index);
          }
        });
      } else if (question.answerType === "typed") {
        const typedInput = document.getElementById(`typedAnswer${index}`);
        if (typedInput) {
          typedInput.value = userAnswers[index] || "";
          
          // Auto-advance on blur or Enter key
          typedInput.addEventListener("blur", function() {
            if (this.value.trim()) {
              userAnswers[index] = this.value;
              markQuestionAsAnswered(index);
              saveQuizState();
              updateProgress();
              autoScrollToNext(index);
            }
          });

          typedInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter" && this.value.trim()) {
              this.blur(); // Trigger blur event
            }
          });

          if (userAnswers[index]) {
            markQuestionAsAnswered(index);
          }
        }
      }
    });
  }

  function markQuestionAsAnswered(index) {
    const questionCard = document.getElementById(`question-${index}`);
    if (questionCard) {
      questionCard.classList.add("answered");
    }
  }

  function autoScrollToNext(currentIndex) {
    // Find next unanswered question
    let nextIndex = currentIndex + 1;
    
    // Skip to next unanswered question
    while (nextIndex < preparedQuestions.length && userAnswers[nextIndex] !== null) {
      nextIndex++;
    }

    // Scroll to next question or submit button
    setTimeout(() => {
      if (nextIndex < preparedQuestions.length) {
        const nextQuestion = document.getElementById(`question-${nextIndex}`);
        if (nextQuestion) {
          nextQuestion.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      } else {
        // All questions answered, scroll to submit button
        submitContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }, 300);
  }

  function updateProgress() {
    const answeredCount = userAnswers.filter(a => a !== null).length;
    const totalQuestions = preparedQuestions.length;
    const percentage = Math.round((answeredCount / totalQuestions) * 100);

    progressBar.style.width = percentage + "%";
    progressText.textContent = `${answeredCount} of ${totalQuestions} answered`;
    progressPercent.textContent = `${percentage}%`;
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
        <input 
          type="text" 
          class="form-control" 
          id="typedAnswer${index}" 
          placeholder="Type your answer and press Enter..." 
          value="${userAnswers[index] || ""}"
          ${userAnswers[index] ? 'disabled' : ''}
        >
      </div>
    `;
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
  

  function startTimer() {
    timerInterval = setInterval(() => {
      timeRemaining--
      updateTimerDisplay()

      // Save state every 10 seconds to preserve timer progress
      if (timeRemaining % 10 === 0) {
        saveQuizState();
      }

      if (timeRemaining <= 0) {
        clearInterval(timerInterval)
        submitQuiz()
      }
    }, 1000)
  }

  function updateTimerDisplay() {
    if (timeRemaining <= 0) {
      timer.textContent = "00:00";
      timer.classList.add("warning");
      return;
    }
    
    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    timer.textContent = `${minutes.toString().padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`;
    
    // Add warning class when time is running low
    if (timeRemaining < 60) {
      timer.classList.add("warning");
    } else {
      timer.classList.remove("warning");
    }
  }

  // Validate if all the questions are answered
  function validateAndSubmitQuiz() {
  // Count unanswered questions
  const unansweredQuestions = userAnswers.reduce((count, answer, index) => {
    return answer === null || answer === "" ? count + 1 : count;
  }, 0);

  // If there are unanswered questions, show warning and scroll to top
  if (unansweredQuestions > 0) {
    // Scroll to top of the page - more reliable methods
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Alternative scroll methods if the above doesn't work
    document.documentElement.scrollTop = 0;
    document.body.scrollTop = 0;
    
    // Create or update warning div
    let warningDiv = document.getElementById('unansweredWarning');
    if (!warningDiv) {
      warningDiv = document.createElement('div');
      warningDiv.id = 'unansweredWarning';
      warningDiv.className = 'alert alert-warning alert-dismissible fade show';
      warningDiv.style.margin = '20px 0';
      warningDiv.style.position = 'sticky';
      warningDiv.style.top = '0';
      warningDiv.style.zIndex = '1000';
      
      // Insert the warning at the very top of the content
      const mainContainer = document.querySelector('.container, main, body');
      mainContainer.insertBefore(warningDiv, mainContainer.firstChild);
    }
    
    // Update warning message
    const questionText = unansweredQuestions === 1 ? 'question' : 'questions';
    warningDiv.innerHTML = `
      <strong>Warning!</strong> You have ${unansweredQuestions} unanswered ${questionText}. 
      Please answer all questions before submitting.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Force a reflow and scroll again to ensure it works
    setTimeout(() => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }, 100);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
      if (warningDiv && warningDiv.parentNode) {
        warningDiv.remove();
      }
    }, 5000);
    
    return; // Stop the submission process
  }
  
  // If all questions are answered, proceed with submission
  submitQuiz();
}

  function submitQuiz() {
    isQuizSubmitted = true;
    if (timerInterval) {
      clearInterval(timerInterval);
    }
    clearQuizState();

    let score = 0;
    const results = [];

    preparedQuestions.forEach((question, index) => { // Use preparedQuestions
      let isCorrect = false;
      const userAnswer = userAnswers[index];
      const correctAnswer = correctAnswers[index];

      if (userAnswer) {
        switch (question.answerType) {
          case "multiple":
            isCorrect = userAnswer === correctAnswer;
            break;
          case "typed":
            isCorrect = userAnswer.toLowerCase() === correctAnswer.toLowerCase();
            break;
          case "truefalse":
            isCorrect = userAnswer === correctAnswer;
            break;
        }
        if (isCorrect) score++;
      }

      let questionText = "";
      switch (question.answerType) {
        case "multiple":
        case "typed":
          questionText = question.description;
          break;
        case "truefalse":
          questionText = question.statement;
          break;
      }

      results.push({
        question: questionText,
        userAnswer: userAnswer || "No answer",
        correctAnswer: correctAnswer,
        answerType: question.answerType,
        isCorrect: isCorrect,
      });
    });

    if (currentQuizId) {
      saveResultToDatabase(currentQuizId, score, preparedQuestions.length);
    }

    showResults(score, preparedQuestions.length, results);
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
          // console.log("Result saved successfully with ID:", data.result_id)
        } else {
          // console.error("Error saving result:", data.message)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
      })
  }

  function showResults(score, totalQuestions, results) {
    isQuizSubmitted = true;
    // Remove the beforeunload event listener
    window.removeEventListener("beforeunload", beforeUnloadHandler);
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

  function setupNavigationInterception() {
    // Use event delegation instead of adding listeners to each link
    document.body.addEventListener('click', function(e) {
      if (isQuizSubmitted) return;
      
      const link = e.target.closest('a');
      if (!link || link.id === 'confirmExitBtn') return;
      
      const href = link.getAttribute('href');
      if (!href || href === '#' || href.startsWith('javascript:')) return;
      
      e.preventDefault();
      exitDestination = href;
      exitWarningModal.show();
    });

    window.addEventListener('beforeunload', beforeUnloadHandler);
  }

  function beforeUnloadHandler(e) {
    // Don't show the confirmation if the quiz is already submitted
    if (isQuizSubmitted) return;
    
    // Standard way of showing a confirmation message
    const confirmationMessage = "Are you sure you want to leave? Your quiz progress will be lost.";
    e.returnValue = confirmationMessage;
    return confirmationMessage;
  }

  function resetQuiz() {
    clearQuizState();
    userAnswers = Array(currentQuiz.questions.length).fill(null);
    
    // Regenerate randomized questions for fresh start
    preparedQuestions = prepareQuizQuestions();
    
    if (currentQuiz.settings && currentQuiz.settings.timed) {
      timeRemaining = currentQuiz.settings.time * 60;
      updateTimerDisplay();
      if (timerInterval) {
        clearInterval(timerInterval);
      }
      startTimer();
    }

    showAllQuestions();
    updateProgress();
  }

  // Utility function to shuffle array
  function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1))
      ;[array[i], array[j]] = [array[j], array[i]]
    }
  }
})