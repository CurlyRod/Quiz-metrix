document.addEventListener("DOMContentLoaded", () => {
  // DOM Elements
  const userTable = document.getElementById("userTableBody")
  const searchInput = document.getElementById("searchInput")
  const prevPage = document.getElementById("prevPage")
  const nextPage = document.getElementById("nextPage")
  const prevMonth = document.getElementById("prevMonth")
  const nextMonth = document.getElementById("nextMonth")
  const btnRefresh = document.querySelector(".btn-refresh")

  // Pagination variables
  const itemsPerPage = 10
  let currentPage = 1
  let totalPages = 1
  const currentMonth = new Date()
  let chartInstance = null

  // Initialize
  loadUsers()
  loadAnalytics()

  // Event listeners
  searchInput.addEventListener("input", () => {
    currentPage = 1
    loadUsers()
  })

  document.getElementById("statusFilter")?.addEventListener("change", () => {
    currentPage = 1
    loadUsers()
})

  prevPage.addEventListener("click", goToPrevPage)
  nextPage.addEventListener("click", goToNextPage)
  prevMonth.addEventListener("click", goToPrevMonth)
  nextMonth.addEventListener("click", goToNextMonth)
  btnRefresh?.addEventListener("click", () => {
    loadUsers()
    loadAnalytics()
  })

  async function loadUsers() {
    userTable.innerHTML =
      '<tr class="loading-row"><td colspan="5" style="text-align: center; padding: 40px;"><div class="spinner"></div><p>Loading users...</p></td></tr>'

    try {
      const searchTerm = searchInput.value.trim()
      const statusFilterValue = statusFilter.value
      
      const response = await fetch(
        `../api/fetch_users_with_status.php?page=${currentPage}&limit=${itemsPerPage}&search=${encodeURIComponent(searchTerm)}&status=${encodeURIComponent(statusFilterValue)}`,
      )
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      
      const text = await response.text()
      const data = JSON.parse(text)

      if (data.success) {
        totalPages = data.pagination.totalPages
        updatePaginationInfo(data)
        displayUsers(data.users)
        updateStats(data.users, data.pagination.totalRecords)
      } else {
        userTable.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--danger); padding: 40px;">${
          data.message || "Error loading users"
        }</td></tr>`
      }
    } catch (error) {
      console.error("Error:", error)
      userTable.innerHTML =
        '<tr><td colspan="5" style="text-align: center; color: var(--danger); padding: 40px;">Network error: ' + error.message + '</td></tr>'
    }
  }

  function displayUsers(users) {
    if (users.length === 0) {
      userTable.innerHTML =
        '<tr><td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 40px;">No users found</td></tr>'
      return
    }

    userTable.innerHTML = ""
    users.forEach((user) => {
      const row = document.createElement("tr")
      const isActive = user.status === "Active"

      row.innerHTML = `
                <td><strong>${user.id}</strong></td>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>
                    <select class="status-dropdown" data-user-id="${user.id}" data-user-name="${user.name}">
                        <option value="Active" ${isActive ? "selected" : ""}>🟢 Active</option>
                        <option value="Inactive" ${!isActive ? "selected" : ""}>🔴 Inactive</option>
                    </select>
                </td>
                <td>${user.date_created}</td>
            `
      userTable.appendChild(row)

      const dropdown = row.querySelector(".status-dropdown")
      dropdown.addEventListener("change", (e) => updateUserStatus(e.target, user.id))
    })
  }

  async function updateUserStatus(dropdown, userId) {
    const newStatus = dropdown.value
    const userName = dropdown.getAttribute("data-user-name")

    try {
      const formData = new FormData()
      formData.append("user_id", userId)
      formData.append("status", newStatus)

      const response = await fetch("../api/update_user_status.php", {
        method: "POST",
        body: formData,
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const text = await response.text()
      const data = JSON.parse(text)
      
      if (data.success) {
        console.log(`User ${userName} status updated to ${newStatus}`)
        dropdown.style.borderColor = "rgba(102, 126, 234, 1)"
        setTimeout(() => {
          dropdown.style.borderColor = ""
        }, 1000)
        
        // Reload users to reflect the change in stats
        loadUsers()
      } else {
        alert("Error updating status: " + data.message)
        loadUsers()
      }
    } catch (error) {
      console.error("Error:", error)
      alert("Failed to update user status: " + error.message)
      loadUsers()
    }
  }

  function updatePaginationInfo(data) {
    const startRecord = (currentPage - 1) * itemsPerPage + 1
    const endRecord = Math.min(startRecord + itemsPerPage - 1, data.pagination.totalRecords)

    document.getElementById("startRecord").textContent = startRecord
    document.getElementById("endRecord").textContent = endRecord
    document.getElementById("totalRecords").textContent = data.pagination.totalRecords
    document.getElementById("pageNumber").textContent = currentPage

    prevPage.disabled = currentPage === 1
    nextPage.disabled = currentPage === totalPages
  }

  function updateStats(users, totalUsers) {
    document.getElementById("totalUsersStat").textContent = totalUsers

    const activeCount = users.filter((u) => u.status === "Active").length
    const inactiveCount = users.filter((u) => u.status === "Inactive").length

    document.getElementById("activeUsersStat").textContent = activeCount
    document.getElementById("inactiveUsersStat").textContent = inactiveCount
  }

  function goToPrevPage(e) {
    e.preventDefault()
    if (currentPage > 1) {
      currentPage--
      loadUsers()
      window.scrollTo({ top: 0, behavior: "smooth" })
    }
  }

  function goToNextPage(e) {
    e.preventDefault()
    if (currentPage < totalPages) {
      currentPage++
      loadUsers()
      window.scrollTo({ top: 0, behavior: "smooth" })
    }
  }

  async function loadAnalytics() {
    const monthStr = currentMonth.toISOString().slice(0, 7)
    updateChartMonth()

    try {
      const response = await fetch(`../api/fetch_weekly_analytics.php?month=${monthStr}`)
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      
      const text = await response.text()
      const data = JSON.parse(text)

      if (data.success) {
        renderChart(data.data)
      }
    } catch (error) {
      console.error("Error fetching analytics:", error)
    }
  }

  function updateChartMonth() {
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
    const monthStr = monthNames[currentMonth.getMonth()] + " " + currentMonth.getFullYear()
    document.getElementById("chartMonth").textContent = monthStr
  }

  function renderChart(data) {
    const ctx = document.getElementById("analyticsChart").getContext("2d")

    if (chartInstance) {
      chartInstance.destroy()
    }

    chartInstance = new Chart(ctx, {
      type: "bar",
      data: {
        labels: data.map((d) => d.week_display),
        datasets: [
          {
            label: "Registrations",
            data: data.map((d) => d.count),
            backgroundColor: "rgba(99, 102, 241, 0.8)",
            borderColor: "rgba(102, 126, 234, 1)",
            borderRadius: 6,
            borderSkipped: false,
            fill: true,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
              title: function(context) {
                return 'Week: ' + context[0].label;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            border: {
              display: false,
            },
            grid: {
              color: "rgba(0, 0, 0, 0.05)",
            },
            title: {
              display: true,
              text: 'Number of Registrations'
            }
          },
          x: {
            border: {
              display: false,
            },
            grid: {
              display: false,
            },
            title: {
              display: true,
              text: 'Weeks'
            }
          },
        },
      },
    })
  }

  function goToPrevMonth() {
    currentMonth.setMonth(currentMonth.getMonth() - 1)
    loadAnalytics()
  }

  function goToNextMonth() {
    currentMonth.setMonth(currentMonth.getMonth() + 1)
    loadAnalytics()
  }
})