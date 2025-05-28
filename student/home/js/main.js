document.addEventListener('DOMContentLoaded', function() {

    $.ajax({
      url: "../../middleware/auth/ValidateUser.php",
      type: "POST",
      data: { action: "check-users" },
      dataType: "json",
      success: function (data) {
        if (data.userinfo) {  
           $("#user-current-id").val(data?.userinfo[1]) 
        } else {
          console.error("Invalid user info:", data);
        }
      },
      error: function (xhr, status, error) {
        console.error("User check error:", error);
      }
    });
    // Set current date in the header
    const today = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = today.toLocaleDateString('en-US', options);
    
    // Modal functionality
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.close-btn');
    const eventForm = document.getElementById('eventForm');
    
    // Open modals
    document.getElementById('addEventBtn').addEventListener('click', function() {
        openModal('eventModal');
        document.getElementById('eventModalTitle').textContent = 'Add New Event';
        document.getElementById('deleteEventBtn').style.display = 'none';
        document.getElementById('eventId').value = '';
        document.getElementById('eventTitle').value = '';
        
        // Set default date to today and configure date input
        const eventDateInput = document.getElementById('eventDate');
        const todayFormatted = formatDate(new Date());
        eventDateInput.value = todayFormatted;
        eventDateInput.min = todayFormatted;
        
        // Completely prevent past date selection
        eventDateInput.addEventListener('input', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                alert("You cannot select past dates. Please choose today or a future date.");
                this.value = todayFormatted;
                // Force the date picker to reopen with correct date
                this.blur();
                setTimeout(() => this.showPicker(), 100);
            }
        });
        
    
    });

    // Also validate on form submission
        // eventForm.addEventListener('submit', function(e) {
        //     const selectedDate = new Date(eventDateInput.value);
        //     const today = new Date();
        //     today.setHours(0, 0, 0, 0);
            
        //     if (selectedDate < today) {
        //         e.preventDefault();
        //         alert("Please select a current or future date for your event.");
        //         eventDateInput.value = todayFormatted;
        //         eventDateInput.focus();
        //     }
        // });
    
    // Rest of your modal opening handlers...
    // document.getElementById('setGoalBtn').addEventListener('click', function() {
    //     openModal('goalModal');
    // });
    
    document.getElementById('settingsBtn').addEventListener('click', function() {
        openModal('timerModal');
    });
    
    // Close modals
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            closeModal(modal);
        });
    });
    
    // Close modal when clicking outside
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this);
            }
        });
    });
    
    // Range slider value display
    document.getElementById('studyTimeInput').addEventListener('input', function() {
        document.getElementById('studyTimeValue').textContent = this.value;
    });
    
    document.getElementById('breakTimeInput').addEventListener('input', function() {
        document.getElementById('breakTimeValue').textContent = this.value;
    });
    
    // Helper functions
    function openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
    }
    
    function closeModal(modal) {
        modal.classList.remove('active');
    }
    
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Enhanced date validation for the entire page
    function validateAllDateInputs() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const todayFormatted = formatDate(today);
        
        document.querySelectorAll('input[type="date"]').forEach(input => {
            // Set min attribute to today
            input.min = todayFormatted;
            
            // Add validation on change
            input.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                if (selectedDate < today) {
                    alert("Please select today or a future date");
                    this.value = todayFormatted;
                }
            });
        });
    }
    
    // Initialize date validation
    validateAllDateInputs();
});