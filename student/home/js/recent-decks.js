document.addEventListener("DOMContentLoaded", () => {
  const recentDecks = document.getElementById("recentDecks")

  // Load recent decks
  loadRecentDecks()

  function loadRecentDecks() {
    fetch("api/get_decks.php")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          displayRecentDecks(data.decks)
        } else {
          console.error("Error fetching decks:", data.message)
          recentDecks.innerHTML = '<div class="col-12"><p class="text-center">No decks found.</p></div>'
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

    // Display up to 4 most recent decks
    const recentDecksData = decks.slice(0, 4)

    recentDecksData.forEach((deck) => {
      const date = new Date(deck.updated_at)
      const formattedDate = date.toLocaleDateString()

      const deckCard = document.createElement("div")
      deckCard.className = "col-md-3 col-sm-6 mb-3"
      deckCard.innerHTML = `
                <div class="recent-deck-card" data-deck-id="${deck.deck_id}"
                style="background-color: #f8f9fa;
                    border-radius: 8px;
                    padding: 12px;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                    transition: all 0.2s ease;
                    cursor: pointer;
                    height: 100%;
                    border:0.5px groove;">
                    <div class="label-user" style="font-size: 11px;">Deck | ${formattedDate}</div>
                    <div class="title" style="font-size: 18px; font-weight: bold;">${deck.title}</div>
                    <div class="date"style="font-size: 11px;">${deck.description ? deck.description.substring(0, 30) + (deck.description.length > 30 ? "..." : "") : "No description"}</div>
                    <div class="card-count" style="font-size: 11px; color: #666;">${deck.card_count} cards</div>
                </div>
            `

      recentDecks.appendChild(deckCard)

      // Add click event to load the deck
      deckCard.querySelector(".recent-deck-card").addEventListener("click", function () {
        const deckId = this.getAttribute("data-deck-id")
        window.location.href = `../flashcard/edit-deck.php?id=${deckId}`
      })
    })
  }
})