<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes App</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    include '../../shared-student/sidebar.php';
    include '../../shared-student/navbar.php';
    ?>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Notes App</h1>
        <input type="hidden" name="user-current-id" id="user-current-id">
        <!-- Note Input Section -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <!-- Collapsed Note Entry -->
              
                <div id="note-collapsed" class="card shadow-sm" style="cursor: pointer;">
                    <div class="card-body d-flex align-items-center">
                        <span class="flex-grow-1">Take a note...</span>

                    </div>
                </div>

                <!-- Expanded Note Entry (hidden initially) -->
                <div id="note-expanded" class="card shadow-sm d-none">
                    <div class="card-body">
                        <input type="text" id="note-title" class="form-control border-0 fw-bold mb-2" placeholder="Title" maxlength="20">
                      
                        <textarea id="note-content" class="form-control border-0" placeholder="Take a note..." maxlength="3000"></textarea>

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

                        <div class="d-flex justify-content align-items-center mt-3">

                            <button id="btn-close" class="btn btn-sm btn-light me-2">Close</button>
                            <button id="btn-save" class="btn btn-sm btn-primary">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Display Section -->
    <div class="row" id="notes-container">
        <!-- Notes will be displayed here -->
    </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="notes.js"></script>
    <script src="../../vendor/admin/home/home.js"></script>
    <script src="../../vendor/bootstrap/jquery.min.js"></script>



</body>

</html>