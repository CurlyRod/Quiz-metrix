// Add this function to auto-resize textareas
function autoResizeTextarea(textarea) {
  // Reset height to calculate the right scrollHeight
  textarea.style.height = "auto"

  // Set the height to match content
  textarea.style.height = textarea.scrollHeight + "px"
}

document.addEventListener("DOMContentLoaded", () => { 


  // DOM Elements
  const noteCollapsed = document.getElementById("note-collapsed")
  const noteExpanded = document.getElementById("note-expanded")
  const noteTitle = document.getElementById("note-title")
  const noteContent = document.getElementById("note-content")   
  const userId = document.getElementById("user-current-id")  
  const btnSave = document.getElementById("btn-save")
  const btnClose = document.getElementById("btn-close")
  const notesContainer = document.getElementById("notes-container")
  const colorPalette = document.getElementById("color-palette")

  // Current selected color for new note
  let currentNoteColor = "default"

  // Add color selection functionality
  if (colorPalette) {
    const colorOptions = colorPalette.querySelectorAll(".color-option")
    colorOptions.forEach((option) => {
      option.addEventListener("click", function () {
        // Remove selected class from all options
        colorOptions.forEach((opt) => opt.classList.remove("selected"))

        // Add selected class to clicked option
        this.classList.add("selected")

        // Update current color
        currentNoteColor = this.dataset.color

        // Update the expanded note background color for preview
        noteExpanded.className = "card shadow-sm d-block note-color-" + currentNoteColor
      })
    })
  }

  // Add auto-resize event listener to the note content textarea
  if (noteContent) {
    noteContent.addEventListener("input", function () {
      autoResizeTextarea(this)
    })
  }

  // Check if elements exist before adding event listeners
  if (noteCollapsed) {
    noteCollapsed.addEventListener("click", expandNoteInput)
  } else {
  }

  if (btnClose) {
    btnClose.addEventListener("click", collapseNoteInput)
  }

  if (btnSave) {
    btnSave.addEventListener("click", saveNote)
  }

  // Load notes on page load
  loadNotes()

  // Functions
  function expandNoteInput() {
    noteCollapsed.classList.add("d-none")
    noteExpanded.classList.remove("d-none")
    noteTitle.focus()

    // Reset color selection to default
    if (colorPalette) {
      const colorOptions = colorPalette.querySelectorAll(".color-option")
      colorOptions.forEach((opt) => opt.classList.remove("selected"))
      const defaultColor = colorPalette.querySelector('[data-color="default"]')
      if (defaultColor) defaultColor.classList.add("selected")
      currentNoteColor = "default"
      noteExpanded.className = "card shadow-sm d-block note-color-default"
    }
  }

  function collapseNoteInput() {
    noteExpanded.classList.add("d-none")
    noteCollapsed.classList.remove("d-none")
    noteTitle.value = ""
    noteContent.value = ""
    // Reset textarea height
    noteContent.style.height = "auto"
    // Reset color
    currentNoteColor = "default"
    noteExpanded.className = "card shadow-sm d-none"
  }

  function saveNote() {
    const title = noteTitle.value.trim()
    const content = noteContent.value.trim() 
    const user_id =  userId.value.trim();

    if (content === "") {
      alert("Please enter some content for your note.")
      return
    }

    // Create form data
    const formData = new FormData()
    formData.append("title", title)
    formData.append("content", content)
    formData.append("color", currentNoteColor)
    formData.append("action", "create") 
    formData.append("user-current-id", user_id);

    // Send data to server
    fetch("notes_api.php", {
      method: "POST",
      body: formData ,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          collapseNoteInput()
          loadNotes()
        } else {
          alert("Error saving note: " + data.message)
        }
      })
  }

  function loadNotes() {     


   fetch(`notes_api.php?action=read`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          renderNotes(data.notes)
        } else {
          console.error("Error loading notes:", data.message)
        }
      })
  } 





  function renderNotes(notes) {
    notesContainer.innerHTML = ""

    if (!notes || notes.length === 0) {
      return
    }

    notes.forEach((note) => {
      const truncatedContent = note.content.length > 250 ? note.content.substring(0, 250) + "..." : note.content
      const noteColor = note.color || "default"

      const noteElement = document.createElement("div")
      noteElement.className = "col-md-4 col-sm-6 mb-4"
      noteElement.innerHTML = `
                <div class="card note-card h-100 note-color-${noteColor}" data-id="${note.id}" data-color="${noteColor}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">${note.title || "Untitled"}</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item view-note" href="#">View</a></li>
                                    <li><a class="dropdown-item edit-note" href="#">Edit</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item delete-note" href="#">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="note-content-truncated">${truncatedContent}</div>
                        <div class="note-content-full d-none">${note.content}</div>
                        <div class="edit-controls d-none mt-3">
                            <button class="btn btn-sm btn-primary save-edit me-2">Save</button>
                            <button class="btn btn-sm btn-light cancel-edit">Cancel</button>
                        </div>
                    </div>
                </div>
            `

      notesContainer.appendChild(noteElement)
    })

    // Add event listeners to note cards
    addNoteCardEventListeners()
  }

  function addNoteCardEventListeners() {
    // View note
    document.querySelectorAll(".view-note").forEach((btn) => {
      btn.addEventListener("click", function (e) {
        e.preventDefault()
        const noteCard = this.closest(".note-card")
        const truncated = noteCard.querySelector(".note-content-truncated")
        const full = noteCard.querySelector(".note-content-full")

        truncated.classList.toggle("d-none")
        full.classList.toggle("d-none")

        if (full.classList.contains("d-none")) {
          this.textContent = "View"
        } else {
          this.textContent = "Collapse"
        }
      })
    })

    // Edit note
    document.querySelectorAll(".edit-note").forEach((btn) => {
      btn.addEventListener("click", function (e) {
        e.preventDefault()
        const noteCard = this.closest(".note-card")
        const noteId = noteCard.dataset.id
        const noteColor = noteCard.dataset.color || "default"
        const title = noteCard.querySelector(".card-title").textContent
        const content = noteCard.querySelector(".note-content-full").textContent

        // Transform to edit mode
        noteCard.classList.add("edit-mode")
        noteCard.querySelector(".card-title").innerHTML = `
                    <input type="text" class="form-control edit-title" value="${title === "Untitled" ? "" : title}">
                `
        noteCard.querySelector(".note-content-truncated").classList.add("d-none")
        noteCard.querySelector(".note-content-full").innerHTML = `
                    <textarea class="form-control edit-content" rows="5">${content}</textarea>
                    <div class="color-palette edit-color-palette mt-3">
                        <div class="color-option ${noteColor === "default" ? "selected" : ""}" data-color="default" style="background-color: #ffffff;"></div>
                        <div class="color-option ${noteColor === "red" ? "selected" : ""}" data-color="red" style="background-color: #f8d7da;"></div>
                        <div class="color-option ${noteColor === "orange" ? "selected" : ""}" data-color="orange" style="background-color: #fff3cd;"></div>
                        <div class="color-option ${noteColor === "yellow" ? "selected" : ""}" data-color="yellow" style="background-color: #fff8e1;"></div>
                        <div class="color-option ${noteColor === "green" ? "selected" : ""}" data-color="green" style="background-color: #d1e7dd;"></div>
                        <div class="color-option ${noteColor === "teal" ? "selected" : ""}" data-color="teal" style="background-color: #d1ecf1;"></div>
                        <div class="color-option ${noteColor === "blue" ? "selected" : ""}" data-color="blue" style="background-color: #cfe2ff;"></div>
                        <div class="color-option ${noteColor === "purple" ? "selected" : ""}" data-color="purple" style="background-color: #e2d9f3;"></div>
                        <div class="color-option ${noteColor === "pink" ? "selected" : ""}" data-color="pink" style="background-color: #f8d7f7;"></div>
                        <div class="color-option ${noteColor === "gray" ? "selected" : ""}" data-color="gray" style="background-color: #e9ecef;"></div>
                    </div>
                `
        noteCard.querySelector(".note-content-full").classList.remove("d-none")
        noteCard.querySelector(".edit-controls").classList.remove("d-none")
        noteCard.querySelector(".dropdown").classList.add("d-none")

        // Add auto-resize to the edit textarea
        const editTextarea = noteCard.querySelector(".edit-content")
        if (editTextarea) {
          // Initial resize
          autoResizeTextarea(editTextarea)

          // Add event listener for input changes
          editTextarea.addEventListener("input", function () {
            autoResizeTextarea(this)
          })
        }

        // Add color selection functionality to edit mode
        const editColorPalette = noteCard.querySelector(".edit-color-palette")
        if (editColorPalette) {
          const colorOptions = editColorPalette.querySelectorAll(".color-option")
          colorOptions.forEach((option) => {
            option.addEventListener("click", function () {
              // Remove selected class from all options
              colorOptions.forEach((opt) => opt.classList.remove("selected"))

              // Add selected class to clicked option
              this.classList.add("selected")

              // Update note card color for preview
              const selectedColor = this.dataset.color

              // Remove all color classes
              noteCard.className = noteCard.className.replace(/note-color-\w+/g, "")

              // Add new color class
              noteCard.classList.add("note-color-" + selectedColor)
              noteCard.dataset.color = selectedColor
            })
          })
        }
      })
    })

    // Save edit
    document.querySelectorAll(".save-edit").forEach((btn) => {
      btn.addEventListener("click", function () {
        const noteCard = this.closest(".note-card")
        const noteId = noteCard.dataset.id
        const title = noteCard.querySelector(".edit-title").value.trim()
        const content = noteCard.querySelector(".edit-content").value.trim()
        const selectedColorOption = noteCard.querySelector(".edit-color-palette .color-option.selected")
        const color = selectedColorOption ? selectedColorOption.dataset.color : "default"

        if (content === "") {
          alert("Note content cannot be empty.")
          return
        }

        // Create form data
        const formData = new FormData()
        formData.append("id", noteId)
        formData.append("title", title)
        formData.append("content", content)
        formData.append("color", color)
        formData.append("action", "update")

        // Send data to server
        fetch("notes_api.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              loadNotes()
            } else {
              alert("Error updating note: " + data.message)
            }
          })
          .catch((error) => {
            console.error("Error:", error)
            alert("An error occurred while updating the note.")
          })
      })
    })

    // Cancel edit
    document.querySelectorAll(".cancel-edit").forEach((btn) => {
      btn.addEventListener("click", () => {
        loadNotes() // Reload notes to cancel edit
      })
    })

    // Delete note
    document.querySelectorAll(".delete-note").forEach((btn) => {
      btn.addEventListener("click", function (e) {
        e.preventDefault()
        if (confirm("Are you sure you want to delete this note?")) {
          const noteCard = this.closest(".note-card")
          const noteId = noteCard.dataset.id

          // Create form data
          const formData = new FormData()
          formData.append("id", noteId)
          formData.append("action", "delete")

          // Send data to server
          fetch("notes_api.php", {
            method: "POST",
            body: formData,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                loadNotes()
              } else {
                alert("Error deleting note: " + data.message)
              }
            })
            .catch((error) => {
              console.error("Error:", error)
              alert("An error occurred while deleting the note.")
            })
        }
      })
    })
  } 
})
