// SIDEBAR DROPDOWN
const allDropdown = document.querySelectorAll('#sidebar .side-dropdown');
const sidebar = document.getElementById('sidebar');

allDropdown.forEach(item=> {
	const a = item.parentElement.querySelector('a:first-child');
	a.addEventListener('click', function (e) {
		e.preventDefault();

		if(!this.classList.contains('active')) {
			allDropdown.forEach(i=> {
				const aLink = i.parentElement.querySelector('a:first-child');

				aLink.classList.remove('active');
				i.classList.remove('show');
			})
		}

		this.classList.toggle('active');
		item.classList.toggle('show');
	})
})

// SIDEBAR COLLAPSE
const toggleSidebar = document.querySelector('nav .toggle-sidebar');
const allSideDivider = document.querySelectorAll('#sidebar .divider');

// Create overlay element for mobile
const sidebarOverlay = document.createElement('div');
sidebarOverlay.className = 'sidebar-overlay';
document.body.appendChild(sidebarOverlay);

// Function to handle sidebar toggle
function handleSidebarToggle() {
    const isMobile = window.innerWidth <= 1000;
    
    if (isMobile) {
        // Mobile behavior - toggle with overlay
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('active');
        
        if (sidebar.classList.contains('show')) {
            // When opening sidebar on mobile
            allSideDivider.forEach(item=> {
                item.textContent = item.dataset.text;
            })
        } else {
            // When closing sidebar on mobile
            allSideDivider.forEach(item=> {
                item.textContent = '-'
            })
            allDropdown.forEach(item=> {
                const a = item.parentElement.querySelector('a:first-child');
                a.classList.remove('active');
                item.classList.remove('show');
            })
        }
    } else {
        // Desktop behavior - original functionality
        sidebar.classList.toggle('hide');
        
        if(sidebar.classList.contains('hide')) {
            allSideDivider.forEach(item=> {
                item.textContent = '-'
            })
            allDropdown.forEach(item=> {
                const a = item.parentElement.querySelector('a:first-child');
                a.classList.remove('active');
                item.classList.remove('show');
            })
        } else {
            allSideDivider.forEach(item=> {
                item.textContent = item.dataset.text;
            })
        }
    }
}

// Initialize sidebar state based on screen size
function initializeSidebar() {
    const isMobile = window.innerWidth <= 1000;
    
    if (isMobile) {
        sidebar.classList.remove('hide');
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('active');
    } else {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('active');
        
        // Check if sidebar should be hidden on desktop
        if(sidebar.classList.contains('hide')) {
            allSideDivider.forEach(item=> {
                item.textContent = '-'
            })
        } else {
            allSideDivider.forEach(item=> {
                item.textContent = item.dataset.text;
            })
        }
    }
}

// Toggle sidebar event
toggleSidebar.addEventListener('click', handleSidebarToggle);

// Close sidebar when overlay is clicked
sidebarOverlay.addEventListener('click', function() {
    sidebar.classList.remove('show');
    this.classList.remove('active');
    
    // Reset divider text when closing
    allSideDivider.forEach(item=> {
        item.textContent = '-'
    })
    allDropdown.forEach(item=> {
        const a = item.parentElement.querySelector('a:first-child');
        a.classList.remove('active');
        item.classList.remove('show');
    })
});

// Update existing sidebar event listeners for desktop
sidebar.addEventListener('mouseleave', function () {
    if(this.classList.contains('hide') && window.innerWidth > 1000) {
        allDropdown.forEach(item=> {
            const a = item.parentElement.querySelector('a:first-child');
            a.classList.remove('active');
            item.classList.remove('show');
        })
        allSideDivider.forEach(item=> {
            item.textContent = '-'
        })
    }
})

sidebar.addEventListener('mouseenter', function () {
    if(this.classList.contains('hide') && window.innerWidth > 1000) {
        allDropdown.forEach(item=> {
            const a = item.parentElement.querySelector('a:first-child');
            a.classList.remove('active');
            item.classList.remove('show');
        })
        allSideDivider.forEach(item=> {
            item.textContent = item.dataset.text;
        })
    }
})

