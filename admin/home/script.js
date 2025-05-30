document.addEventListener("DOMContentLoaded", () => {
  // DOM elements
  const userTable = document.getElementById("userTableBody");
  const searchInput = document.getElementById("searchInput");
  const clearSearch = document.getElementById("clearSearch");
  const paginationControls = document.getElementById("paginationControls");
  const paginationInfo = document.getElementById("paginationInfo");
  const prevPage = document.getElementById("prevPage");
  const nextPage = document.getElementById("nextPage");

  // Pagination variables
  const itemsPerPage = 10;
  let currentPage = 1;
  let totalPages = 1;

  // User data
  let allUsers = [];
  let filteredUsers = [];

  // Initial load
  loadUsers();
  generateCalendar();
  loadTotalUsers();

  // Event listeners
  searchInput.addEventListener("input", filterUsers);
  if (clearSearch) clearSearch.addEventListener("click", clearSearchField);
  if (prevPage) prevPage.addEventListener("click", goToPrevPage);
  if (nextPage) nextPage.addEventListener("click", goToNextPage);

  async function loadUsers() {
    // Show loading state
    userTable.innerHTML = '<tr><td colspan="4" class="text-center">Loading users...</td></tr>';
    
    try {
        const response = await fetch(`api/fetch_users.php?page=${currentPage}&limit=${itemsPerPage}&search=${encodeURIComponent(searchInput.value.trim())}`);
        const data = await response.json();
        
        if (data.success) {
            allUsers = data.users;
            filteredUsers = [...allUsers];
            totalPages = data.pagination.totalPages;
            updatePagination();
            displayUsers();
        } else {
            userTable.innerHTML = `<tr><td colspan="4" class="text-center text-danger">${data.message || 'Error loading users'}</td></tr>`;
        }
    } catch (error) {
        console.error("Error:", error);
        userTable.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Network error</td></tr>';
    }
}

  function filterUsers() {
    const searchTerm = searchInput.value.toLowerCase().trim();

    if (searchTerm === "") {
      filteredUsers = [...allUsers];
    } else {
      filteredUsers = allUsers.filter(
        user => user.name.toLowerCase().includes(searchTerm) || 
               user.email.toLowerCase().includes(searchTerm)
      );
    }

    // Reset to first page when filtering
    currentPage = 1;
    updatePagination();
    displayUsers();
  }

  function clearSearchField() {
    searchInput.value = "";
    filterUsers();
  }

  function updatePagination() {
    totalPages = Math.max(1, Math.ceil(filteredUsers.length / itemsPerPage));

    // Ensure current page is valid
    if (currentPage > totalPages) {
      currentPage = totalPages;
    }

    // Update pagination controls
    if (paginationControls) {
      paginationControls.innerHTML = "";

      // Previous button
      const prevLi = document.createElement("li");
      prevLi.className = `page-item ${currentPage === 1 ? "disabled" : ""}`;
      prevLi.innerHTML = `
        <a class="page-link" href="#" aria-label="Previous">
          <span aria-hidden="true">&laquo;</span>
        </a>
      `;
      prevLi.addEventListener("click", goToPrevPage);
      paginationControls.appendChild(prevLi);

      // Page numbers
      const startPage = Math.max(1, currentPage - 2);
      const endPage = Math.min(totalPages, startPage + 4);

      for (let i = startPage; i <= endPage; i++) {
        const pageLi = document.createElement("li");
        pageLi.className = `page-item ${i === currentPage ? "active" : ""}`;
        pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;

        pageLi.addEventListener("click", (e) => {
          e.preventDefault();
          currentPage = i;
          displayUsers();
          updatePagination();
        });

        paginationControls.appendChild(pageLi);
      }

      // Next button
      const nextLi = document.createElement("li");
      nextLi.className = `page-item ${currentPage === totalPages ? "disabled" : ""}`;
      nextLi.innerHTML = `
        <a class="page-link" href="#" aria-label="Next">
          <span aria-hidden="true">&raquo;</span>
        </a>
      `;
      nextLi.addEventListener("click", goToNextPage);
      paginationControls.appendChild(nextLi);
    }

    // Update pagination info text
    if (paginationInfo) {
      const start = (currentPage - 1) * itemsPerPage + 1;
      const end = Math.min(start + itemsPerPage - 1, filteredUsers.length);
      paginationInfo.textContent = `Showing ${start}-${end} of ${filteredUsers.length} users`;
    }
  }

  function goToPrevPage(e) {
    e.preventDefault();
    if (currentPage > 1) {
      currentPage--;
      displayUsers();
      updatePagination();
    }
  }

  function goToNextPage(e) {
    e.preventDefault();
    if (currentPage < totalPages) {
      currentPage++;
      displayUsers();
      updatePagination();
    }
  }

  function displayUsers() {
    if (filteredUsers.length === 0) {
      userTable.innerHTML = '<tr><td colspan="4" class="text-center">No users found</td></tr>';
      return;
    }

    userTable.innerHTML = "";

    // Get users for current page
    const startIndex = (currentPage - 1) * itemsPerPage;
    const pageUsers = filteredUsers.slice(startIndex, startIndex + itemsPerPage);

    pageUsers.forEach(user => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${user.id}</td>
        <td>${user.name}</td>
        <td>${user.email}</td>
        <td>${new Date(user.date_created).toLocaleString()}</td>
      `;
      userTable.appendChild(row);
    });
  }

  // Function to load total users count
function loadTotalUsers() {
    fetch('api/count_users.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalUsersCount').textContent = data.total;
        })
        .catch(error => {
            console.error('Error fetching total users:', error);
        });
}


// Function to generate the calendar with limited interactivity
function generateCalendar() {
  // Calendar variables
  const calendarContainer = document.getElementById("calendar-container");
  const currentMonthElement = document.getElementById("currentMonth");
  const prevMonthButton = document.getElementById("prevMonth");
  const nextMonthButton = document.getElementById("nextMonth");
  const todayButton = document.getElementById("todayButton");
  
  // Current date
  let currentDate = new Date();
  let today = new Date();
  today.setHours(0, 0, 0, 0); // Normalize today's date
  
  // Initialize calendar
  renderCalendar(currentDate);
  
  // Event listeners for navigation
  prevMonthButton.addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar(currentDate);
  });
  
  nextMonthButton.addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar(currentDate);
  });
  
  todayButton.addEventListener("click", () => {
    currentDate = new Date();
    renderCalendar(currentDate);
  });
  
  // Function to render the calendar
  function renderCalendar(date) {
    // Update month title
    const monthNames = [
      "January", "February", "March", "April", "May", "June",
      "July", "August", "September", "October", "November", "December"
    ];
    currentMonthElement.textContent = `${monthNames[date.getMonth()]} ${date.getFullYear()}`;
    
    // Clear calendar container
    calendarContainer.innerHTML = "";
    
    // Create day headers
    const dayHeaders = document.createElement("div");
    dayHeaders.className = "calendar-grid";
    
    const daysOfWeek = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    daysOfWeek.forEach((day) => {
      const dayHeader = document.createElement("div");
      dayHeader.className = "calendar-day-header";
      dayHeader.textContent = day;
      dayHeaders.appendChild(dayHeader);
    });
    
    calendarContainer.appendChild(dayHeaders);
    
    // Create calendar days
    const calendarDays = document.createElement("div");
    calendarDays.className = "calendar-grid";
    
    // Get first day of month and starting day of week
    const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
    const startingDay = firstDay.getDay();
    
    // Get last day of month
    const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
    const totalDays = lastDay.getDate();
    
    // Get last day of previous month
    const prevMonthLastDay = new Date(date.getFullYear(), date.getMonth(), 0).getDate();
    
    // Days from previous month (disabled)
    for (let i = startingDay - 1; i >= 0; i--) {
      const day = document.createElement("div");
      day.className = "calendar-day other-month";
      day.textContent = prevMonthLastDay - i;
      day.style.opacity = "0.5";
      calendarDays.appendChild(day);
    }
    
    // Days from current month
    for (let i = 1; i <= totalDays; i++) {
      const day = document.createElement("div");
      day.className = "calendar-day";
      day.textContent = i;
      
      // Create a date object for this day
      const dayDate = new Date(date.getFullYear(), date.getMonth(), i);
      
      // Check if this day is today
      if (dayDate.getTime() === today.getTime()) {
        day.classList.add("today");
        // Only make today clickable
        day.addEventListener("click", function() {
          alert("Today is clicked!");
        });
      } else {
        day.style.pointerEvents = "none"; // Disable clicking
      }
      
      calendarDays.appendChild(day);
    }
    
    // Days from next month (disabled)
    const totalCells = 42; // 6 rows x 7 days
    const remainingCells = totalCells - (startingDay + totalDays);
    
    for (let i = 1; i <= remainingCells; i++) {
      const day = document.createElement("div");
      day.className = "calendar-day other-month";
      day.textContent = i;
      day.style.opacity = "0.5";
      calendarDays.appendChild(day);
    }
    
    calendarContainer.appendChild(calendarDays);
  }
}


// PROFILE DROPDOWN
const profile = document.querySelector('nav .profile');
const imgProfile = profile.querySelector('img');
const dropdownProfile = profile.querySelector('.profile-link');

imgProfile.addEventListener('click', function () {
	dropdownProfile.classList.toggle('show');
})

});