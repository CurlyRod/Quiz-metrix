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
            <input type="text" id="newTodoInput" placeholder="Add a new task...">
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

      <!-- Timer Card -->
      <div class="card timer-card">
        <div class="card-header">
          <h3><i class="fas fa-clock"></i> Study Timer</h3>
        </div>
        <div class="card-body">
          <div class="timer-tabs">
            <button class="timer-tab active" data-tab="study">Study</button>
            <button class="timer-tab" data-tab="break">Break</button>
          </div>

          <div class="timer-display">
            <span id="timerDisplay">25:00</span>
          </div>
        </div>
        <div class="timer-footer">
          <button id="startPauseBtn" class="btn primary-btn">
            <i class="fas fa-play"></i> Start
          </button>
          <div class="timer-controls">
            <button id="resetBtn" class="timer-control-btn">
              <i class="fas fa-redo"></i>
            </button>
            <button id="settingsBtn" class="timer-control-btn">
              <i class="fas fa-cog"></i>
            </button>
          </div>
        </div>
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
          <input type="text" id="eventTitle" placeholder="Enter event title">
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

  <!-- Modal for Setting Goals -->
  <div class="modal" id="goalModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Set a New Goal</h3>
        <button class="close-btn" data-bs-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="goal-sentence-label">Your Goal</label>
          <div class="goal-sentence">
            <span>Finish</span>
            <select id="setsCount" class="inline-dropdown" style="width: 50px;">
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5" selected>5</option>
              <option value="6">6</option>
              <option value="7">7</option>
              <option value="8">8</option>
              <option value="9">9</option>
              <option value="10">10</option>
            </select>
            <span id="setsLabel">set/s</span>
            <span>of</span>
            <select id="activityType" class="inline-dropdown" style="width:100px;">
              <option value="Quiz" selected>Quiz</option>
              <option value="Flashcard">Flashcard</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="goalDateInput">Target Date</label>
          <input type="date" id="goalDateInput" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button id="saveGoalBtn" class="btn primary-btn">Save Goal</button>
      </div>
    </div>
  </div>

  <!-- Modal for Timer Settings -->
  <div class="modal" id="timerModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Timer Settings</h3>
        <button class="close-btn" data-bs-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="studyTimeInput">Study Time (minutes)</label>
          <div class="slider-container">
            <input type="range" id="studyTimeInput" min="10" max="120" step="5" value="25">
            <span id="studyTimeValue">25</span>
          </div>
        </div>
        <div class="form-group">
          <label for="breakTimeInput">Break Time (minutes)</label>
          <div class="slider-container">
            <input type="range" id="breakTimeInput" min="5" max="30" step="1" value="5">
            <span id="breakTimeValue">5</span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button id="saveTimerBtn" class="btn primary-btn">Save Settings</button>
      </div>
    </div>
  </div>
 
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Custom JS -->
  <script src="js/recent.js"></script>

  <script src="js/calendar.js"></script>
  <script src="js/todo.js"></script>
  <!-- <script src="js/goals.js"></script> -->
  <script src="js/timer.js"></script>
  <script src="js/main.js"></script>
  <!-- <script src="js/validate-user.js"></script> -->
  <?php include '../../shared-student/script.php'; ?>

  
</body>

</html>