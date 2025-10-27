function autoResizeTextarea(textarea) {
  textarea.style.height = "auto"
  textarea.style.height = textarea.scrollHeight + "px"
}

document.addEventListener("DOMContentLoaded", () => {
  const notesContainer = document.getElementById("notes-container");

  const createModal = document.getElementById("createNoteModal");
  const createBtn = document.getElementById("createNoteBtn");
  const btnClose = document.getElementById("btn-close");
  const btnSave = document.getElementById("btn-save");
  const bulkDeleteBtn = document.getElementById("bulkDeleteBtn");

  const selectItemsBtn = document.getElementById("selectItemsBtn"); 
  let selectionMode = false; 

  const noteTitle = document.getElementById("note-title");
  const noteContent = document.getElementById("note-content");
  const charCount = document.getElementById("charCount");
  const userId = document.getElementById("user-current-id");
  const colorPalette = document.getElementById("color-palette");
  const noteExpanded = document.getElementById("note-expanded");

  const viewModal = document.getElementById("viewNoteModal");
  const viewBackBtn = document.getElementById("viewBackBtn");
  const viewTitle = document.getElementById("viewTitle");
  const viewContent = document.getElementById("viewContent");
  const viewDate = document.getElementById("viewDate");
  const viewEditBtn = document.getElementById("viewEditBtn");
  const viewDeleteBtn = document.getElementById("viewDeleteBtn");

  // ✅ Delete confirmation modal
  const deleteConfirmModal = document.getElementById("deleteConfirmModal");
  const deleteConfirmBtn = document.getElementById("deleteConfirmBtn");
  const deleteCancelBtn = document.getElementById("deleteCancelBtn");
  const deleteModalTitle = document.getElementById("deleteModalTitle");
  const deleteModalMessage = document.getElementById("deleteModalMessage");

  let deleteTargetId = null;
  let deleteMode = "single"; 
  let bulkIds = [];

  let currentNoteColor = "default";
  let editingId = null;

  /* ---------- Toast helper ---------- */
  function showToast(message, type = "info") {
    const container = document.getElementById("toast-container");
    if (!container) return;

    const toast = document.createElement("div");
    toast.className = "toast " + type;
    toast.innerHTML = `
      <span>${message}</span>
      <span class="close-btn">&times;</span>
    `;

    container.appendChild(toast);

    toast.querySelector(".close-btn").addEventListener("click", () => {
      toast.classList.remove("show");
      setTimeout(() => container.removeChild(toast), 300);
    });

    setTimeout(() => toast.classList.add("show"), 100);

    setTimeout(() => {
      if (toast.parentNode) {
        toast.classList.remove("show");
        setTimeout(() => toast.parentNode && toast.parentNode.removeChild(toast), 400);
      }
    }, 3000);
  }

  /* ---------- Helpers ---------- */
  function setNoteColorClass(el, color) {
    if (!el) return;
    Array.from(el.classList).forEach(cls => {
      if (cls.startsWith("note-color-")) el.classList.remove(cls);
    });
    el.classList.add("note-color-" + (color || "default"));
  }

  function resetCreateForm() {
    noteTitle.value = "";
    noteContent.value = "";
    currentNoteColor = "default";
    editingId = null;
    btnSave.textContent = "Save";

    setNoteColorClass(noteExpanded, currentNoteColor);
    if (colorPalette) {
      const opts = colorPalette.querySelectorAll(".color-option");
      opts.forEach(o => o.classList.remove("selected"));
      const def = colorPalette.querySelector('[data-color="default"]');
      if (def) def.classList.add("selected");
    }

    if (charCount) charCount.textContent = "0 / 3000";
  }

  function openCreateModal(note = null) {
    createModal.style.display = "flex";

    if (note) {
      editingId = note.id;
      noteTitle.value = note.title;
      noteContent.value = note.content;
      currentNoteColor = note.color || "default";

      setNoteColorClass(noteExpanded, currentNoteColor);
      if (colorPalette) {
        const opts = colorPalette.querySelectorAll(".color-option");
        opts.forEach(o => o.classList.remove("selected"));
        const sel = colorPalette.querySelector(`[data-color="${currentNoteColor}"]`);
        if (sel) sel.classList.add("selected");
      }

      btnSave.textContent = "Update";
      if (noteTitle.value) noteContent.focus(); else noteTitle.focus();
    } else {
      resetCreateForm();
      noteTitle.focus();
    }

    if (charCount) charCount.textContent = `${noteContent.value.length} / 3000`;
  }

  /* ---------- Character Counter ---------- */
  if (noteContent && charCount) {
    const updateCount = () => {
      charCount.textContent = `${noteContent.value.length} / 3000`;
    };
    noteContent.addEventListener("input", updateCount);
    noteContent.addEventListener("paste", () => {
      setTimeout(updateCount, 0);
    });
    updateCount();
  }

  if (noteContent) {
    noteContent.addEventListener("input", function () {
      autoResizeTextarea(this)
    })
  } 

  /* ---------- Save Note (with title uniqueness check) ---------- */
  function saveNote() {
    let title = noteTitle.value.trim();
    const content = noteContent.value.trim();

    if (!content) {
      showToast("Note content required", "error");
      return;
    }

    // ✅ Ensure title uniqueness by adding numbers if duplicates exist
    const existingTitles = Array.from(document.querySelectorAll(".note-title"))
      .map(el => el.textContent.trim().toLowerCase());

    if (title) {
      let baseTitle = title;
      let counter = 1;
      while (existingTitles.includes(title.toLowerCase())) {
        title = `${baseTitle} (${counter})`;
        counter++;
      }
    } else {
      title = "Untitled";
      let baseTitle = title;
      let counter = 1;
      while (existingTitles.includes(title.toLowerCase())) {
        title = `${baseTitle} (${counter})`;
        counter++;
      }
    }

    const formData = new FormData();
    if (editingId) {
      formData.append("id", editingId);
      formData.append("action", "update");
    } else {
      formData.append("action", "create");
    }
    formData.append("title", title);
    formData.append("content", content);
    formData.append("color", currentNoteColor);
    formData.append("user-current-id", userId ? userId.value : "");

    btnSave.disabled = true;
    btnSave.textContent = editingId ? "Updating..." : "Saving...";

    fetch("notes_api.php", { method: "POST", body: formData })
      .then(resp => resp.json())
      .then(data => {
        btnSave.disabled = false;
        btnSave.textContent = "Save";
        if (data.success) {
          loadNotes();
          createModal.style.display = "none";
          resetCreateForm();
          showToast("Note saved successfully!", "success"); 
        } else {
          showToast("Error saving note: " + (data.message || "unknown"), "error");
        }
      })
      .catch(err => {
        btnSave.disabled = false;
        btnSave.textContent = "Save";
        console.error("Save fetch error:", err);
        showToast("Network error while saving note.", "error");
      });
  }

  function editNote(noteId) {
    fetch("notes_api.php?action=read&id=" + noteId)
      .then(r => r.json())
      .then(data => {
        if (data.success && data.notes) {
          const note = Array.isArray(data.notes) ? data.notes[0] : data.notes;
          if (note) {
            openCreateModal(note);
            viewModal.style.display = "none";
          } else {
            showToast("Note not found", "error");
          }
        } else if (data.success && data.note) {
          openCreateModal(data.note);
          viewModal.style.display = "none";
        } else {
          showToast("Error loading note for editing", "error");
        }
      })
      .catch(err => {
        console.error(err);
        showToast("Failed to fetch note data", "error");
      });
  }

  /* ---------- Events setup ---------- */
  createBtn.addEventListener("click", () => openCreateModal());

  btnClose.addEventListener("click", () => {
    createModal.style.display = "none";
    resetCreateForm();
  });

  window.addEventListener("click", (e) => {
    if (e.target === viewModal) {
      viewModal.style.display = "none";
    }
    if (e.target === deleteConfirmModal) {
      deleteConfirmModal.style.display = "none";
      deleteTargetId = null;
      bulkIds = [];
    }
  });

  if (colorPalette) {
    const options = colorPalette.querySelectorAll(".color-option");
    options.forEach(opt => {
      opt.addEventListener("click", function () {
        options.forEach(o => o.classList.remove("selected"));
        this.classList.add("selected");
        currentNoteColor = this.dataset.color || "default";
        setNoteColorClass(noteExpanded, currentNoteColor);
      });
    });
  }

  btnSave.addEventListener("click", saveNote);

  /* ---------- Select Items Toggle ---------- */
  selectItemsBtn.addEventListener("click", () => {
    selectionMode = !selectionMode;
    if (selectionMode) {
      document.body.classList.add("selection-mode");
      bulkDeleteBtn.style.display = "inline-block";
      selectItemsBtn.querySelector("span").textContent = "Cancel Selection";
    } else {
      document.body.classList.remove("selection-mode");
      bulkDeleteBtn.style.display = "none";
      selectItemsBtn.querySelector("span").textContent = "Select Items";
      document.querySelectorAll(".select-note").forEach(cb => cb.checked = false);
    }
  });

  /* ---------- Load & render ---------- */
  function loadNotes() {
    fetch("notes_api.php?action=read")
      .then(r => r.json())
      .then(data => {
        if (data.success) renderNotes(data.notes || []);
        else console.error("Failed to load notes:", data);
      })
      .catch(err => {
        console.error("Load notes error:", err);
      });
  }

  function renderNotes(notes) {
    notesContainer.innerHTML = "";
    if (!notes || notes.length === 0) return;

    notes.forEach(note => {
      const noteColor = note.color || "default";
      let preview = note.content || "";
      if (preview.length > 120) preview = preview.substring(0, 120) + "...";

      const el = document.createElement("div");
      el.className = `note-card note-color-${noteColor}`;
      el.dataset.id = note.id;
      el.dataset.color = noteColor;

      el.innerHTML = `
        <div class="card-header">
          <h5 class="note-title">${escapeHtml(note.title || "Untitled")}</h5>
          <input type="checkbox" class="select-note" data-id="${note.id}">
        </div>
        <p class="note-text">${escapeHtml(preview)}</p>
        <small class="note-date">Date: ${escapeHtml(note.created_at || "")}</small>
      `;

      el.addEventListener("click", (e) => {
        if (e.target.closest(".select-note")) return;
        openViewModal(note);
      });

      notesContainer.appendChild(el);
    });
  }

  function escapeHtml(str) {
    if (!str && str !== 0) return "";
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function openViewModal(note) {
    viewTitle.textContent = note.title || "Untitled";

viewContent.innerHTML = (note.content || "")
  .replace(/\r\n/g, "<br>")
  .replace(/\n/g, "<br>")
  .replace(/\r/g, "<br>");


    viewDate.textContent = note.created_at || "";

    viewEditBtn.dataset.id = note.id;
    viewDeleteBtn.dataset.id = note.id;

    const vm = document.querySelector(".view-modal");
    vm.className = "modal-content view-modal note-color-" + (note.color || "default");

    viewModal.style.display = "flex";
  }

  viewBackBtn.addEventListener("click", () => {
    viewModal.style.display = "none";
  });

  viewEditBtn.addEventListener("click", () => {
    const noteId = viewEditBtn.dataset.id;
    if (noteId) {
      editNote(noteId);
    } else {
      showToast("Note ID missing", "error");
    }
  });

  /* ---------- Delete (single + bulk) ---------- */
  viewDeleteBtn.addEventListener("click", () => {
    deleteTargetId = viewDeleteBtn.dataset.id;
    deleteMode = "single";
    deleteModalTitle.textContent = "Delete this note?";
    deleteModalMessage.textContent = "This action cannot be undone.";
    deleteConfirmModal.style.display = "flex";
  });

  bulkDeleteBtn.addEventListener("click", () => {
    const selected = Array.from(document.querySelectorAll(".select-note:checked"));
    if (selected.length === 0) {
      showToast("Please select at least one note to delete.", "error");
      return;
    }
    bulkIds = selected.map(cb => cb.dataset.id);
    deleteMode = "bulk";
    deleteModalTitle.textContent = `Delete ${bulkIds.length} notes?`;
    deleteModalMessage.textContent = "This action cannot be undone.";
    deleteConfirmModal.style.display = "flex";
  });

  deleteCancelBtn.addEventListener("click", () => {
    deleteConfirmModal.style.display = "none";
    deleteTargetId = null;
    bulkIds = [];
  });

  deleteConfirmBtn.addEventListener("click", () => {
    if (deleteMode === "single" && deleteTargetId) {
      const formData = new FormData();
      formData.append("id", deleteTargetId);
      formData.append("action", "delete");

      fetch("notes_api.php", { method: "POST", body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            viewModal.style.display = "none";
            showToast("Note deleted successfully.", "success");
            loadNotes();
          } else {
            showToast("Error deleting note.", "error");
          }
        })
        .catch(err => {
          console.error("Delete fetch error:", err);
          showToast("Network error while deleting.", "error");
        });
    }

    if (deleteMode === "bulk" && bulkIds.length > 0) {
      const formData = new FormData();
      formData.append("ids", JSON.stringify(bulkIds));
      formData.append("action", "bulk_delete");

      fetch("notes_api.php", { method: "POST", body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            showToast("Selected notes deleted successfully!", "success");
            loadNotes();
            selectionMode = false;
            document.body.classList.remove("selection-mode");
            bulkDeleteBtn.style.display = "none";
            selectItemsBtn.querySelector("span").textContent = "Select Items";
          } else {
            showToast("Error deleting notes.", "error");
          }
        })
        .catch(err => {
          console.error("Bulk delete error:", err);
          showToast("Network error while deleting notes.", "error");
        });
    }

    deleteConfirmModal.style.display = "none";
    deleteTargetId = null;
    bulkIds = [];
  });

  /* ---------- Search ---------- */
  const searchInput = document.getElementById("searchNotes");
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      const term = this.value.trim().toLowerCase();

      if (!term) {
        document.querySelectorAll(".note-card").forEach(card => {
          card.style.display = "";
        });
        return;
      }

      document.querySelectorAll(".note-card").forEach(card => {
        const titleEl = card.querySelector(".note-title");
        const title = titleEl ? titleEl.textContent.trim().toLowerCase() : "";
        const matches = title.includes(term) || title === term;
        card.style.display = matches ? "" : "none";
      });
    });
  }

  loadNotes();
});