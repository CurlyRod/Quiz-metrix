document.addEventListener("DOMContentLoaded", () => {
  // Calendar variables
  const calendarContainer = document.getElementById("calendar-container")
  const currentMonthElement = document.getElementById("currentMonth")
  const prevMonthButton = document.getElementById("prevMonth")
  const nextMonthButton = document.getElementById("nextMonth")
  const todayButton = document.getElementById("todayButton")
  const addEventBtn = document.getElementById("addEventBtn")
  const eventModal = document.getElementById("eventModal")
  const closeBtn = document.querySelector(".close-btn")

  // Current date
  let currentDate = new Date()
  let selectedDate = new Date()

  // Initialize calendar
  renderCalendar(currentDate)

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
  })

  addEventBtn.addEventListener("click", () => {
    openEventModal(null, formatDate(selectedDate))
  })

  closeBtn.addEventListener("click", () => {
    eventModal.classList.remove("active")
  })

  document.getElementById("saveEventBtn").addEventListener("click", saveEvent)
  document.getElementById("deleteEventBtn").addEventListener("click", deleteEvent)

  // Close modal when clicking outside
  eventModal.addEventListener("click", (e) => {
    if (e.target === eventModal) {
      eventModal.classList.remove("active")
    }
  })

  // Helper function to check if a date is in the past
  function isPastDate(date) {
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    return date < today
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
    const daysOfWeek = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"]
    daysOfWeek.forEach((day) => {
      const dayHeader = document.createElement("div")
      dayHeader.className = "calendar-day-header"
      dayHeader.textContent = day
      calendarContainer.appendChild(dayHeader)
    })

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
      const dayDate = new Date(date.getFullYear(), date.getMonth() - 1, prevMonthLastDay - i)
      createDayCell(dayDate, true)
    }

    // Days from current month
    const today = new Date()
    for (let i = 1; i <= totalDays; i++) {
      const dayDate = new Date(date.getFullYear(), date.getMonth(), i)
      const isToday = dayDate.toDateString() === today.toDateString()
      createDayCell(dayDate, false, isToday)
    }

    // Days from next month
    const totalCells = 42 // 6 rows x 7 days
    const remainingCells = totalCells - (startingDay + totalDays)
    for (let i = 1; i <= remainingCells; i++) {
      const dayDate = new Date(date.getFullYear(), date.getMonth() + 1, i)
      createDayCell(dayDate, true)
    }

    // Load events for the current month
    loadEvents()
  }

  // Function to create a day cell
  function createDayCell(dayDate, isOtherMonth = false, isToday = false) {
    const dayCell = document.createElement("div")
    dayCell.className = "calendar-day"
    dayCell.dataset.date = formatDate(dayDate)

    if (isOtherMonth) {
      dayCell.classList.add("other-month")
    }

    if (isToday) {
      dayCell.classList.add("today")
    }

    if (
      dayDate.getFullYear() === selectedDate.getFullYear() &&
      dayDate.getMonth() === selectedDate.getMonth() &&
      dayDate.getDate() === selectedDate.getDate()
    ) {
      dayCell.classList.add("selected")
    }

    if (isPastDate(dayDate) && !isToday) {
      dayCell.classList.add("disabled-date")
    }

    // Day number
    const dayNumber = document.createElement("div")
    dayNumber.className = "day-number"
    dayNumber.textContent = dayDate.getDate()
    dayCell.appendChild(dayNumber)

    // Events container
    const eventsContainer = document.createElement("div")
    eventsContainer.className = "events-container"
    dayCell.appendChild(eventsContainer)

    // Add click listener for selecting date
    if (!isPastDate(dayDate) || isToday) {
      dayCell.addEventListener("click", () => {
        selectDate(dayCell, dayDate)
      })
    }

    calendarContainer.appendChild(dayCell)
  }

  // Function to select a date
  function selectDate(dayCell, dayDate) {
    document.querySelectorAll(".calendar-day.selected").forEach((day) => {
      day.classList.remove("selected")
    })

    dayCell.classList.add("selected")
    selectedDate = new Date(dayDate)
  }

  // Function to load events for all visible dates in the calendar
