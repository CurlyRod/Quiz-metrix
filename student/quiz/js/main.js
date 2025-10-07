    document.addEventListener("DOMContentLoaded", () => {
        const successAlert = document.getElementById("successAlert");
        const errorAlert = document.getElementById("errorAlert");
        const recentQuizzes = document.getElementById("recentQuizzes");

        // Load recent quizzes immediately
        loadRecentQuizzes();

        function loadRecentQuizzes() {
            fetch("api/get_quizzes.php")
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        displayRecentQuizzes(data.quizzes);
                    } else {
                        console.error("Error fetching quizzes:", data.message);
                        recentQuizzes.innerHTML = '<div class="col-12"><p class="text-center">Error loading quizzes.</p></div>';
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    recentQuizzes.innerHTML = '<div class="col-12"><p class="text-center">Error loading quizzes.</p></div>';
                });
        }

        function displayRecentQuizzes(quizzes) {
            recentQuizzes.innerHTML = "";

            if (!quizzes || quizzes.length === 0) {
                recentQuizzes.innerHTML = '<div class="col-12"><p class="text-center">No saved quizzes found.</p></div>';
                return;
            }

            // Display up to 4 most recent quizzes
            const recentQuizzesData = quizzes.slice(0, 12);

            recentQuizzesData.forEach((quiz) => {
                const date = new Date(quiz.updated_at);
                const formattedDate = date.toLocaleDateString();

                const quizCard = document.createElement("div");
                quizCard.className = "col-md-3 col-sm-6 mb-3";
                quizCard.innerHTML = `
                    <div class="recent-quiz-card" data-quiz-id="${quiz.quiz_id}">
                        <div class="label-user">Quiz | ${formattedDate}</div>
                        <div class="title">${quiz.title}</div>
                        <div class="date">${quiz.description ? (quiz.description.substring(0, 30) + (quiz.description.length > 30 ? "..." : "")) : 'No description'}</div>
                    </div>
                `;

                recentQuizzes.appendChild(quizCard);

                // Add click event to load the quiz
                quizCard.querySelector(".recent-quiz-card").addEventListener("click", function () {
                    const quizId = this.getAttribute("data-quiz-id");
                    window.location.href = `edit.php?id=${quizId}`;
                });
            });
        }

        function showSuccess(message) {
            if (successAlert) {
                successAlert.textContent = message;
                successAlert.classList.remove("d-none");
                if (errorAlert) errorAlert.classList.add("d-none");

                setTimeout(() => {
                    successAlert.classList.add("d-none");
                }, 3000);
            }
        }

        function showError(message) {
            if (errorAlert) {
                errorAlert.textContent = message;
                errorAlert.classList.remove("d-none");
                if (successAlert) successAlert.classList.add("d-none");

                setTimeout(() => {
                    errorAlert.classList.add("d-none");
                }, 5000);
            }
        }
    });