// Handle window resize
window.addEventListener('resize', function() {
    const isMobile = window.innerWidth <= 1000;
    
    if (isMobile) {
        // Ensure sidebar is hidden on mobile unless explicitly shown
        if (!sidebar.classList.contains('show')) {
            sidebar.classList.remove('hide');
        }
        sidebarOverlay.classList.remove('active');
    } else {
        // Reset to desktop behavior
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('active');
        
        // Restore divider text on desktop
        if (!sidebar.classList.contains('hide')) {
            allSideDivider.forEach(item=> {
                item.textContent = item.dataset.text;
            })
        }
    }
});

// Close sidebar when a menu item is clicked (on mobile)
document.addEventListener('click', function(e) {
    const isMobile = window.innerWidth <= 1000;
    
    if (isMobile && sidebar.classList.contains('show')) {
        // Check if click is on a sidebar link
        if (e.target.closest('#sidebar a')) {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('active');
        }
    }
});

// Mobile sidebar close button handler (if you add the close button to sidebar.php)
const closeSidebarMobile = document.querySelector('.close-sidebar-mobile');
if (closeSidebarMobile) {
    closeSidebarMobile.addEventListener('click', function() {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('active');
    });
}

// Initialize sidebar on page load
initializeSidebar();

// PROFILE DROPDOWN
const profile = document.querySelector('nav .profile');
const imgProfile = profile.querySelector('.profile-img, img'); // Updated selector
const dropdownProfile = profile.querySelector('.profile-link');

if (imgProfile) {
    imgProfile.addEventListener('click', function () {
        dropdownProfile.classList.toggle('show');
    })
}

// MENU
const allMenu = document.querySelectorAll('main .content-data .head .menu');

allMenu.forEach(item=> {
	const icon = item.querySelector('.icon');
	const menuLink = item.querySelector('.menu-link');

	icon.addEventListener('click', function () {
		menuLink.classList.toggle('show');
	})
})

window.addEventListener('click', function (e) {
	if(e.target !== imgProfile) {
		if(e.target !== dropdownProfile) {
			if(dropdownProfile.classList.contains('show')) {
				dropdownProfile.classList.remove('show');
			}
		}
	}

	allMenu.forEach(item=> {
		const icon = item.querySelector('.icon');
		const menuLink = item.querySelector('.menu-link');

		if(e.target !== icon) {
			if(e.target !== menuLink) {
				if (menuLink.classList.contains('show')) {
					menuLink.classList.remove('show')
				}
			}
		}
	})
})

// PROGRESSBAR
const allProgress = document.querySelectorAll('main .card .progress');

allProgress.forEach(item=> {
	item.style.setProperty('--value', item.dataset.value)
})

