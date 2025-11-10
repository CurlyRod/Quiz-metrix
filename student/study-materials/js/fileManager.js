document.addEventListener("DOMContentLoaded", () => {
  // Initialize variables

  const viewToggles = document.querySelectorAll(".view-toggle[data-view]")
  const uploadForm = document.getElementById("uploadForm")
  const uploadButton = document.getElementById("uploadButton")

  const newFolderForm = document.getElementById("newFolderForm")
  const createFolderButton = document.getElementById("createFolderButton")
  const renameFolderForm = document.getElementById("renameFolderForm")
  const renameFolderButton = document.getElementById("renameFolderButton")
  const confirmActionButton = document.getElementById("confirmActionButton")
  const searchInput = document.querySelector("#searchForm input")
  const fileListElement = document.getElementById("fileList")
  const closeButtons = document.querySelectorAll("#uploadModal .btn-close, #uploadModal .btn-secondary")

  const progressWrapper = document.getElementById("uploadProgressWrapper")
  const progressText = document.getElementById("progressPercent")
  const circle = document.querySelector(".circular-progress circle.progress")
  const circumference = 251.2
  const checked = Array.from(document.querySelectorAll(".select-file:checked"));
  const selectedIds = checked.map(cb => cb.dataset.id);
  const fileCards = document.querySelectorAll(".file-card");
  
  const selectBtn = document.getElementById("selectItemsBtn")
  const bulkDeleteBtn = document.getElementById("bulkDeleteBtn")
  const selectAllBtn = document.getElementById("selectAllBtn");
  // Initialize Bootstrap modals
  const uploadModal = document.getElementById("uploadModal")
    ? new window.bootstrap.Modal(document.getElementById("uploadModal"))
    : null
  const newFolderModal = document.getElementById("newFolderModal")
    ? new window.bootstrap.Modal(document.getElementById("newFolderModal"))
    : null
  const renameFolderModal = document.getElementById("renameFolderModal")
    ? new window.bootstrap.Modal(document.getElementById("renameFolderModal"))
    : null
  const previewModal = document.getElementById("previewModal")
    ? new window.bootstrap.Modal(document.getElementById("previewModal"))
    : null
  const confirmationModal = document.getElementById("confirmationModal")
    ? new window.bootstrap.Modal(document.getElementById("confirmationModal"))
    : null

  const fileInputWrapper = document.getElementById("fileInputWrapper")
  const fileInput = document.getElementById("fileUpload")
  const fileList = document.getElementById("fileList")
  let selectedFiles = []

  function formatFileSize(bytes) {
    if (bytes === 0) return "0 Bytes"
    const k = 1024
    const sizes = ["Bytes", "KB", "MB", "GB"]
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round(bytes / Math.pow(k, i)) + " " + sizes[i]
  }

  function getUniqueFileName(name) {
  let baseName = name.substring(0, name.lastIndexOf(".")) || name
  let ext = name.includes(".") ? "." + name.split(".").pop() : ""
  let counter = 1
  let newName = name

  while (selectedFiles.some(f => f.name === newName)) {
    newName = `${baseName} (${counter})${ext}`
    counter++
  }
  return newName
}
  // Render file list
  function renderFileList() {
    if (!fileList) return
    fileList.innerHTML = ""
    selectedFiles.forEach((file, index) => {
      const li = document.createElement("li")
      li.className = "list-group-item d-flex justify-content-between align-items-center"
      li.textContent = file.name

      const removeBtn = document.createElement("button")
      removeBtn.className = "bx bx-x transparent btn btn-sm"
      removeBtn.onclick = () => {
        selectedFiles.splice(index, 1)
        renderFileList()
      }

      li.appendChild(removeBtn)
      fileList.appendChild(li)
    })
  }

  //search
if (searchInput) {
  searchInput.addEventListener("input", () => {
    if (e.key === "Enter") {
      e.preventDefault(); // prevent form submission (if inside form)
      const query = searchInput.value.toLowerCase().trim();
      let matchCount = 0;

      //Filter Folders
      document.querySelectorAll(".folder-card").forEach((card) => {
        const name = card.querySelector(".card-title")?.textContent.toLowerCase() || "";
        const match = name.includes(query);
        card.closest(".col").style.display = match ? "block" : "none";
        if (match) matchCount++;
      });

      //(Grid view)
      document.querySelectorAll(".file-card").forEach((card) => {
        const name = card.querySelector(".card-title")?.textContent.toLowerCase() || "";
        const match = name.includes(query);
        card.closest(".col").style.display = match ? "block" : "none";
        if (match) matchCount++;
      });

      // (List view)
      document.querySelectorAll(".list-view tbody tr").forEach((row) => {
        const name = row.querySelector("td span")?.textContent.toLowerCase() || "";
        const match = name.includes(query);
        row.style.display = match ? "" : "none";
        if (match) matchCount++;
      });
    }
    // Show "No results" message if nothing matches
    const noResults = document.getElementById("noResultsMessage")
    if (noResults) {
      if (matchCount === 0 && query !== "") {
        noResults.classList.remove("d-none")
      } else {
        noResults.classList.add("d-none")
      }
    }
  })
}

//pagination
const rowsPerPage = 10; // Change to desired number
let currentPage = 1;

function paginateList() {
  const rows = Array.from(document.querySelectorAll(".list-view tbody tr"));
  const totalRows = rows.length;
  const totalPages = Math.ceil(totalRows / rowsPerPage);

  rows.forEach((row, index) => {
    row.style.display =
      index >= (currentPage - 1) * rowsPerPage && index < currentPage * rowsPerPage
        ? ""
        : "none";
  });

  const pageInfoEl = document.getElementById("pageInfo");
  if (pageInfoEl) {
    pageInfoEl.textContent = `Page ${currentPage} of ${totalPages}`;
  }

  const prevBtn = document.getElementById("prevPage");
  const nextBtn = document.getElementById("nextPage");

  if (prevBtn && nextBtn) {
    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages || totalPages === 0;
  }
}

const prevBtn = document.getElementById("prevPage");
const nextBtn = document.getElementById("nextPage");

if (prevBtn && nextBtn) {
  prevBtn.addEventListener("click", () => {
    if (currentPage > 1) {
      currentPage--;
      paginateList();
    }
  });

  nextBtn.addEventListener("click", () => {
    const rows = document.querySelectorAll(".list-view tbody tr");
    const totalPages = Math.ceil(rows.length / rowsPerPage);
    if (currentPage < totalPages) {
      currentPage++;
      paginateList();
    }
  });

  // Run pagination initially
  paginateList();
}


  // Reset function
  function resetFileList() {
    selectedFiles = []
    fileListElement.innerHTML = ""
    uploadForm.reset()
  }

  closeButtons.forEach((btn) => {
    btn.addEventListener("click", resetFileList)
  })

  // File explorer select
  const MAX_FILE_SIZE_MB = 5;
const MAX_FILE_SIZE = MAX_FILE_SIZE_MB * 1024 * 1024; // bytes

if (fileInput) {
  fileInput.addEventListener("change", (e) => {
    Array.from(e.target.files).forEach((file) => {
      const ext = file.name.split(".").pop().toLowerCase();
      if (["pdf", "docx", "txt"].includes(ext)) {
        if (file.size > MAX_FILE_SIZE) {
          showToast(
            `❌ ${file.name} exceeds ${MAX_FILE_SIZE_MB} MB limit.`,
            "danger"
          );
          return; // skip this file
        }

        const uniqueName = getUniqueFileName(file.name);
        const renamedFile = new File([file], uniqueName, { type: file.type });
        selectedFiles.push(renamedFile);
      } else {
        showToast(`Invalid file type: ${file.name}`, "danger");
      }
    });
    renderFileList();
  });
}

  // Drag & Drop with 5 MB Limit 
if (fileInputWrapper) {
  fileInputWrapper.addEventListener("dragover", (e) => {
    e.preventDefault();
    fileInputWrapper.classList.add("bg-primary", "text-white");
  });

  fileInputWrapper.addEventListener("dragleave", () => {
    fileInputWrapper.classList.remove("bg-primary", "text-white");
  });

  fileInputWrapper.addEventListener("drop", (e) => {
    e.preventDefault();
    fileInputWrapper.classList.remove("bg-primary", "text-white");

    const droppedFiles = Array.from(e.dataTransfer.files);
    let validFiles = [];

    droppedFiles.forEach((file) => {
      const ext = file.name.split(".").pop().toLowerCase();

      if (!["pdf", "docx", "txt"].includes(ext)) {
        showToast(`❌ Invalid file type: ${file.name}`, "danger");
        return;
      }

      if (file.size > MAX_FILE_SIZE) {
        showToast(`❌ ${file.name} exceeds ${MAX_FILE_SIZE_MB} MB limit.`, "danger");
        return;
      }

      validFiles.push(file);
    });

    if (validFiles.length > 0) {
      validFiles.forEach((file) => {
        const uniqueName = getUniqueFileName(file.name);
        const renamedFile = new File([file], uniqueName, { type: file.type });
        selectedFiles.push(renamedFile);
      });
      renderFileList();
    }
  });
}

  // Upload all selected files
  if (uploadButton) {
    uploadButton.addEventListener("click", () => {
      if (selectedFiles.length === 0 && (!fileInput || fileInput.files.length === 0)) {
        showToast("Please select at least one file", "warning")
        return
      }

      const formData = new FormData()
      const folderInput = document.querySelector("input[name='folder_id']")
      if (folderInput) {
        formData.append("folder_id", folderInput.value)
      }

      // Append actual files
      const filesToUpload = selectedFiles.length > 0 ? selectedFiles : Array.from(fileInput.files)
      filesToUpload.forEach((file) => formData.append("files[]", file))

      const fileNameDisplay = document.getElementById("uploadFileName")
      const fileSizeDisplay = document.getElementById("uploadFileSize")

      let totalSize = 0
      filesToUpload.forEach((file) => (totalSize += file.size))

     if (fileNameDisplay && fileSizeDisplay) {
  if (filesToUpload.length === 1) {
    fileNameDisplay.textContent = filesToUpload[0].name
  } else {
    fileNameDisplay.textContent = `(${filesToUpload.length}) files`
  }
  fileSizeDisplay.textContent = `(${formatFileSize(totalSize)})`
}


      // Reset circle
      if (circle) circle.style.strokeDashoffset = circumference
      if (progressText) progressText.textContent = "0%"
      if (progressWrapper) progressWrapper.classList.remove("d-none")

      let currentPercent = 0
      let targetPercent = 0
      let animationFrameId = null

      function animateProgress() {
        if (currentPercent < targetPercent) {
          // Smoother increment with easing
          const diff = targetPercent - currentPercent
          const increment = Math.max(0.3, diff * 0.08)
          currentPercent = Math.min(currentPercent + increment, targetPercent)

          // Update progress circle - let CSS transition handle the smoothness
          const offset = circumference - (currentPercent / 100) * circumference
          if (circle) circle.style.strokeDashoffset = offset

          // Update percentage text
          if (progressText) progressText.textContent = Math.round(currentPercent) + "%"

          animationFrameId = requestAnimationFrame(animateProgress)
        } else {
          // Reached target, update to exact value
          const offset = circumference - (targetPercent / 100) * circumference
          if (circle) circle.style.strokeDashoffset = offset
          if (progressText) progressText.textContent = Math.round(targetPercent) + "%"
        }
      }

      const xhr = new XMLHttpRequest()

      xhr.upload.addEventListener("progress", (e) =>  {
        if (e.lengthComputable) {
          targetPercent = Math.round((e.loaded / e.total) * 100)

          // Start animation if not already running
          if (!animationFrameId) {
            animationFrameId = requestAnimationFrame(animateProgress)
          }
        }
      })

      xhr.addEventListener("load", () => {
        if (animationFrameId) {
          cancelAnimationFrame(animationFrameId)
          animationFrameId = null
        }

        if (xhr.status === 200) {
          try {
            const response = JSON.parse(xhr.responseText)
            if (response.success) {
              targetPercent = 100
              currentPercent = Math.max(currentPercent, 95)

              function finalAnimation() {
                if (currentPercent < 100) {
                  const diff = 100 - currentPercent
                  const increment = Math.max(0.5, diff * 0.15)
                  currentPercent = Math.min(currentPercent + increment, 100)

                  const offset = circumference - (currentPercent / 100) * circumference
                  if (circle) circle.style.strokeDashoffset = offset
                  if (progressText) progressText.textContent = Math.round(currentPercent) + "%"
                  requestAnimationFrame(finalAnimation)
                } else {
                  // Reached 100%
                  if (circle) circle.style.strokeDashoffset = 0
                  if (progressText) progressText.textContent = "100%"
            if (Array.isArray(response.uploaded) && response.uploaded.length > 0) {
                        response.uploaded.forEach(file => {
                         const fileName = file.name; // e.g. "document(1).txt"
                           if (typeof addFileCard === "function") {
                           addFileCard(fileName); // your existing function to show new file
                }
              });
            }
                  showToast("Files uploaded successfully", "success")

                  setTimeout(() => {
                    if (progressWrapper) progressWrapper.classList.add("d-none")
                    uploadForm.reset()
                    selectedFiles = []
                    renderFileList()
                    if (uploadModal) uploadModal.hide()
                    window.location.reload()
                  }, 1500)
                }
              }

              finalAnimation()
            } else {
              showToast(response.message || "Error uploading files", "danger")
              if (progressWrapper) progressWrapper.classList.add("d-none")
              uploadForm.reset()
              selectedFiles = []
              renderFileList()
            }
          } catch (e) {
            console.error("Response error:", e)
            showToast("Error processing server response", "danger")
            if (progressWrapper) progressWrapper.classList.add("d-none")
          }
        } else {
          showToast("Error uploading files", "danger")
          if (progressWrapper) progressWrapper.classList.add("d-none")
        }
      })

      xhr.addEventListener("error", () => {
        if (animationFrameId) {
          cancelAnimationFrame(animationFrameId)
        }
        showToast("Network error during upload", "danger")
        if (progressWrapper) progressWrapper.classList.add("d-none")
      })

      xhr.open("POST", "api/upload.php", true)
      xhr.send(formData)
    })
  }

  // Initialize SortableJS for drag and drop
  const folderContainers = document.querySelectorAll(".folders-container")
  const fileContainers = document.querySelectorAll(".files-container");
  const listTableBody = document.querySelector(".list-view tbody");

if (typeof window.Sortable !== "undefined") {
  fileContainers.forEach((container) => {
    if (container) {
      new window.Sortable(container, {
        animation: 200,
        ghostClass: "sortable-ghost",
        chosenClass: "sortable-chosen",
        handle: ".file-card",   // bawat file card puwedeng i-drag
        swapThreshold: 0.65,
        delay: 400,             // ⏱️ long-press delay (mobile)
        delayOnTouchOnly: true, // long-press lang sa touch devices
        onEnd: () => {
          const files = Array.from(container.querySelectorAll(".file-card")).map((card, index) => ({
            id: card.getAttribute("data-id"),
            position: index,
          }));

          // ✅ Optional: update new order sa database
          updatePositions("file", files);
        },
      });
    }
  });
}
// ===== LIST VIEW (allow checkbox clicks) =====
if (listTableBody) {
  new window.Sortable(listTableBody, {
    animation: 200,
    ghostClass: "sortable-ghost",
    chosenClass: "sortable-chosen",
    swapThreshold: 0.65,
    delay: 400,
    delayOnTouchOnly: true,

    filter: ".file-checkbox, .file-checkbox *",

    onFilter: (evt) => {
      const checkbox = evt.target.closest(".file-checkbox");
      if (checkbox) {
        evt.preventDefault();
        evt.stopPropagation();
      }
    },
    handle: "tr", 

    onEnd: () => {
      const files = Array.from(listTableBody.querySelectorAll("tr")).map((row, index) => ({
        id: row.getAttribute("data-id"),
        position: index,
      }));
      updatePositions("file", files);
    },
  });
}




  // View toggle (Grid/List)
 viewToggles.forEach((toggle) => {
  toggle.addEventListener("click", function () {
    const view = this.getAttribute("data-view")

    // Save preference
    localStorage.setItem("preferredView", view)

    applyView(view)
  })
})

// Function to apply view
function applyView(view) {
  const gridView = document.querySelector(".grid-view")
  const listView = document.querySelector(".list-view")

  // Remove active class from all toggles
  viewToggles.forEach((t) => t.classList.remove("active"))

  if (view === "grid") {
    if (gridView) gridView.classList.remove("d-none")
    if (listView) listView.classList.add("d-none")
    document.querySelector(`.view-toggle[data-view="grid"]`)?.classList.add("active")
  } else if (view === "list") {
    if (gridView) gridView.classList.add("d-none")
    if (listView) listView.classList.remove("d-none")
    document.querySelector(`.view-toggle[data-view="list"]`)?.classList.add("active")
  }

  // Save preference
  localStorage.setItem("preferredView", view)
}

// Load saved view preference (default grid)
const savedView = localStorage.getItem("preferredView") || "grid"
applyView(savedView)

// Event listeners for buttons
viewToggles.forEach((toggle) => {
  toggle.addEventListener("click", function () {
    const view = this.getAttribute("data-view")
    applyView(view)
  })
})

  // Sorting and Filtering Script
  // Save original order of files (grid + list) for reset
  const originalGrid = Array.from(document.querySelectorAll(".file-card")).map((el) => el.closest(".col"))
  const originalList = Array.from(document.querySelectorAll(".list-view tbody tr"))

  // Store original button labels
  const defaultLabels = {
    sortDateDropdown: '<i class="bx bx-calendar me-1"></i> Date',
    sortTypeDropdown: '<i class="bx bx-file me-1"></i> File Type',
    sortNameDropdown: '<i class="bx bx-sort-a-z me-1"></i> Name',
  }

  document.querySelectorAll(".dropdown-menu .dropdown-item").forEach((item) => {
    item.addEventListener("click", function (e) {
      e.preventDefault()

      const targetId = this.getAttribute("data-target")
      const dropdownButton = document.getElementById(targetId)
      const filterValue = this.getAttribute("data-filter")

      // RESET HANDLING
      if (filterValue === "all") {
        // Reset button text to default
        if (dropdownButton && defaultLabels[targetId]) {
          dropdownButton.innerHTML = defaultLabels[targetId]
        }

        // Reset files in Grid
        const container = document.querySelector(".files-container")
        originalGrid.forEach((el) => {
          el.style.display = "block"
          container.appendChild(el)
        })

        // Reset files in List
        const tbody = document.querySelector(".list-view tbody")
        originalList.forEach((el) => {
          el.style.display = ""
          tbody.appendChild(el)
        })

        return
      }

      // NORMAL FILTER / SORT
      if (dropdownButton) {
        const iconHTML = dropdownButton.querySelector("i") ? dropdownButton.querySelector("i").outerHTML + " " : ""
        dropdownButton.innerHTML = iconHTML + this.textContent
      }

      const filesGrid = document.querySelectorAll(".file-card")
      const filesList = document.querySelectorAll(".list-view tbody tr")

      // FILE TYPE FILTER
      if (targetId === "sortTypeDropdown") {
        filesGrid.forEach((file) => {
          file.closest(".col").style.display = file
            .querySelector(".preview-placeholder")
            ?.classList.contains(filterValue)
            ? "block"
            : "none"
        })

        filesList.forEach((row) => {
          row.style.display = row.getAttribute("data-type") === filterValue ? "" : "none"
        })
      }

      // NAME SORT
      if (targetId === "sortNameDropdown") {
        const sortedGrid = Array.from(filesGrid).sort((a, b) => {
          const nameA = a.querySelector(".card-title").textContent.toLowerCase()
          const nameB = b.querySelector(".card-title").textContent.toLowerCase()
          return filterValue === "az"
            ? nameA.localeCompare(nameB, undefined, { numeric: true })
            : filterValue === "za"
              ? nameB.localeCompare(nameA, undefined, { numeric: true })
              : 0
        })

        const container = document.querySelector(".files-container")
        sortedGrid.forEach((el) => container.appendChild(el.closest(".col")))

        const sortedList = Array.from(filesList).sort((a, b) => {
          const nameA = a.querySelector("td span").textContent.toLowerCase()
          const nameB = b.querySelector("td span").textContent.toLowerCase()
          return filterValue === "az"
            ? nameA.localeCompare(nameB, undefined, { numeric: true })
            : filterValue === "za"
              ? nameB.localeCompare(nameA, undefined, { numeric: true })
              : 0
        })

        const tbody = document.querySelector(".list-view tbody")
        sortedList.forEach((el) => tbody.appendChild(el))
      }

      // DATE SORT
      if (targetId === "sortDateDropdown") {
        const sortedGrid = Array.from(filesGrid).sort((a, b) => {
          const dateA = new Date(a.querySelector(".card-text small").dataset.upload)
          const dateB = new Date(b.querySelector(".card-text small").dataset.upload)
          return filterValue === "newest" ? dateB - dateA : filterValue === "oldest" ? dateA - dateB : 0
        })

        const container = document.querySelector(".files-container")
        sortedGrid.forEach((el) => container.appendChild(el.closest(".col")))

        const sortedList = Array.from(filesList).sort((a, b) => {
          const dateA = new Date(a.querySelector("td[data-upload]").dataset.upload)
          const dateB = new Date(b.querySelector("td[data-upload]").dataset.upload)
          return filterValue === "newest" ? dateB - dateA : filterValue === "oldest" ? dateA - dateB : 0
        })

        const tbody = document.querySelector(".list-view tbody")
        sortedList.forEach((el) => tbody.appendChild(el))
      }
    })
  })

  // Create new folder
  if (createFolderButton) {
    createFolderButton.addEventListener("click", () => {
      const folderNameInput = document.getElementById("folderName")
      if (!folderNameInput) {
        showToast("Form error: Folder name input not found", "danger")
        return
      }

      const folderName = folderNameInput.value.trim()

      if (!folderName) {
        showToast("Please enter a folder name", "warning")
        return
      }

      const formData = new FormData(newFolderForm)

      fetch("api/create-folder.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`)
          }
          return response.json()
        })
        .then((data) => {
          if (data.success) {
            showToast("Folder created successfully", "success")
            setTimeout(() => {
              window.location.reload()
            }, 2000)
          } else {
            showToast(data.message || "Error creating folder", "danger")
          }
        })
        .catch((error) => {
          showToast("Error creating folder", "danger")
          console.error("Error:", error)
        })
        .finally(() => {
          newFolderForm.reset()
          if (newFolderModal) {
            newFolderModal.hide()
          }
        })
    })
  }

  // Rename folder
  document.querySelectorAll(".rename-folder").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()

      const folderId = this.getAttribute("data-id")
      const folderName = this.getAttribute("data-name")

      const renameFolderIdInput = document.getElementById("renameFolderId")
      const newFolderNameInput = document.getElementById("newFolderName")

      if (renameFolderIdInput && newFolderNameInput) {
        renameFolderIdInput.value = folderId
        newFolderNameInput.value = folderName
      }

      if (renameFolderModal) {
        renameFolderModal.show()
      }
    })
  })

  if (renameFolderButton) {
    renameFolderButton.addEventListener("click", () => {
      const newFolderNameInput = document.getElementById("newFolderName")
      if (!newFolderNameInput) {
        showToast("Form error: New folder name input not found", "danger")
        return
      }

      const folderName = newFolderNameInput.value.trim()

      if (!folderName) {
        showToast("Please enter a folder name", "warning")
        return
      }

      const formData = new FormData(renameFolderForm)

      fetch("api/rename-folder.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`)
          }
          return response.json()
        })
        .then((data) => {
          if (data.success) {
            showToast("Folder renamed successfully", "success")
            setTimeout(() => {
              window.location.reload()
            }, 2000)
          } else {
            showToast(data.message || "Error renaming folder", "danger")
          }
        })
        .catch((error) => {
          showToast("Error renaming folder", "danger")
          console.error("Error:", error)
        })
        .finally(() => {
          renameFolderForm.reset()
          if (renameFolderModal) {
            renameFolderModal.hide()
          }
        })
    })
  }

  // Delete folder
  document.querySelectorAll(".delete-folder").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()

      const folderId = this.getAttribute("data-id")

      const confirmationMessageEl = document.getElementById("confirmationMessage")
      if (confirmationMessageEl) {
        confirmationMessageEl.textContent =
          "Are you sure you want to delete this folder? All files inside will be moved to the parent folder."
      }

      if (confirmActionButton) {
        confirmActionButton.setAttribute("data-action", "delete-folder")
        confirmActionButton.setAttribute("data-id", folderId)
      }

      if (confirmationModal) {
        confirmationModal.show()
      }
    })
  })

  // Delete file
  document.querySelectorAll(".delete-file").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()

      const fileId = this.getAttribute("data-id")

      const confirmationMessageEl = document.getElementById("confirmationMessage")
      if (confirmationMessageEl) {
        confirmationMessageEl.textContent = "Are you sure you want to delete this file?"
      }

      if (confirmActionButton) {
        confirmActionButton.setAttribute("data-action", "delete-file")
        confirmActionButton.setAttribute("data-id", fileId)
      }

      if (confirmationModal) {
        confirmationModal.show()
      }
    })
  })

  // Confirmation action
