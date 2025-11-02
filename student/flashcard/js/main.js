document.addEventListener("DOMContentLoaded", () => {
  const successAlert = document.getElementById("successAlert")
  const errorAlert = document.getElementById("errorAlert")
  const recentDecks = document.getElementById("recentDecks")

  loadRecentDecks()

  function loadRecentDecks() {
    fetch("api/get_decks.php")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          displayRecentDecks(data.decks)
        } else {
          console.error("Error fetching decks:", data.message)
          recentDecks.innerHTML = '<div class="col-12"><p class="text-center">Error loading decks.</p></div>'
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        recentDecks.innerHTML = '<div class="col-12"><p class="text-center">Error loading decks.</p></div>'
      })
  }

  function displayRecentDecks(decks) {
    recentDecks.innerHTML = ""

    if (!decks || decks.length === 0) {
      recentDecks.innerHTML = '<div class="col-12"><p class="text-center">No saved decks found.</p></div>'
      return
    }

    const recentDecksData = decks.slice(0, 12)

    recentDecksData.forEach((deck) => {
      const date = new Date(deck.updated_at)
      const formattedDate = date.toLocaleDateString()

      const deckCard = document.createElement("div")
      deckCard.className = "col-md-3 col-sm-6 mb-3"
      deckCard.innerHTML = `
                <div class="recent-quiz-card" data-deck-id="${deck.deck_id}">
                    <div class="label-user">Deck | ${formattedDate}</div>
                    <div class="title">${deck.title}</div>
                    <div class="date">${deck.description ? deck.description.substring(0, 30) + (deck.description.length > 30 ? "..." : "") : "No description"}</div>
                    <div class="card-count">${deck.card_count} cards</div>
                </div>
            `

      recentDecks.appendChild(deckCard)

      deckCard.querySelector(".recent-quiz-card").addEventListener("click", function () {
        const deckId = this.getAttribute("data-deck-id")
        window.location.href = `edit-deck.php?id=${deckId}`
      })
    })
  }
})
