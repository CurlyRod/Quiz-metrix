<!DOCTYPE html>
<html lang="en">
	
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

  <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="../../vendor/student/home/home.css">
	<link rel="stylesheet" href="../../vendor/admin/users/users.css">
	
  <link rel="icon" type="image/x-icon" href="../../assets/img/logo/apple-touch-icon.png">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="styles.css">

<body>
	<?php include '../../shared-admin/navbar.php'; ?>	
	
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Left Column - User Cards -->
            <div class="col-lg-9">
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex ">
                        <h5 class="mb-0">STI ALABANG USERS</h5>
                        <div class="search-container">
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text bg-transparent border-end-0">
                            <i class="bx bx-search"></i></span>
                                <input type="text" id="searchInput" class="form-control" placeholder="Search User via Name or Email" aria-label="addon-wrapping" aria-describedby="addon-wrapping" style="padding: 10px 10px 10px 8px" maxlength=30>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>NAME</th>
                                        <th>EMAIL</th>
                                        <th>DATE REGISTERED</th>
                                    </tr>
                                </thead>
                                <tbody id="userTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                        <div id="paginationInfo">Showing <span id="currentPage"> 1 </span> of <span id="totalPages"> 1 </span> PAGE</div>
                        <div class="pagination-controls">
                            <button id="prevPage" class="btn">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button id="nextPage" class="btn">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Calendar and Total Users -->
            <div class="col-lg-3">
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
					</div>
					<div class="card-body p-0">
					<div id="calendar-container">
						<!-- Calendar will be generated here -->
					</div>
					</div>
				</div>

                
                <!-- Total Users Card -->
                
				<div class="card timer-card">
					<div class="card-header h3"  style="border-bottom: 1px solid #e0e0e0;">
					<h3>Total User</h3>
					</div>
					<div class="card-body">
					<div class="totalUsers">
						<span id="totalUsersCount">0</span>
					</div>
					</div>
					<div class="total-footer">
					</div>
				</div>

			</div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="script.js"></script>
	<?php include '../../shared-admin/script.php'; ?>

</body>
</html>