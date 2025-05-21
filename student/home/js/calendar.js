document.addEventListener("DOMContentLoaded", () => {
    // Calendar variables
    const calendarContainer = document.getElementById("calendar-container")
    const currentMonthElement = document.getElementById("currentMonth")
    const prevMonthButton = document.getElementById("prevMonth")
    const nextMonthButton = document.getElementById("nextMonth")
    const todayButton = document.getElementById("todayButton")
    const currentDateElement = document.getElementById("currentDate")
  
    // Current date
    let currentDate = new Date()
    let selectedDate = new Date()
    
    // Helper function to check if a date is in the past (before today)
    function isPastDate(date) {
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Reset time to compare dates only
        return date < today;
    }

    // Initialize calendar
    renderCalendar(currentDate)
    updateCurrentDateDisplay()
    loadEventsForDate(formatDate(selectedDate))
  
    // Event listeners for navigation
    prevMonthButton.addEventListener("click", () => {
      currentDate.setMonth(currentDate.getMonth() - 1)
      renderCalendar(currentDate)
    })
  
    nextMonthButton.addEventListener("click", () => {
      currentDate.setMonth(currentDate.getMonth() + 1)
      renderCalendar(currentDate)
    })
  
    todayButton.addEventListener("click", () => {
      currentDate = new Date()
      selectedDate = new Date()
      renderCalendar(currentDate)
      updateCurrentDateDisplay()
      loadEventsForDate(formatDate(selectedDate))
    })
  
    // Function to update current date display
    function updateCurrentDateDisplay() {
      const options = { weekday: "long", year: "numeric", month: "long", day: "numeric" }
      currentDateElement.textContent = selectedDate.toLocaleDateString("en-US", options)
    }
  
    // Function to render the calendar
    function renderCalendar(date) {
      // Update month title
      const monthNames = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
      ]
      currentMonthElement.textContent = `${monthNames[date.getMonth()]} ${date.getFullYear()}`
  
      // Clear calendar container
      calendarContainer.innerHTML = ""
  
      // Create day headers
      const dayHeaders = document.createElement("div")
      dayHeaders.className = "calendar-grid"
  
      const daysOfWeek = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"]
      daysOfWeek.forEach((day) => {
        const dayHeader = document.createElement("div")
        dayHeader.className = "calendar-day-header"
        dayHeader.textContent = day
        dayHeaders.appendChild(dayHeader)
      })
  
      calendarContainer.appendChild(dayHeaders)
  
      // Create calendar days
      const calendarDays = document.createElement("div")
      calendarDays.className = "calendar-grid"
  
      // Get first day of month
      const firstDay = new Date(date.getFullYear(), date.getMonth(), 1)
      const startingDay = firstDay.getDay()
  
      // Get last day of month
      const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0)
      const totalDays = lastDay.getDate()
  
      // Get last day of previous month
      const prevMonthLastDay = new Date(date.getFullYear(), date.getMonth(), 0).getDate()
  
      // Days from previous month
      for (let i = startingDay - 1; i >= 0; i--) {
        const day = document.createElement("div")
        day.className = "calendar-day other-month"
        day.textContent = prevMonthLastDay - i
  
        // Create a date object for this day
        const dayDate = new Date(date.getFullYear(), date.getMonth() - 1, prevMonthLastDay - i)
        day.dataset.date = formatDate(dayDate)
        
        // Disable past dates
        if (isPastDate(dayDate)) {
            day.classList.add("disabled-date")
            day.style.pointerEvents = "none";
            day.style.opacity = "0.5";
        } else {
            day.addEventListener("click", function () {
              selectDate(this)
            })
        }
  
        calendarDays.appendChild(day)
      }
  
      // Days from current month
      const today = new Date()
      for (let i = 1; i <= totalDays; i++) {
        const day = document.createElement("div")
        day.className = "calendar-day"
        day.textContent = i
  
        // Create a date object for this day
        const dayDate = new Date(date.getFullYear(), date.getMonth(), i)
        day.dataset.date = formatDate(dayDate)
  
        // Check if this day is today
        if (date.getFullYear() === today.getFullYear() && date.getMonth() === today.getMonth() && i === today.getDate()) {
          day.classList.add("today")
        }
  
        // Check if this day is selected
        if (
          date.getFullYear() === selectedDate.getFullYear() &&
          date.getMonth() === selectedDate.getMonth() &&
          i === selectedDate.getDate()
        ) {
          day.classList.add("selected")
        }
        
        // Disable past dates
        if (isPastDate(dayDate)) {
            day.classList.add("disabled-date")
            day.style.pointerEvents = "none";
            day.style.opacity = "0.5";
        } else {
            day.addEventListener("click", function () {
              selectDate(this)
            })
        }
  
        calendarDays.appendChild(day)
      }
  
      // Days from next month
      const totalCells = 42 // 6 rows x 7 days
      const remainingCells = totalCells - (startingDay + totalDays)
  
      for (let i = 1; i <= remainingCells; i++) {
        const day = document.createElement("div")
        day.className = "calendar-day other-month"
        day.textContent = i
  
        // Create a date object for this day
        const dayDate = new Date(date.getFullYear(), date.getMonth() + 1, i)
        day.dataset.date = formatDate(dayDate)
        
        // Disable past dates
        if (isPastDate(dayDate)) {
            day.classList.add("disabled-date")
            day.style.pointerEvents = "none";
            day.style.opacity = "0.5";
        } else {
            day.addEventListener("click", function () {
              selectDate(this)
            })
        }
  
        calendarDays.appendChild(day)
      }
  
      calendarContainer.appendChild(calendarDays)
  
      // Load events for the current month
      loadEvents()
    }
  
    // Function to select a date
    function selectDate(dayElement) {
      // Remove selected class from all days
      document.querySelectorAll(".calendar-day").forEach((day) => {
        day.classList.remove("selected")
      })
  
      // Add selected class to clicked day
      dayElement.classList.add("selected")
  
      // Update selected date
      selectedDate = new Date(dayElement.dataset.date)
      updateCurrentDateDisplay()
  
      // Load events for selected date
      loadEventsForDate(dayElement.dataset.date)
    }
  
    todayButton.addEventListener("click", () => {
      currentDate = new Date()
      selectedDate = new Date()
      renderCalendar(currentDate)
      updateCurrentDateDisplay()
      loadEventsForDate(formatDate(selectedDate))
    })
  
    // Function to update current date display
    function updateCurrentDateDisplay() {
      const options = { weekday: "long", year: "numeric", month: "long", day: "numeric" }
      currentDateElement.textContent = selectedDate.toLocaleDateString("en-US", options)
    }
  
    // Function to render the calendar
    function renderCalendar(date) {
      // Update month title
      const monthNames = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
      ]
      currentMonthElement.textContent = `${monthNames[date.getMonth()]} ${date.getFullYear()}`
  
      // Clear calendar container
      calendarContainer.innerHTML = ""
  
      // Create day headers
      const dayHeaders = document.createElement("div")
      dayHeaders.className = "calendar-grid"
  
      const daysOfWeek = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"]
      daysOfWeek.forEach((day) => {
        const dayHeader = document.createElement("div")
        dayHeader.className = "calendar-day-header"
        dayHeader.textContent = day
        dayHeaders.appendChild(dayHeader)
      })
  
      calendarContainer.appendChild(dayHeaders)
  
      // Create calendar days
      const calendarDays = document.createElement("div")
      calendarDays.className = "calendar-grid"
  
      // Get first day of month
      const firstDay = new Date(date.getFullYear(), date.getMonth(), 1)
      const startingDay = firstDay.getDay()
  
      // Get last day of month
      const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0)
      const totalDays = lastDay.getDate()
  
      // Get last day of previous month
      const prevMonthLastDay = new Date(date.getFullYear(), date.getMonth(), 0).getDate()
  
      // Days from previous month
      for (let i = startingDay - 1; i >= 0; i--) {
        const day = document.createElement("div")
        day.className = "calendar-day other-month"
        day.textContent = prevMonthLastDay - i
  
        // Create a date object for this day
        const dayDate = new Date(date.getFullYear(), date.getMonth() - 1, prevMonthLastDay - i)
        day.dataset.date = formatDate(dayDate)
  
        day.addEventListener("click", function () {
          selectDate(this)
        })
  
        calendarDays.appendChild(day)
      }
  
      // Days from current month
      const today = new Date()
      for (let i = 1; i <= totalDays; i++) {
        const day = document.createElement("div")
        day.className = "calendar-day"
        day.textContent = i
  
        // Create a date object for this day
        const dayDate = new Date(date.getFullYear(), date.getMonth(), i)
        day.dataset.date = formatDate(dayDate)
  
        // Check if this day is today
        if (date.getFullYear() === today.getFullYear() && date.getMonth() === today.getMonth() && i === today.getDate()) {
          day.classList.add("today")
        }
  
        // Check if this day is selected
        if (
          date.getFullYear() === selectedDate.getFullYear() &&
          date.getMonth() === selectedDate.getMonth() &&
          i === selectedDate.getDate()
        ) {
          day.classList.add("selected")
        }
  
        day.addEventListener("click", function () {
          selectDate(this)
        })
  
        calendarDays.appendChild(day)
      }
  
      // Days from next month
      const totalCells = 42 // 6 rows x 7 days
      const remainingCells = totalCells - (startingDay + totalDays)
  
      for (let i = 1; i <= remainingCells; i++) {
        const day = document.createElement("div")
        day.className = "calendar-day other-month"
        day.textContent = i
  
        // Create a date object for this day
        const dayDate = new Date(date.getFullYear(), date.getMonth() + 1, i)
        day.dataset.date = formatDate(dayDate)
  
        day.addEventListener("click", function () {
          selectDate(this)
        })
  
        calendarDays.appendChild(day)
      }
  
      calendarContainer.appendChild(calendarDays)
  
      // Load events for the current month
      loadEvents()
    }
  
    // Function to select a date
    function selectDate(dayElement) {
      // Remove selected class from all days
      document.querySelectorAll(".calendar-day").forEach((day) => {
        day.classList.remove("selected")
      })
  
      // Add selected class to clicked day
      dayElement.classList.add("selected")
  
      // Update selected date
      selectedDate = new Date(dayElement.dataset.date)
      updateCurrentDateDisplay()
  
      // Load events for selected date
      loadEventsForDate(dayElement.dataset.date)
    }
  
    // Function to load events
    function loadEvents() {
      fetch("api/events.php?action=getMonthEvents")
        .then((response) => response.json())
        .then((data) => {
          // Group events by date
          const eventsByDate = {}
  
          data.forEach((event) => {
            const eventDate = event.event_date
            if (!eventsByDate[eventDate]) {
              eventsByDate[eventDate] = []
            }
            eventsByDate[eventDate].push(event)
          })
  
          // Add event indicators and count badges to calendar days
          for (const [date, events] of Object.entries(eventsByDate)) {
            const dayElement = document.querySelector(`.calendar-day[data-date="${date}"]`)
  
            if (dayElement) {
              dayElement.classList.add("has-event")
  
              // Add event count badge
              const eventCount = events.length
              if (eventCount > 0) {
                const badge = document.createElement("span")
                badge.className = "event-count-badge"
                badge.textContent = eventCount
                dayElement.appendChild(badge)
              }
            }
          }
        })
        .catch((error) => console.error("Error loading events:", error))
    }
  
    // Function to load events for a specific date
    function loadEventsForDate(dateStr) {
      fetch(`api/events.php?action=getEventsForDate&date=${dateStr}`)
        .then((response) => response.json())
        .then((data) => {
          const eventsContainer = document.getElementById("currentEvents")
          eventsContainer.innerHTML = ""
  
          if (data.length === 0) {
            eventsContainer.innerHTML = '<div class="empty-events">No events for this date</div>'
            return
          }
  
          data.forEach((event) => {
            const eventItem = createEventItem(event)
            eventsContainer.appendChild(eventItem)
          })
        })
        .catch((error) => console.error("Error loading events for date:", error))
    }
  
    // Function to create event item
    function createEventItem(event) {
      const eventDate = new Date(event.event_date)
      const formattedDate = eventDate.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
      })
  
      const eventItem = document.createElement("div")
      eventItem.className = "event-item"
      eventItem.dataset.eventId = event.event_id
  
      // Determine event type based on date
      const today = new Date()
      today.setHours(0, 0, 0, 0)
  
      const eventDateOnly = new Date(eventDate)
      eventDateOnly.setHours(0, 0, 0, 0)
  
      let eventType = "upcoming"
      if (eventDateOnly.getTime() < today.getTime()) {
        eventType = "past"
      } else if (eventDateOnly.getTime() === today.getTime()) {
        eventType = "current"
      }
  
      eventItem.innerHTML = `
              <div class="event-details">
                  <h4>${event.title}</h4>
                  <p>${formattedDate} <span class="event-badge ${eventType}">${eventType}</span></p>
              </div>
              <button class="delete-btn" data-event-id="${event.event_id}">
                  <i class="fas fa-times"></i>
              </button>
          `
  
      // Add event listeners
      eventItem.addEventListener("click", (e) => {
        if (!e.target.closest(".delete-btn")) {
          openEventModal(event)
        }
      })
  
      const deleteBtn = eventItem.querySelector(".delete-btn")
      deleteBtn.addEventListener("click", (e) => {
        e.stopPropagation()
        deleteEvent(event.event_id)
      })
  
      return eventItem
    }
  
    // Function to open the event modal
    function openEventModal(event = null, date = null) {
      const eventModal = document.getElementById("eventModal")
      const modalTitle = document.getElementById("eventModalTitle")
      const eventIdInput = document.getElementById("eventId")
      const eventDateInput = document.getElementById("eventDate")
      const eventTitleInput = document.getElementById("eventTitle")
      const deleteButton = document.getElementById("deleteEventBtn")
  
      // Reset form
      eventTitleInput.value = ""
  
      if (event) {
        // Edit existing event
        modalTitle.textContent = "Edit Event"
        eventIdInput.value = event.event_id
        eventDateInput.value = event.event_date
        eventTitleInput.value = event.title
        deleteButton.style.display = "block"
      } else {
        // Add new event
        modalTitle.textContent = "Add New Event"
        eventIdInput.value = ""
        eventDateInput.value = date || formatDate(selectedDate)
        eventTitleInput.value = ""
        deleteButton.style.display = "none"
      }
  
      eventModal.classList.add("active")
    }
  
    // Event listener for Add Event button
    document.getElementById("addEventBtn").addEventListener("click", () => {
      openEventModal(null, formatDate(selectedDate))
    })
  
    // Event listener for Save Event button
    document.getElementById("saveEventBtn").addEventListener("click", () => {
      saveEvent()
    })
  
    // Event listener for Delete Event button
    document.getElementById("deleteEventBtn").addEventListener("click", () => {
      deleteEvent()
    })
  
    // Event listener for Close buttons
    document.querySelectorAll(".close-btn").forEach((btn) => {
      btn.addEventListener("click", function () {
        const modal = this.closest(".modal")
        modal.classList.remove("active")
      })
    })
  
    // Function to save an event
    function saveEvent() {
      const eventId = document.getElementById("eventId").value
      const eventDate = document.getElementById("eventDate").value
      const eventTitle = document.getElementById("eventTitle").value
  
      if (!eventTitle || !eventDate) {
        alert("Please fill in all required fields")
        return
      }
  
      const eventData = {
        event_id: eventId,
        title: eventTitle,
        event_date: eventDate,
      }
  
      const action = eventId ? "updateEvent" : "addEvent"
  
      fetch(`api/events.php?action=${action}`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(eventData),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            document.getElementById("eventModal").classList.remove("active")
            renderCalendar(currentDate)
            loadEventsForDate(eventDate)
         
          } else {
            alert("Error: " + data.message)
          }
        })
        .catch((error) => console.error("Error saving event:", error))
    }
  
    // Function to delete an event
    function deleteEvent(eventId = null) {
      if (!eventId) {
        eventId = document.getElementById("eventId").value
      }
  
      if (!eventId) {
        return
      }
  
      fetch(`api/events.php?action=deleteEvent&id=${eventId}`, {
        method: "DELETE",
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            document.getElementById("eventModal").classList.remove("active")
            renderCalendar(currentDate)
  
            // Reload events for the current date
            loadEventsForDate(formatDate(selectedDate))
          } else {
            alert("Error: " + data.message)
          }
        })
        .catch((error) => console.error("Error deleting event:", error))
    }
  
  
    // Helper function to format date as YYYY-MM-DD
    function formatDate(date) {
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, "0")
      const day = String(date.getDate()).padStart(2, "0")
      return `${year}-${month}-${day}`
    }
  })
  