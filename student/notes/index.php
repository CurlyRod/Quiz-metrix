<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lemon&display=swap" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="notes.css">
    <link rel="stylesheet" href="../../vendor/student/home/home.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/x-icon" href="../../assets/img/logo/apple-touch-icon.png">



</head>


<body>
    <?php
    session_start();
    // Check if user is logged in
    if (!isset($_SESSION['user'])) {
        header("Location: ../../landing-page/");
        exit();
    }

    $userData = $_SESSION['user'];
    $_SESSION['USER_NAME'] = $userData['displayName'];
    ?>
    <?php
    $currentPage = 'notes'; 

    include '../../shared-student/sidebar.php';
    include '../../shared-student/navbar.php';
    ?>
    

   <div class="app-container">
    <h1 class="app-title">My Notes</h1>

    <!-- Search -->
    <div class="search-container">
      <input type="text" id="searchNotes" placeholder="🔍 Search Notes">
    </div>

    <!-- Create & Bulk Delete -->
<div class="actions">
  <button id="createNoteBtn">
       <i class="fas fa-plus"></i> Create Note
  </button>
  <button id="selectItemsBtn">
  <i class="fas fa-check-square"></i> <span>Select Notes</span>
</button>

  <button id="bulkDeleteBtn" style="display:none;">
    <i class="fas fa-trash"></i> Delete Selected
  </button>
</div>



    <!-- Notes -->
    <div id="notes-container" class="notes-container"></div>
  </div>

  <!-- Hidden -->
  <input type="hidden" id="user-current-id" value="1">

  <!-- Create Note Modal -->
  <div id="createNoteModal" class="modal">
    <div class="modal-content note-color-default" id="note-expanded">
      <input type="text" id="note-title" placeholder="Title" class="form-control mb-2" maxlength="50">
      <textarea id="note-content" placeholder="Take a note..." class="form-control mb-2"maxlength="3000"></textarea>
      <small id="charCount" class="text-muted d-block mb-2">0 / 3000</small>


     <!-- Color Palette -->
                        <div id="color-palette" class="color-palette mt-3">
                            <div class="color-option selected" data-color="default" style="background-color: #ffffff;"></div>
                            <div class="color-option" data-color="red" style="background-color: #f8d7da;"></div>
                            <div class="color-option" data-color="orange" style="background-color: #fff3cd;"></div>
                            <div class="color-option" data-color="yellow" style="background-color: #fff8e1;"></div>
                            <div class="color-option" data-color="green" style="background-color: #d1e7dd;"></div>
                            <div class="color-option" data-color="teal" style="background-color: #d1ecf1;"></div>
                            <div class="color-option" data-color="blue" style="background-color: #cfe2ff;"></div>
                            <div class="color-option" data-color="purple" style="background-color: #e2d9f3;"></div>
                            <div class="color-option" data-color="pink" style="background-color: #f8d7f7;"></div>
                            <div class="color-option" data-color="gray" style="background-color: #e9ecef;"></div>
                        </div>
       
    

      <!-- Buttons -->
      <div class="mt-3 d-flex justify-content-end gap-2">
         
        <button id="btn-close" class="btn btn-light">Close</button>
        <button id="btn-save" class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>

  <!-- View Note Modal -->
  <div id="viewNoteModal" class="modal">
    <div class="modal-content view-modal">
      <button id="viewBackBtn" class="back-btn"><i class="fas fa-arrow-left"></i></button>
      
      <h3 id="viewTitle">Title</h3>
      <hr>
      <p id="viewContent">Sample text</p>
      <hr>
      <div class="view-actions">
        <small id="viewDate">Date & Time Created</small>
        <button id="viewEditBtn" class="btn-action"><i class="fas fa-edit"></i> Edit</button>
        <button id="viewDeleteBtn" class="btn-action delete"><i class="fas fa-trash"></i> Delete</button>
      </div>
    </div>
  </div>

<!-- Delete Confirmation Modal -->
<div class="delete-confirm-modal" id="deleteConfirmModal">
    <div class="delete-confirm-overlay"></div>
    <div class="delete-confirm-content">
        <div class="delete-confirm-header">
            <h3 class="delete-confirm-title" id="deleteModalTitle">Delete Quiz</h3>
            <button type="button" class="delete-confirm-close" data-dismiss="modal">
                <svg class="delete-confirm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="delete-confirm-body">
            <p id="deleteModalBody">Are you sure you want to move this quiz to recycling bin? You can restore it later.</p>
        </div>
        <div class="delete-confirm-footer">
            <button type="button" class="delete-confirm-btn delete-confirm-cancel" data-dismiss="modal">Cancel</button>
            <button type="button" class="delete-confirm-btn delete-confirm-delete" id="confirmDeleteBtn">Move to Bin</button>
        </div>
    </div>
</div>


    <!-- Toast container -->
  <div id="toast-container"></div>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="notes.js"></script>
    <script src="../../vendor/admin/home/home.js"></script>
    <script src="../../vendor/bootstrap/jquery.min.js"></script>



</body>

</html>