if (confirmActionButton) {
  confirmActionButton.addEventListener("click", function () {
    const action = this.getAttribute("data-action");
    const id = this.getAttribute("data-id");
    let url, successMessage;


    if (action === "delete-folder") {
      url = "api/delete-folder.php";
      successMessage = "Folder deleted successfully";
      setTimeout(() => {
        window.location.reload();
      }, 3000);

      fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + id,
      })
        .then((response) => {
          if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
          return response.json();
        })
        .then((data) => {
          if (data.success) {
            showToast(successMessage, "success");
            window.location.reload();
          } else {
            showToast(data.message || "Error deleting folder", "danger");
          }
        })
        .catch((error) => {
          showToast("Error performing action", "danger");
          console.error("Error:", error);
        })
        .finally(() => {
          if (confirmationModal) confirmationModal.hide();
        });

      return;
    }

   if (action === "delete-file") {
  url = "api/delete-file.php";
  successMessage = "File deleted successfully";

  fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "id=" + id,
  })
    .then((response) => {
      if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        showToast(successMessage, "success", id);

        // Remove from Grid View
        const gridCard = document.querySelector(`.file-card[data-id="${id}"]`);
        if (gridCard) gridCard.closest(".col")?.remove();

        // Remove from List View
        const listRow = document.querySelector(`.list-view tbody tr[data-id="${id}"]`);
        if (listRow) listRow.remove();

        // ✅ Check if no files left, then auto-refresh
        const remainingFiles =
          document.querySelectorAll(".file-card").length +
          document.querySelectorAll(".list-view tbody tr").length;

        if (remainingFiles === 0) {
          setTimeout(() => {
            window.location.reload();
          }, 800);
        }
      } else {
        showToast(data.message || "Error deleting file", "danger");
      }
    })
    .catch((error) => {
      showToast("Error performing action", "danger");
      console.error("Error:", error);
    })
    .finally(() => {
      if (confirmationModal) confirmationModal.hide();
    });

  return;
}

   if (action === "bulk-delete") {
  const checkedBoxes = Array.from(document.querySelectorAll(".select-file:checked"));
  const selectedIds = checkedBoxes.map(cb => cb.dataset.id);

  if (selectedIds.length === 0) {
    showToast("No files selected", "warning");
    if (confirmationModal) confirmationModal.hide();
    return;
  }

  const params = new URLSearchParams();
  selectedIds.forEach((id) => params.append("ids[]", id));

  fetch("api/bulk-delete.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: params.toString(),
  })
    .then((response) => {
      if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        selectedIds.forEach((id) => {
          document.querySelector(`.file-card[data-id="${id}"]`)?.closest(".col")?.remove();
          document.querySelector(`.list-view tbody tr[data-id="${id}"]`)?.remove();
        });

        showToast(`Deleted ${selectedIds.length} file(s) successfully`, "success");
        resetSelectionMode();

        // ✅ Auto-refresh if no files remain
        const remainingFiles =
          document.querySelectorAll(".file-card").length +
          document.querySelectorAll(".list-view tbody tr").length;

        if (remainingFiles === 0) {
          setTimeout(() => {
            window.location.reload();
          }, 800);
        }
      } else {
        showToast(data.message || "Error deleting files", "danger");
      }
    })
    .catch((error) => {
      showToast("Error performing bulk delete", "danger");
      console.error("Error:", error);
    })
    .finally(() => {
      if (confirmationModal) confirmationModal.hide();
    });

  return;
}

    if (confirmationModal) {
      confirmationModal.hide();
    }
  });
}


  // Preview file