function loadEvents() {
    // Get all visible date elements from the calendar
    const allDateElements = document.querySelectorAll('.calendar-day[data-date]');
    const visibleDates = Array.from(allDateElements).map(day => day.dataset.date);
    
    if (visibleDates.length === 0) return;

    // Create a date range for the visible dates
    const sortedDates = visibleDates.sort();
    const startDate = sortedDates[0];
    const endDate = sortedDates[sortedDates.length - 1];

    // Fetch events for the date range
    fetch(`api/events.php?action=getEventsForDateRange&start_date=${startDate}&end_date=${endDate}`)
        .then((response) => response.json())
        .then((data) => {
            if (!data.success || !data.events) {
                console.error("Error loading events:", data.message || "No events data received");
                return;
            }

            // Group events by date
            const eventsByDate = {};
            data.events.forEach((event) => {
                const eventDate = event.event_date;
                if (!eventsByDate[eventDate]) {
                    eventsByDate[eventDate] = [];
                }
                eventsByDate[eventDate].push(event);
            });

            // Add events to all calendar cells
            for (const [date, events] of Object.entries(eventsByDate)) {
                const dayCell = document.querySelector(`.calendar-day[data-date="${date}"]`);
                if (dayCell) {
                    const eventsContainer = dayCell.querySelector(".events-container");
                    eventsContainer.innerHTML = "";

                    // Limit to 10 events per date
                    const displayEvents = events.slice(0, 10);
                    displayEvents.forEach((event) => {
                        const eventItem = document.createElement("div");
                        eventItem.className = "event-item";
                        eventItem.dataset.eventId = event.event_id;

                        const eventTitle = document.createElement("span");
                        eventTitle.className = "event-title";
                        eventTitle.textContent = event.title;

                        const deleteBtn = document.createElement("button");
                        deleteBtn.className = "event-delete-btn";
                        deleteBtn.innerHTML = '<i class="fas fa-times"></i>';
                        deleteBtn.addEventListener("click", (e) => {
                            e.stopPropagation();
                            deleteEventFromCalendar(event.event_id);
                        });

                        eventItem.appendChild(eventTitle);
                        eventItem.appendChild(deleteBtn);

                        eventItem.addEventListener("click", (e) => {
                            if (!e.target.closest(".event-delete-btn")) {
                                openEventModal(event);
                            }
                        });

                        eventsContainer.appendChild(eventItem);
                    });

                    // Show "more" indicator if there are more than 10 events
                    if (events.length > 10) {
                        const moreItem = document.createElement("div");
                        moreItem.className = "event-item has-more";
                        moreItem.textContent = `+${events.length - 10} more`;
                        eventsContainer.appendChild(moreItem);
                    }
                }
            }
        })
        .catch((error) => console.error("Error loading events:", error));
}

  // Function to open the event modal
  function openEventModal(event = null, date = null) {
    const modalTitle = document.getElementById("eventModalTitle")
    const eventIdInput = document.getElementById("eventId")
    const eventDateInput = document.getElementById("eventDate")
    const eventTitleInput = document.getElementById("eventTitle")
    const deleteButton = document.getElementById("deleteEventBtn")

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
    eventTitleInput.focus()
  }

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
          eventModal.classList.remove("active")
          renderCalendar(currentDate)
        } else {
          alert("Error: " + data.message)
        }
      })
      .catch((error) => console.error("Error saving event:", error))
  }

  // Function to delete an event
  function deleteEvent() {
    const eventId = document.getElementById("eventId").value
    if (!eventId) return

    fetch(`api/events.php?action=deleteEvent&id=${eventId}`, {
      method: "DELETE",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          eventModal.classList.remove("active")
          renderCalendar(currentDate)
        } else {
          alert("Error: " + data.message)
        }
      })
      .catch((error) => console.error("Error deleting event:", error))
  }

  // Function to delete event from calendar (inline delete)
  function deleteEventFromCalendar(eventId) {
    if (confirm("Are you sure you want to delete this event?")) {
      fetch(`api/events.php?action=deleteEvent&id=${eventId}`, {
        method: "DELETE",
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            renderCalendar(currentDate)
          } else {
            alert("Error: " + data.message)
          }
        })
        .catch((error) => console.error("Error deleting event:", error))
    }
  }

  // Helper function to format date as YYYY-MM-DD
  function formatDate(date) {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, "0")
    const day = String(date.getDate()).padStart(2, "0")
    return `${year}-${month}-${day}`
  }

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
})
