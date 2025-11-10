<?php
session_start();
require_once "../config/database.php";

function checkUserStatus() {
    global $conn;
    
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }
    
    $status_stmt = $conn->prepare("SELECT status FROM user_credential WHERE email = ?");
    $status_stmt->bind_param("s", $_SESSION['USER_EMAIL']);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();

    if ($status_result->num_rows === 0 || $status_result->fetch_assoc()['status'] !== 'Active') {
        session_destroy();
        throw new Exception('Your account has been deactivated. Please contact administrator.');
    }
    $status_stmt->close();
}

function getUserId() {
    global $conn;
    
    checkUserStatus(); 
    
    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

    // Prepare and execute query to get user ID
    $stmt = $conn->prepare("SELECT id FROM user_credential WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Execution error: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
    } else {
        throw new Exception('User not found');
    }
    $stmt->close();
    
    return $user_id;
}

// Validate user status on page load
try {
    // Check if user session exists
    if (!isset($_SESSION['USER_EMAIL'])) {
        header('Location: ../../index.php');
        exit;
    }
    
    // Validate user status using your function
    checkUserStatus();
    
    // If we get here, user is authenticated and active
    $user_id = getUserId();
    $_SESSION['USER_ID'] = $user_id; // Store user ID in session for later use
    
} catch (Exception $e) {
    // User is inactive or not authenticated - redirect to 403
    header('Location: ../Middleware/auth/403-Forbidden.html');
    exit;
}

// If user is active, show loader and proceed to dashboard
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading Dashboard - STI Alabang</title>
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
    </style>
</head>
<body>
    <div class="loader-container">
        <h1>Setting Up Your Dashboard</h1>
        <p>Please wait while we prepare your workspace...</p>
        
        <div class="loader-group">
            <!-- From Uiverse.io by mobinkakei --> 
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
        
        <div class="progress-text" id="progressText">Initializing system...</div>
    </div>
    
    <script>
        // Progress messages to show during loading
        const messages = [
            "Initializing system...",
            "Loading modules...", 
            "Setting up preferences...",
            "Almost ready...",
            "Redirecting to dashboard..."
        ];
        
        let currentMessage = 0;
        const progressText = document.getElementById('progressText');
        
        // Update progress message every second
        const messageInterval = setInterval(() => {
            currentMessage++;
            if (currentMessage < messages.length) {
                progressText.textContent = messages[currentMessage];
            } else {
                clearInterval(messageInterval);
            }
        }, 1000);
        
        // After refresh, redirect to home page
        setTimeout(function() {
            window.location.href = '../student/home/index.php';
        }, 4000);
    </script>
</body>
</html>