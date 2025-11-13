<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title>



  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">

  <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="../../vendor/student/home/home.css">
	<link rel="stylesheet" href="../../vendor/admin/users/users.css">
	
  <link rel="icon" type="image/x-icon" href="../../assets/img/logo/apple-touch-icon.png">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/styles.css">

  


</head>

<body>
  <?php
  session_start();
  // Check if user is logged in
  if (!isset($_SESSION['user'])) {
    header("Location: ../../landing-page/");
    exit();
  }

  require '../../middleware/Auth/UserAuthenticate.php';  
  require '../../student/home/db_connect.php'; 
  
  $userAthenticate = new UserAuthenticate($conn);
  $userData = $_SESSION['user'];
  $email = $userData['mail']; 
  $_SESSION['USER_NAME'] = $userData['displayName'];

  $getUser = $userAthenticate->GetUserLogin($email); 

  if (!$getUser['isAuthenticate']) {
      $registerNewUser = $userAthenticate->RegisterUser($email);  
  
      if ($registerNewUser['isAuthenticate']) {
          $_SESSION['USER_EMAIL'] = $registerNewUser['userinfo'][0]; 
      }
  } else {
      $_SESSION['USER_EMAIL'] = $getUser['userinfo'][0];
  }
  $currentPage = 'home'; 

  include '../../shared-student/sidebar.php';
  include '../../shared-student/navbar.php';
  ?>

  <input type="hidden" name="user-current-id" id="user-current-id">

  <div class="dashboard-grid">
    <!-- Left Column -->
    <div class="left-column">
      <!-- Calendar Card -->
      <div class="card calendar-card">
        <div class="calendar-header">
          <button class="today-button" id="todayButton" style="color: #6366f1"><u>Today</u></button>
          <div class="month-navigation">
            <button class="nav-button" id="prevMonth">
              <i class="fas fa-chevron-left"></i>
            </button>
            <span class="month-title" id="currentMonth">May 2025</span>
            <button class="nav-button" id="nextMonth">
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
          <button class="btn primary-btn" id="addEventBtn" >Add Event</button>
        </div>
        <div class="card-body p-0">
          <div id="calendar-container">
            <!-- Calendar will be generated here -->
          </div>
          <div class="card-footer">
          <a class="view-button" href="calendar.php" id="viewFullCalendar" style="color: #6366f1"><u>Full Calendar</u></a>
        </div>
        </div>
      </div>

     <!-- Recent Quizzes Section -->
      <div class="card recent-quizzes-card" style="max-width: none;">
        <div class="card-header">
          <h3> Recent Quizzes</h3>
        </div>
        <div class="card-body">
          <div class="row" id="recentQuizzes">

            <!-- Quiz cards will be generated here -->
          
          </div>
        </div>
      </div>
<!-- Recent Decks Section -->
<div class="card recent-decks-card" style="max-width: none;">
  <div class="card-header">
    <h3>Recent Decks</h3>
  </div>
  <div class="card-body">
    <div class="row" id="recentDecks">
      <!-- Deck cards will be generated here -->
    </div>
  </div>
</div>
    </div>

    <!-- Right Column -->
    <div class="right-column">
      <!-- Events Card -->
      <div class="card current-events-card">
        <div class="card-header">
          <h3><i class="fas fa-calendar-day"></i> Events</h3>
          <p id="currentDate"></p>
        </div>
        <div class="card-body" id="currentEvents">
          <!-- Current events will be displayed here -->
          <div class="empty-events">No events for this date</div>
        </div>
      </div>

      <!-- To-Do Card -->
      <div class="card todo-card">
        <div class="card-header">
          <h3><i class="fas fa-tasks"></i> To-Do List</h3>
        </div>
        <div class="card-body">
          <div class="todo-input">
            <input type="text" id="newTodoInput" placeholder="Add a new task..." maxlength="30">
            <button id="addTodoBtn" class="btn icon-btn">
              <i class="fas fa-plus"></i>
            </button>
          </div>

          <ul class="todo-list" id="todoList">
            <!-- Todo items will be generated here -->
          </ul>
        </div>
        <div class="card-footer">
          <button id="finishAllBtn" class="btn outline-btn">Finish All</button>
        </div>
      </div>
      </div>

     
  <!-- Modal for Adding/Editing Events -->
  <div class="modal" id="eventModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="eventModalTitle">Add New Event</h3>
        <button class="close-btn" data-bs-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="eventId">
        <div class="form-group">
          <label for="eventTitle">Event Title</label>
          <input type="text" id="eventTitle" placeholder="Enter event title" maxlength="30">
        </div>
        <div class="form-group">
          <label for="eventDate">Date</label>
          <input type="date" id="eventDate">
        </div>
      </div>
      <div class="modal-footer">
        <button id="deleteEventBtn" class="btn delete-btn" style="display: none;">Delete</button>
        <button id="saveEventBtn" class="btn primary-btn">Save Event</button>
      </div>
    </div>
  </div>

  
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Custom JS -->
  <script src="js/recent.js"></script>
  <script src="js/recent-decks.js"></script>
  <script src="js/calendar.js"></script>
  <script src="js/todo.js"></script>
  <!-- <script src="js/goals.js"></script> -->
  <script src="js/main.js"></script>
  <!-- <script src="js/validate-user.js"></script> -->
  <?php include '../../shared-student/script.php'; ?>


  
</body>

</html>