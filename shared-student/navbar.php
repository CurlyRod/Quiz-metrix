<!-- NAVBAR -->
<section id="content">
    <!-- NAVBAR -->
    <nav>
        <i class='bx bx-menu toggle-sidebar'></i>
        
        <!-- Timer Section -->
        <div class="nav-timer">
            <button id="stopTimerBtn" class="timer-btn stop-btn">
                Stop
            </button>
            
            <div class="timer-display-container">
                <span id="timerDisplay" class="nav-timer-display">25:00</span>
            </div>
            
            <button id="startPauseBtn" class="timer-btn primary-btn">
                Start
            </button>
        </div>
        
        <form action="#"></form> 
        
        <h6 id="userNameDisplay"><?php 
            echo str_replace(" (Student)", "", $_SESSION['USER_NAME']); 
        ?></h6>
        
        <div class="profile">
    <?php
    // Get the user's name from session
    $userName = $_SESSION['USER_NAME'];
    
    // Remove " (Student)" suffix if present
    $displayName = str_replace(" (Student)", "", $userName);
    
    // Extract initials
    $initials = '';
    $nameParts = explode(' ', $displayName);
    
    if (count($nameParts) >= 2) {
        // Take first letter of first name and first letter of last name
        $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
    } else if (count($nameParts) == 1) {
        // If only one name, take first two letters
        $initials = strtoupper(substr($nameParts[0], 0, 2));
    } else {
        // Fallback
        $initials = 'US';
    }
    ?>
    
    <div class="profile-avatar" style="position: relative; display: inline-block;">
    <div class="avatar-initials profile-img" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; cursor: pointer;">
        <?php echo $initials; ?>
    </div>
</div>
    
    <ul class="profile-link">
        <li style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
            <div class="avatar-initials" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">
                <?php echo $initials; ?>
            </div>
            <div>
                <div class="nav-link" style="font-weight: bold;"><?php echo $displayName; ?></div>
                <div class="nav-link" style="font-size: 11px; color: #666;"><?php echo $_SESSION['USER_EMAIL'];?></div>
            </div>
        </li>
        <li><a class="nav-link" href="../../student/profile/index.php"><i class='bx bx-user icon'></i>Profile</a></li>
        <li><hr style="margin: 10px 0; border-color: black;"></li>
        <li><a class="nav-link" href="../../shared-student/logout.php"><i class='bx bx-log-out-circle icon'></i>Logout</a></li>
    </ul>
</div>
    </nav>

    <!-- Modal for Timer Settings -->
    <div class="timer-modal" id="timerModal">
        <div class="timer-modal-content">
            <div class="timer-modal-header">
                <h3>Timer Settings</h3>
                <!-- <button class="close-btn" data-bs-dismiss="modal">&times;</button> -->
            </div>
            <div class="timer-modal-body">
                <div class="timer-form-group">
                    <label for="studyTimeInput">Study Time (minutes)</label>
                    <div class="slider-container">
                        <input type="range" id="studyTimeInput" min="10" max="120" step="5" value="25">
                        <span id="studyTimeValue">25</span>
                    </div>
                </div>
                <div class="timer-form-group">
                    <label for="breakTimeInput">Break Time (minutes)</label>
                    <div class="slider-container">
                        <input type="range" id="breakTimeInput" min="5" max="30" step="1" value="5">
                        <span id="breakTimeValue">5</span>
                    </div>
                </div>
            </div>
            <div class="timer-modal-footer">
                <button id="saveTimerBtn" class="save-btn primary-btn">Save Settings</button>
            </div>
        </div>
    </div>

    <!-- Modal for Break Time -->
	<div class="break-modal" id="breakModal">
		<div class="modal-content">
			<div class="modal-header">
				<h3>Break Time!</h3>
				<!-- <span class="close-btn" id="closeBreakModal">&times;</span> -->
			</div>
			<div class="modal-body">
				<p>Your study session has ended. It's time for a break!</p>
				<div class="break-timer-display">
					<span id="breakTimerDisplay">05:00</span>
				</div>
				<p>Break duration: <span id="breakDurationDisplay">5</span> minutes</p>
			</div>
			<div class="modal-footer">
				<button id="studyMoreBtn" class="btn primary-btn">Study More</button>
			</div>
		</div>
	</div>
    <!-- NAVBAR -->

    

