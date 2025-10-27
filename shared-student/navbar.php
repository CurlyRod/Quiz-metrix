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
        
        <h6><?php 
            echo str_replace(" (Student)", "", $_SESSION['USER_NAME']); 
        ?></h6>
        
        <div class="profile">
            <img src="../../assets/img/avatars/avatar_2.png" alt="">
            <ul class="profile-link">
                <li style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                    <img src="../../assets/img/avatars/avatar_2.png" alt="" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <div class="nav-link" style="font-weight: bold;"><?php echo str_replace(" (Student)", "", $_SESSION['USER_NAME']); ?></div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
    // Timer elements
    const timerDisplay = document.getElementById('timerDisplay');
    const startPauseBtn = document.getElementById('startPauseBtn');
    const stopTimerBtn = document.getElementById('stopTimerBtn');
    const timerModal = document.getElementById('timerModal');
    const breakModal = document.getElementById('breakModal');
    const studyTimeInput = document.getElementById('studyTimeInput');
    const breakTimeInput = document.getElementById('breakTimeInput');
    const studyTimeValue = document.getElementById('studyTimeValue');
    const breakTimeValue = document.getElementById('breakTimeValue');
    const saveTimerBtn = document.getElementById('saveTimerBtn');
    const closeModalBtn = document.querySelector('.close-btn');
    const studyMoreBtn = document.getElementById('studyMoreBtn');
    const breakTimerDisplay = document.getElementById('breakTimerDisplay');
    const breakDurationDisplay = document.getElementById('breakDurationDisplay');
    // Remove closeBreakModal reference
    
    // Timer variables
    let timer;
    let timeLeft;
    let isRunning = false;
    let isStudyMode = true;
    let studyTime = 25 * 60;
    let breakTime = 5 * 60;
    let startTime;
    let breakTimer;
    
    // Load timer settings from localStorage
    loadTimerSettings();
    
    // Load timer state from localStorage
    loadTimerState();
    
    // Check if break modal was active before refresh
    checkBreakModalState();
    
    // Start/Pause button
    startPauseBtn.addEventListener('click', function() {
        if (isRunning) {
            pauseTimer();
        } else {
            startTimer();
        }
    });
    
    // Stop button
    stopTimerBtn.addEventListener('click', function() {
        resetTimer();
    });
    
    // Timer display click to open settings
    timerDisplay.addEventListener('click', function() {
        studyTimeInput.value = Math.floor(studyTime / 60);
        breakTimeInput.value = Math.floor(breakTime / 60);
        studyTimeValue.textContent = Math.floor(studyTime / 60);
        breakTimeValue.textContent = Math.floor(breakTime / 60);
        timerModal.classList.add('active');
    });
    
    // Save timer settings
    saveTimerBtn.addEventListener('click', function() {
        studyTime = parseInt(studyTimeInput.value) * 60;
        breakTime = parseInt(breakTimeInput.value) * 60;
        
        localStorage.setItem('studyTime', studyTime);
        localStorage.setItem('breakTime', breakTime);
        
        if (!isRunning) {
            timeLeft = isStudyMode ? studyTime : breakTime;
            updateTimerDisplay(timeLeft);
            saveTimerState();
        }
        
        timerModal.classList.remove('active');
    });
    
    // Close timer modal
    // closeModalBtn.addEventListener('click', function() {
    //     timerModal.classList.remove('active');
    // });
    
    // Study More button
    studyMoreBtn.addEventListener('click', function() {
        resetAllTimers();
        closeBreakModalHandler();
    });
    
    // Close timer modal when clicking outside (keep this for timer modal only)
    timerModal.addEventListener('click', function(e) {
        if (e.target === timerModal) {
            timerModal.classList.remove('active');
        }
    });
    
    // PREVENT break modal from closing when clicking outside
    breakModal.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Update study time value display
    studyTimeInput.addEventListener('input', function() {
        studyTimeValue.textContent = this.value;
    });
    
    // Update break time value display
    breakTimeInput.addEventListener('input', function() {
        breakTimeValue.textContent = this.value;
    });
    
    // Function to handle closing break modal
    function closeBreakModalHandler() {
        breakModal.classList.remove('active');
        localStorage.removeItem('breakModalActive'); // Remove from storage when closed
        localStorage.removeItem('breakTimeRemaining'); // Remove break time remaining
        if (breakTimer) {
            clearInterval(breakTimer);
        }
        resetAllTimers();
    }
    
    // Function to check if break modal should be active on page load
    function checkBreakModalState() {
        const breakModalActive = localStorage.getItem('breakModalActive');
        const breakTimeRemaining = localStorage.getItem('breakTimeRemaining');
        const breakStartTime = localStorage.getItem('breakStartTime');
        
        if (breakModalActive === 'true' && breakTimeRemaining && breakStartTime) {
            const elapsedSeconds = Math.floor((Date.now() - parseInt(breakStartTime)) / 1000);
            let remainingBreakTime = Math.max(0, parseInt(breakTimeRemaining) - elapsedSeconds);
            
            if (remainingBreakTime > 0) {
                // Resume the break modal
                breakModal.classList.add('active');
                updateBreakTimerDisplay(remainingBreakTime);
                breakDurationDisplay.textContent = Math.floor(breakTime / 60);
                
                // Restart the break timer
                startBreakTimer(remainingBreakTime);
            } else {
                // Break time finished while page was closed
                closeBreakModalHandler();
                isStudyMode = true;
                timeLeft = studyTime;
                updateTimerDisplay(timeLeft);
                saveTimerState();
            }
        }
    }
    
    // Function to start break timer with specific time
    function startBreakTimer(initialTime) {
        let breakTimeLeft = initialTime;
        
        // Clear any existing break timer
        if (breakTimer) {
            clearInterval(breakTimer);
        }
        
        breakTimer = setInterval(function() {
            breakTimeLeft--;
            updateBreakTimerDisplay(breakTimeLeft);
            
            // Save current state
            localStorage.setItem('breakTimeRemaining', breakTimeLeft);
            
            if (breakTimeLeft <= 0) {
                clearInterval(breakTimer);
                localStorage.removeItem('breakModalActive');
                localStorage.removeItem('breakTimeRemaining');
                localStorage.removeItem('breakStartTime');
                
                isStudyMode = true;
                timeLeft = studyTime;
                updateTimerDisplay(timeLeft);
                startPauseBtn.innerHTML = 'Start';
                saveTimerState();
                breakModal.classList.remove('active');
            }
        }, 1000);
    }
    
    // Function to load timer settings from localStorage
    function loadTimerSettings() {
        const savedStudyTime = localStorage.getItem('studyTime');
        const savedBreakTime = localStorage.getItem('breakTime');
        
        if (savedStudyTime) studyTime = parseInt(savedStudyTime);
        if (savedBreakTime) breakTime = parseInt(savedBreakTime);
    }
    
    // Function to load timer state from localStorage
    function loadTimerState() {
        const savedTimeLeft = localStorage.getItem('timeLeft');
        const savedIsRunning = localStorage.getItem('isRunning');
        const savedIsStudyMode = localStorage.getItem('isStudyMode');
        const savedStartTime = localStorage.getItem('startTime');
        
        if (savedIsStudyMode !== null) {
            isStudyMode = savedIsStudyMode === 'true';
        }
        
        if (savedTimeLeft !== null) {
            if (savedIsRunning === 'true' && savedStartTime) {
                const elapsedSeconds = Math.floor((Date.now() - parseInt(savedStartTime)) / 1000);
                timeLeft = Math.max(0, parseInt(savedTimeLeft) - elapsedSeconds);
                
                if (timeLeft > 0) {
                    isRunning = true;
                    startTime = Date.now() - (elapsedSeconds * 1000);
                    startTimerCountdown();
                    startPauseBtn.innerHTML = 'Pause';
                } else {
                    timeLeft = isStudyMode ? studyTime : breakTime;
                    isRunning = false;
                    startPauseBtn.innerHTML = 'Start';
                }
            } else {
                timeLeft = parseInt(savedTimeLeft);
                isRunning = false;
                startPauseBtn.innerHTML = 'Start';
            }
        } else {
            timeLeft = isStudyMode ? studyTime : breakTime;
        }
        
        updateTimerDisplay(timeLeft);
    }
    
    // Function to save timer state to localStorage
    function saveTimerState() {
        localStorage.setItem('timeLeft', timeLeft);
        localStorage.setItem('isRunning', isRunning);
        localStorage.setItem('isStudyMode', isStudyMode);
        if (isRunning && startTime) {
            localStorage.setItem('startTime', startTime);
        } else {
            localStorage.removeItem('startTime');
        }
    }
    
    // Function to start the timer
    function startTimer() {
        isRunning = true;
        startTime = Date.now() - ((timeLeft < (isStudyMode ? studyTime : breakTime) ? (isStudyMode ? studyTime : breakTime) - timeLeft : 0) * 1000);
        startTimerCountdown();
        startPauseBtn.innerHTML = 'Pause';
        saveTimerState();
    }
    
    // Function to start the countdown
    function startTimerCountdown() {
        clearInterval(timer);
        
        timer = setInterval(function() {
            const currentTime = Date.now();
            const elapsedSeconds = Math.floor((currentTime - startTime) / 1000);
            const totalSeconds = isStudyMode ? studyTime : breakTime;
            
            timeLeft = Math.max(0, totalSeconds - elapsedSeconds);
            updateTimerDisplay(timeLeft);
            saveTimerState();
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                isRunning = false;
                
                playNotificationSound();
                showNotification();
                
                isStudyMode = !isStudyMode;
                
                if (!isStudyMode) {
                    showBreakModal();
                } else {
                    timeLeft = studyTime;
                    updateTimerDisplay(timeLeft);
                    startPauseBtn.innerHTML = 'Start';
                    saveTimerState();
                }
            }
        }, 1000);
    }
    
    // Function to show break modal
    function showBreakModal() {
        breakDurationDisplay.textContent = Math.floor(breakTime / 60);
        updateBreakTimerDisplay(breakTime);
        breakModal.classList.add('active');
        
        // Save break modal state
        localStorage.setItem('breakModalActive', 'true');
        localStorage.setItem('breakTimeRemaining', breakTime);
        localStorage.setItem('breakStartTime', Date.now());
        
        startBreakTimer(breakTime);
    }
    
    // Function to update break timer display
    function updateBreakTimerDisplay(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        breakTimerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
    }
    
    // Function to pause the timer
    function pauseTimer() {
        clearInterval(timer);
        isRunning = false;
        startPauseBtn.innerHTML = 'Start';
        saveTimerState();
    }
    
    // Function to reset the timer
    function resetTimer() {
        clearInterval(timer);
        isRunning = false;
        timeLeft = isStudyMode ? studyTime : breakTime;
        updateTimerDisplay(timeLeft);
        startPauseBtn.innerHTML = 'Start';
        saveTimerState();
    }
    
    // Function to reset all timers
    function resetAllTimers() {
        clearInterval(timer);
        if (breakTimer) {
            clearInterval(breakTimer);
        }
        isRunning = false;
        isStudyMode = true;
        timeLeft = studyTime;
        updateTimerDisplay(timeLeft);
        startPauseBtn.innerHTML = 'Start';
        saveTimerState();
        
        // Clear break modal state
        localStorage.removeItem('breakModalActive');
        localStorage.removeItem('breakTimeRemaining');
        localStorage.removeItem('breakStartTime');
    }
    
    // Function to update the timer display
    function updateTimerDisplay(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        timerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
    }
    
    // Function to play notification sound
    function playNotificationSound() {
        const audio = new Audio('https://assets.mixkit.co/sfx/preview/mixkit-alarm-digital-clock-beep-989.mp3');
        audio.play();
    }
    
    // Function to show notification
    function showNotification() {
        if (Notification.permission === 'granted') {
            const title = isStudyMode ? 'Break Time!' : 'Study Time!';
            const message = isStudyMode ? 'Time to take a break.' : 'Time to focus on your studies.';
            
            new Notification(title, {
                body: message
            });
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission();
        }
    }
    
    // Handle page visibility changes
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible' && isRunning) {
            loadTimerState();
        }
    });
    
    // Request notification permission on page load
    if ('Notification' in window) {
        Notification.requestPermission();
    }
});
    </script>