// TIMER FUNCTIONALITY
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
    
    // Timer variables
    let timer;
    let timeLeft;
    let isRunning = false;
    let isStudyMode = true;
    let studyTime = 25 * 60;
    let breakTime = 5 * 60;
    let startTime;
    let breakTimer;
    
    // Only initialize timer if elements exist
    if (timerDisplay && startPauseBtn) {
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
        if (stopTimerBtn) {
            stopTimerBtn.addEventListener('click', function() {
                resetTimer();
            });
        }
        
        // Timer display click to open settings
        timerDisplay.addEventListener('click', function() {
            studyTimeInput.value = Math.floor(studyTime / 60);
            breakTimeInput.value = Math.floor(breakTime / 60);
            studyTimeValue.textContent = Math.floor(studyTime / 60);
            breakTimeValue.textContent = Math.floor(breakTime / 60);
            timerModal.classList.add('active');
        });
        
        // Save timer settings
        if (saveTimerBtn) {
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
        }
        
        // Study More button
        if (studyMoreBtn) {
            studyMoreBtn.addEventListener('click', function() {
                resetAllTimers();
                closeBreakModalHandler();
            });
        }
        
        // Close timer modal when clicking outside (keep this for timer modal only)
        if (timerModal) {
            timerModal.addEventListener('click', function(e) {
                if (e.target === timerModal) {
                    timerModal.classList.remove('active');
                }
            });
        }
        
        // PREVENT break modal from closing when clicking outside
        if (breakModal) {
            breakModal.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        // Update study time value display
        if (studyTimeInput) {
            studyTimeInput.addEventListener('input', function() {
                studyTimeValue.textContent = this.value;
            });
        }
        
        // Update break time value display
        if (breakTimeInput) {
            breakTimeInput.addEventListener('input', function() {
                breakTimeValue.textContent = this.value;
            });
        }
    }
    
    // Function to handle closing break modal
    function closeBreakModalHandler() {
        if (breakModal) {
            breakModal.classList.remove('active');
        }
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
        
        if (breakModalActive === 'true' && breakTimeRemaining && breakStartTime && breakModal) {
            const elapsedSeconds = Math.floor((Date.now() - parseInt(breakStartTime)) / 1000);
            let remainingBreakTime = Math.max(0, parseInt(breakTimeRemaining) - elapsedSeconds);
            
            if (remainingBreakTime > 0) {
                // Resume the break modal
                breakModal.classList.add('active');
                updateBreakTimerDisplay(remainingBreakTime);
                if (breakDurationDisplay) {
                    breakDurationDisplay.textContent = Math.floor(breakTime / 60);
                }
                
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
                if (startPauseBtn) {
                    startPauseBtn.innerHTML = 'Start';
                }
                saveTimerState();
                if (breakModal) {
                    breakModal.classList.remove('active');
                }
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
                    if (startPauseBtn) {
                        startPauseBtn.innerHTML = 'Pause';
                    }
                } else {
                    timeLeft = isStudyMode ? studyTime : breakTime;
                    isRunning = false;
                    if (startPauseBtn) {
                        startPauseBtn.innerHTML = 'Start';
                    }
                }
            } else {
                timeLeft = parseInt(savedTimeLeft);
                isRunning = false;
                if (startPauseBtn) {
                    startPauseBtn.innerHTML = 'Start';
                }
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
        if (startPauseBtn) {
            startPauseBtn.innerHTML = 'Pause';
        }
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
                    if (startPauseBtn) {
                        startPauseBtn.innerHTML = 'Start';
                    }
                    saveTimerState();
                }
            }
        }, 1000);
    }
    
    // Function to show break modal
    function showBreakModal() {
        if (breakDurationDisplay && breakModal) {
            breakDurationDisplay.textContent = Math.floor(breakTime / 60);
            updateBreakTimerDisplay(breakTime);
            breakModal.classList.add('active');
            
            // Save break modal state
            localStorage.setItem('breakModalActive', 'true');
            localStorage.setItem('breakTimeRemaining', breakTime);
            localStorage.setItem('breakStartTime', Date.now());
            
            startBreakTimer(breakTime);
        }
    }
    
    // Function to update break timer display
    function updateBreakTimerDisplay(seconds) {
        if (breakTimerDisplay) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            breakTimerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
        }
    }
    
    // Function to pause the timer
    function pauseTimer() {
        clearInterval(timer);
        isRunning = false;
        if (startPauseBtn) {
            startPauseBtn.innerHTML = 'Start';
        }
        saveTimerState();
    }
    
    // Function to reset the timer
    function resetTimer() {
        clearInterval(timer);
        isRunning = false;
        timeLeft = isStudyMode ? studyTime : breakTime;
        updateTimerDisplay(timeLeft);
        if (startPauseBtn) {
            startPauseBtn.innerHTML = 'Start';
        }
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
        if (startPauseBtn) {
            startPauseBtn.innerHTML = 'Start';
        }
        saveTimerState();
        
        // Clear break modal state
        localStorage.removeItem('breakModalActive');
        localStorage.removeItem('breakTimeRemaining');
        localStorage.removeItem('breakStartTime');
    }
    
    // Function to update the timer display
    function updateTimerDisplay(seconds) {
        if (timerDisplay) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            timerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
        }
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