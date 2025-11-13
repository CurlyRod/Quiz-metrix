<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Term Extractor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <?php include '../../shared-student/header.php'; ?>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<!-- Sidebar and Navbar -->
    <?php
        session_start();
        if (!isset($_SESSION['user'])) {
            header("Location: ../../landing-page/");
            exit();
        } 
    
        $userData = $_SESSION['user']; 
        $_SESSION['USER_NAME'] = $userData['displayName'];
        $currentPage = 'ai-tools'; 

        include '../../shared-student/sidebar.php';
        include '../../shared-student/navbar.php';
    ?>
    <input type="hidden" name="user-current-id" id="user-current-id">
    <div class="container">
        <h1>PDF Term Extractor</h1>
        <div class="subtitle-container">
            <p class="subtitle">Upload a PDF to extract key terms and definitions using AI</p>
            <div class="disclaimer-icon" id="disclaimerIcon" title="Extraction limit info">
                !
                <div class="disclaimer-tooltip" id="disclaimerTooltip">
                    AI extraction may not capture 100% of terms and definitions accurately.
                </div>
            </div>
        </div>
         
        <!-- Limit Info Section -->
        <div class="limit-info">
            <div class="limit-text">
                <span id="extractionsLeft">Extractions remaining: <strong id="count">2/2</strong></span>
            </div>
            <div class="disclaimer-icon" id="disclaimerIcon" title="Extraction limit info">
                !
                <div class="disclaimer-tooltip" id="disclaimerTooltip">
                    Extraction limits reset every Monday at 8:00 AM.
                </div>
            </div>
        </div>
        
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="upload-area" id="uploadArea">
                <i class="bx bx-cloud-upload fs-1 text-primary mb-2"></i>
                <h3>Upload PDF File</h3>
                <p>Drag & drop or click to select a PDF file</p>
                <div class="file-input">
                    <input type="file" id="pdfFile" name="pdfFile" accept=".pdf" required style="display:none;">
                </div>
                <div id="fileInfo" style="margin-top: 10px; min-height: 40px;"></div>
            </div>
            
            <div class="error" id="error"></div>
            <div class="success" id="success"></div>
            
            <div class="button-group">
                <button type="submit" class="btn" id="processBtn">Extract Terms</button>
            </div>
        </form>
        
        
    </div>

        <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>


    <script src="js/script.js"></script>
    <?php include '../../shared-student/script.php'; ?>
</body>
</html>