document.querySelectorAll(".preview-file").forEach((button) => {
  button.addEventListener("click", function (e) {
    e.preventDefault()

    const fileId = this.getAttribute("data-id")
    const fileType = this.getAttribute("data-type")
    const filePath = this.getAttribute("data-path")
    const fileName = this.getAttribute("data-name") // Get the original file name

    // For PDF files, open in a new tab instead of modal
    if (fileType === "pdf") {
  // Open PDF in new tab
  const pdfWindow = window.open(`uploads/${filePath}`, "_blank");
  
  if (pdfWindow && fileName) {
    setTimeout(() => {
      try {
        pdfWindow.document.title = fileName;
      } catch (e) {
        // Security restrictions may prevent this
      }
    }, 1000);
  }
  return;
}

    const previewContent = document.getElementById("previewContent")
    const previewDownloadBtn = document.getElementById("previewDownloadBtn")
    const previewTitle = document.getElementById("previewTitle") // If you have a title element

    if (!previewContent || !previewDownloadBtn) {
      console.error("Preview elements not found")
      return
    }

    // Clear previous content
    previewContent.innerHTML = ""

    // Set download link with original filename
    previewDownloadBtn.href = "api/download.php?id=" + fileId
    previewDownloadBtn.download = fileName // Set the download filename to original name

    if (previewTitle && fileName) {
      previewTitle.textContent = "Preview: " + fileName;
    }

    // Set download link with original filename
    previewDownloadBtn.href = "api/download.php?id=" + fileId;
    if (fileName) {
      previewDownloadBtn.download = fileName; // Set the download filename to original name
    }

      // Load preview based on file type
      if (fileType === "txt") {
        fetch("api/preview.php?id=" + fileId)
          .then((response) => {
            if (!response.ok) {
              throw new Error(`HTTP error! Status: ${response.status}`)
            }
            return response.text()
          })
          .then((text) => {
            previewContent.innerHTML = `<pre class="p-3">${text}</pre>`
          })
          .catch((error) => {
            previewContent.innerHTML = '<div class="alert alert-danger">Error loading file preview</div>'
            console.error("Error:", error)
          })
      } else if (fileType === "docx") {
        previewContent.innerHTML =
          '<div class="alert alert-info">Preview not available for DOCX files. Please download the file to view it.</div>'
      } else {
        previewContent.innerHTML = '<div class="alert alert-warning">Preview not available for this file type</div>'
      }

      if (previewModal) {
        previewModal.show()
      }
    })
  })

  // Update positions after drag and drop
  function updatePositions(type, positions) {
    fetch("api/update-positions.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        type: type,
        positions: positions,
      }),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`)
        }
        return response.json()
      })
      .then((data) => {
        if (!data.success) {
          showToast("Error updating positions", "warning")
        }
      })
      .catch((error) => {
        console.error("Error:", error)
      })
  }

  //bulk delete

/* ---------- Toggle Select Items (for wrapper + input) ---------- */
if (selectBtn) {
  selectBtn.addEventListener("click", () => {
    const checkboxWrappers = document.querySelectorAll(".file-checkbox");
    const checkboxes = document.querySelectorAll(".select-file");
    const isActive = selectBtn.classList.contains("active");

    if (isActive) {
    
      checkboxes.forEach(cb => {
        cb.checked = false;
        cb.disabled = false; 
      });
      checkboxWrappers.forEach(div => div.style.display = "none");
      bulkDeleteBtn.classList.add("d-none");
      selectAllBtn.classList.add("d-none");
      selectAllBtn.classList.remove("active");
      selectAllBtn.innerHTML = '<i class="bx bx-select-multiple"></i> Select All';
      selectBtn.classList.remove("active");
      selectBtn.innerHTML = '<i class="bx bx-check-circle"></i> Select Files';
    } else {
     
      checkboxes.forEach(cb => {
        cb.checked = false;
        cb.disabled = false; 
      });
      checkboxWrappers.forEach(div => div.style.display = "inline-block");
      bulkDeleteBtn.classList.remove("d-none");
      selectAllBtn.classList.remove("d-none");
      selectBtn.classList.add("active");
      selectBtn.innerHTML = '<i class="bx bx-x-circle"></i> Cancel';
    }
  });
}

if (selectAllBtn) {
  selectAllBtn.addEventListener("click", () => {
    const checkboxes = Array.from(document.querySelectorAll(".select-file"));
    const allChecked = checkboxes.length > 0 && checkboxes.every(cb => cb.checked);

    if (allChecked) {
      checkboxes.forEach(cb => {
        cb.checked = false;
        cb.disabled = false;
      });
      selectAllBtn.classList.remove("active");
      selectAllBtn.innerHTML = '<i class="bx bx-select-multiple"></i> Select All';

      // Hide bulk delete button if nothing is selected
      if (bulkDeleteBtn) bulkDeleteBtn.classList.add("d-none");
    } else {
      // 🔹 SELECT ALL: check + disable (lock) each checkbox
      checkboxes.forEach(cb => {
        cb.checked = true;
        cb.disabled = true; // lock so user cannot uncheck
      });
      selectAllBtn.classList.add("active");
      selectAllBtn.innerHTML = '<i class="bx bx-x"></i> Unselect All';

      // Show bulk delete button
      if (bulkDeleteBtn) bulkDeleteBtn.classList.remove("d-none");
    }
  });
}


document.querySelectorAll(".select-file").forEach(cb => {
  cb.addEventListener("change", e => {
    if (cb.disabled) {
      // prevent changing locked checkboxes
      e.preventDefault();
      cb.checked = true;
      return;
    }

    // Update Select All button UI normally if not locked
    const checkboxes = Array.from(document.querySelectorAll(".select-file"));
    const allChecked = checkboxes.every(c => c.checked);
    const anyChecked = checkboxes.some(c => c.checked);

    if (selectAllBtn) {
      if (allChecked) {
        selectAllBtn.classList.add("active");
        selectAllBtn.innerHTML = '<i class="bx bx-x"></i> Unselect All';
      } else {
        selectAllBtn.classList.remove("active");
        selectAllBtn.innerHTML = '<i class="bx bx-select-multiple"></i> Select All';
      }
    }

    if (bulkDeleteBtn) {
      bulkDeleteBtn.classList.toggle("d-none", !anyChecked);
    }
  });
});



//Bulk delete 
if (bulkDeleteBtn) {
  bulkDeleteBtn.addEventListener("click", () => {
    // ✅ Get currently checked boxes
    const checked = Array.from(document.querySelectorAll(".select-file:checked"));
    const selectedIds = checked.map(cb => cb.dataset.id);

    if (selectedIds.length === 0) {
      showToast("No files selected", "warning");
      return;
    }

    // Update modal confirmation message
    const confirmationMessageEl = document.getElementById("confirmationMessage");
    if (confirmationMessageEl) {
      confirmationMessageEl.textContent = `Are you sure you want to delete ${selectedIds.length} selected file(s)?`;
    }

    // Set data for confirm button
    if (confirmActionButton) {
      confirmActionButton.setAttribute("data-action", "bulk-delete");
      confirmActionButton.setAttribute("data-ids", JSON.stringify(selectedIds));
    }

    // Show confirmation modal
    if (confirmationModal) confirmationModal.show();
  });
}


  // Show toast notification
  function showToast(message, type, fileId = null) {
    const toastContainer = document.querySelector(".toast-container")
    if (!toastContainer) {
      console.error("Toast container not found")
      return
    }

    const toastId = "toast-" + Date.now()

    let undoButton = ""
    if (fileId && type === "success" && message.includes("deleted")) {
      undoButton = `<button type="button" class="btn btn-link p-0 ms-auto" onclick="undoDelete(${fileId})">Undo</button>`
    }

    const toastHTML = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
          <div class="toast-header">
            <strong class="me-auto">File Manager</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body d-flex">
            <div class="text-${type} me-auto">${message}</div>
            ${undoButton}
          </div>
        </div>
      `

    toastContainer.insertAdjacentHTML("beforeend", toastHTML)

    const toastElement = document.getElementById(toastId)
    if (toastElement) {
      const toast = new window.bootstrap.Toast(toastElement)
      toast.show()

      // Remove toast from DOM after it's hidden
      toastElement.addEventListener("hidden.bs.toast", () => {
        toastElement.remove()
      })
    }
  }
  
  /* ---------- Reset selection mode ---------- */
function resetSelectionMode() {
  const checkboxWrappers = document.querySelectorAll(".file-checkbox");
  const checkboxes = document.querySelectorAll(".select-file");

  checkboxes.forEach(cb => cb.checked = false);
  checkboxWrappers.forEach(div => div.style.display = "none");

  // Hide select all & delete buttons
  bulkDeleteBtn.classList.add("d-none");
  selectAllBtn.classList.add("d-none");
  selectAllBtn.classList.remove("active");
  selectAllBtn.innerHTML = '<i class="bx bx-select-multiple"></i> Select All';

  // Reset select button
  selectBtn.classList.remove("active");
  selectBtn.innerHTML = '<i class="bx bx-check-circle"></i> Select Items';
}


  // Undo delete function (global scope)
  window.undoDelete = (fileId) => {
    fetch("api/restore-file.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "id=" + fileId,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`)
        }
        return response.json()
      })
      .then((data) => {
        if (data.success) {
          showToast("File restored successfully", "success")
          window.location.reload()
        } else {
          showToast(data.message || "Error restoring file", "danger")
        }
      })
      .catch((error) => {
        showToast("Error restoring file", "danger")
      })
  }
})