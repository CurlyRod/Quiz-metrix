<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons CSS -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" rel="stylesheet">

    <?php include '../../shared-student/header.php'; ?>


    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
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

        

        $userId = $_SESSION['user_id'];

        $stmt = $conn->prepare("SELECT * FROM notes WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        ?>
<?php
include '../../shared-student/sidebar.php';
include '../../shared-student/navbar.php';
?>
    <div class="container profile-container">
        <div class="row">
            <!-- Profile Information -->
            <div class="col-md-8 mb-4">
                <div class="profile-card">
                    <div class="d-flex align-items-start">
                        <div class="avatar-container">
                            <img id="profile-avatar" src="avatar_2_boy.png" alt="Profile Avatar" class="avatar-img">
                            <div class="avatar-toggle" id="avatar-toggle">
                                <i class="bx bx-edit-alt"></i>
                            </div>
                            <div class="avatar-dropdown" id="avatar-dropdown">
                                <div class="avatar-option" data-avatar="avatar_2_boy.png">
                                    <img src="avatar_2_boy.png" alt="Boy Avatar">
                                    <span>Boy</span>
                                </div>
                                <div class="avatar-option" data-avatar="avatar_2_girl.png">
                                    <img src="avatar_2_girl.png" alt="Girl Avatar">
                                    <span>Girl</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="profile-info">
                            <div class="info-label">Name</div>
                            <!-- <div class="info-value" id="user-name">Mj Despi</div> -->
                            <?php 
                                echo str_replace(" (Student)", "", $_SESSION['USER_NAME']); 
                            ?>
                        </div>
                        <div class="profile-info">
                            <div class="info-label">Email</div>
                            <?php echo $_SESSION['USER_EMAIL']; ?>

                        </div>
                        <div class="profile-info">
                            <div class="info-label">Password</div>
                            <div class="info-value">
                                <span>********</span>
                                <button class="btn-change ms-2" id="toggle-password-form">Change</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Password Verification Form (Hidden by default) -->
                    <div class="password-form" id="current-password-form">
                        <div class="stats-header">Verify Current Password</div>
                        <div class="password-form-content">
                            <div class="mb-3">
                                <label for="current-password" class="form-label">Current Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" class="form-control" id="current-password">
                                    <button type="button" class="password-toggle-btn" data-target="current-password">
                                        <i class="bx bx-eye-closed" style="color: #000000;"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-light me-2" id="cancel-current-password">Cancel</button>
                                <button class="btn btn-dark" id="verify-current-password">Done</button>
                            </div>
                        </div>
                    </div>

                    <!-- Password Change Form (Hidden by default) -->
                    <div class="password-form" id="password-form">
                        <div class="stats-header">Change Password</div>
                        <div class="password-form-content">
                            <div class="mb-3">
                                <label for="new-password" class="form-label">New Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" class="form-control" id="new-password">
                                    <button type="button" class="password-toggle-btn" data-target="new-password">
                                        <i class="bx bx-eye-closed"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm-password" class="form-label">Confirm Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" class="form-control" id="confirm-password">
                                    <button type="button" class="password-toggle-btn" data-target="confirm-password">
                                        <i class="bx bx-eye-closed"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-light me-2" id="cancel-password-change">Cancel</button>
                                <button class="btn btn-dark" id="submit-password-change">Change Password</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="col-md-4">
                <!-- Quiz Stats -->
                <div class="profile-card p-0 overflow-hidden">
                    <div class="stats-header">Quiz</div>
                    <div class="stats-body">
                        <div class="stats-row">
                            <div class="stats-cell">
                                <div class="text-muted small">Created</div>
                                <div class="fw-medium">10</div>
                            </div>
                            <div class="stats-cell">
                                <div class="text-muted small">Taken</div>
                                <div class="fw-medium">5</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Flashcard Stats -->
                <div class="profile-card p-0 overflow-hidden">
                    <div class="stats-header">Flashcard</div>
                    <div class="stats-body">
                        <div class="stats-row">
                            <div class="stats-cell">
                                <div class="text-muted small">Created</div>
                                <div class="fw-medium">10</div>
                            </div>
                            <div class="stats-cell">
                                <div class="text-muted small">Taken</div>
                                <div class="fw-medium">5</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <?php include '../../shared-student/script.php'; ?>


</body>
</html>
