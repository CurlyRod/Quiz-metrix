<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycle Bin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <?php include '../../shared-student/header.php'; ?>

    
</head>
<body>
    <!-- Sidebar and Navbar -->
    <?php
        session_start();
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ../../landing-page/");
            exit();
        } 
    
        $userData = $_SESSION['user']; 
        $_SESSION['USER_NAME'] = $userData['displayName'];

        $currentPage = 'recyclebin'; 

        include '../../shared-student/sidebar.php';
        include '../../shared-student/navbar.php';
    ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../../shared-student/script.php'; ?>

  
</body>
</html>
