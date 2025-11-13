<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing PDF - STI Alabang</title>
    <style>
        /* From Uiverse.io by mobinkakei */ 
        .loader {
            --path: #2f3545;
            --dot: #5628ee;
            --duration: 3s;
            width: 44px;
            height: 44px;
            position: relative;
        }

        .loader:before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            position: absolute;
            display: block;
            background: var(--dot);
            top: 37px;
            left: 19px;
            transform: translate(-18px, -18px);
            animation: dotRect var(--duration) cubic-bezier(0.785, 0.135, 0.15, 0.86) infinite;
        }

        .loader svg {
            display: block;
            width: 100%;
            height: 100%;
        }

        .loader svg rect,
        .loader svg polygon,
        .loader svg circle {
            fill: none;
            stroke: var(--path);
            stroke-width: 10px;
            stroke-linejoin: round;
            stroke-linecap: round;
        }

        .loader svg polygon {
            stroke-dasharray: 145 76 145 76;
            stroke-dashoffset: 0;
            animation: pathTriangle var(--duration) cubic-bezier(0.785, 0.135, 0.15, 0.86) infinite;
        }

        .loader svg rect {
            stroke-dasharray: 192 64 192 64;
            stroke-dashoffset: 0;
            animation: pathRect 3s cubic-bezier(0.785, 0.135, 0.15, 0.86) infinite;
        }

        .loader svg circle {
            stroke-dasharray: 150 50 150 50;
            stroke-dashoffset: 75;
            animation: pathCircle var(--duration) cubic-bezier(0.785, 0.135, 0.15, 0.86) infinite;
        }

        .loader.triangle {
            width: 48px;
        }

        .loader.triangle:before {
            left: 21px;
            transform: translate(-10px, -18px);
            animation: dotTriangle var(--duration) cubic-bezier(0.785, 0.135, 0.15, 0.86) infinite;
        }

        @keyframes pathTriangle {
            33% {
                stroke-dashoffset: 74;
            }
            66% {
                stroke-dashoffset: 147;
            }
            100% {
                stroke-dashoffset: 221;
            }
        }

        @keyframes dotTriangle {
            33% {
                transform: translate(0, 0);
            }
            66% {
                transform: translate(10px, -18px);
            }
            100% {
                transform: translate(-10px, -18px);
            }
        }

        @keyframes pathRect {
            25% {
                stroke-dashoffset: 64;
            }
            50% {
                stroke-dashoffset: 128;
            }
            75% {
                stroke-dashoffset: 192;
            }
            100% {
                stroke-dashoffset: 256;
            }
        }

        @keyframes dotRect {
            25% {
                transform: translate(0, 0);
            }
            50% {
                transform: translate(18px, -18px);
            }
            75% {
                transform: translate(0, -36px);
            }
            100% {
                transform: translate(-18px, -18px);
            }
        }

        @keyframes pathCircle {
            25% {
                stroke-dashoffset: 125;
            }
            50% {
                stroke-dashoffset: 175;
            }
            75% {
                stroke-dashoffset: 225;
            }
            100% {
                stroke-dashoffset: 275;
            }
        }

        .loader {
            display: inline-block;
            margin: 0 16px;
        }

        /* Custom styles for our loader page */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
        }

        .loader-container {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
        }

        .loader-group {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        h1 {
            margin-bottom: 10px;
            font-size: 24px;
        }

        p {
            margin-top: 0;
            opacity: 0.8;
        }

        .progress-text {
            margin-top: 20px;
            font-size: 14px;
            opacity: 0.7;
        }

        .error-message {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.3);
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .retry-button {
            background: #fff;
            color: #667eea;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
            font-weight: bold;
        }

        .retry-button:hover {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="loader-container">
        <h1>Processing Your PDF</h1>
        <p id="statusMessage">Please wait while we extract important terms...</p>
        
        <div class="loader-group">
            <div class="loader">
                <svg viewBox="0 0 80 80">
                    <circle r="32" cy="40" cx="40" id="test"></circle>
                </svg>
            </div>

            <div class="loader triangle">
                <svg viewBox="0 0 86 80">
                    <polygon points="43 8 79 72 7 72"></polygon>
                </svg>
            </div>

            <div class="loader">
                <svg viewBox="0 0 80 80">
                    <rect height="64" width="64" y="8" x="8"></rect>
                </svg>
            </div>
        </div>

        <div class="progress-text" id="progressText">Initializing PDF processing...</div>
        
        <div id="errorContainer" style="display: none;" class="error-message">
            <strong>Error:</strong> <span id="errorMessage"></span>
            <br>
            <button class="retry-button" onclick="window.location.href = 'index.php'">Try Again</button>
        </div>
    </div>
    
    <script>
        // Progress messages to show during processing
        const messages = [
            "Initializing PDF processing...",
            "Uploading PDF file...", 
            "Extracting text content...",
            "Analyzing terms and definitions...",
            "Processing AI extraction...",
            "Finalizing results...",
            "Redirecting to results page..."
        ];
        
        let currentMessage = 0;
        const progressText = document.getElementById('progressText');
        const statusMessage = document.getElementById('statusMessage');
        const errorContainer = document.getElementById('errorContainer');
        const errorMessage = document.getElementById('errorMessage');
        
        // Function to update progress messages
        function updateProgress() {
            if (currentMessage < messages.length - 1) {
                currentMessage++;
                progressText.textContent = messages[currentMessage];
            }
        }
        
        // Start progress updates
        const messageInterval = setInterval(updateProgress, 2000);
        
        // Function to show error
        function showError(message) {
            clearInterval(messageInterval);
            statusMessage.textContent = 'Processing Failed';
            progressText.style.display = 'none';
            errorMessage.textContent = message;
            errorContainer.style.display = 'block';
        }
        
        // Start PDF processing when page loads
        async function startProcessing() {
            try {
                // Get the file data from sessionStorage
                const fileData = sessionStorage.getItem('pendingPdfFile');
                const fileName = sessionStorage.getItem('pendingPdfFileName');
                
                if (!fileData) {
                    throw new Error('No PDF file found. Please go back and upload a file.');
                }
                
                const fileInfo = JSON.parse(fileData);
                
                // Update status
                statusMessage.textContent = `Processing: ${fileInfo.name}`;
                progressText.textContent = messages[0];
                
                const formData = new FormData();
    
                
                progressText.textContent = "Uploading PDF file...";
                
                setTimeout(() => {
                    progressText.textContent = "Extraction complete! Redirecting...";
                    
                    // Clear session storage
                    sessionStorage.removeItem('pendingPdfFile');
                    sessionStorage.removeItem('pendingPdfFileName');
                    
                    // Redirect to results page after short delay
                    setTimeout(() => {
                        window.location.href = 'extraction-results.php';
                    }, 1000);
                    
                }, 5000); // Simulate 5-second processing time
                
            } catch (error) {
                console.error('Processing error:', error);
                showError(error.message);
            }
        }
        
        // Start processing when page loads
        document.addEventListener('DOMContentLoaded', startProcessing);
        
        // Safety timeout - if processing takes too long
        setTimeout(() => {
            if (errorContainer.style.display === 'none') {
                showError('Processing is taking longer than expected. Please try again.');
            }
        }, 120000); // 120 second timeout
    </script>
</body>
</html>