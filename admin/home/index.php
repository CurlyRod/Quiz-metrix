<?php
session_start();
require_once 'check_admin_session.php'; 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - User Management</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <?php include '../../shared-admin/header.php'; ?>


    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
</head>
<body class="admin-body">
    <?php include '../../shared-admin/navbar.php'; ?>

    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <h1>Dashboard</h1>
                <p>Manage users and monitor key metrics</p>
            </div>
            <div class="header-actions">
                <button class="btn-refresh" title="Refresh data">
                    <i class="bx bx-refresh"></i>
                </button>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-users">
                    <i class="bx bx-group"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Users</span>
                    <span class="stat-value" id="totalUsersStat">0</span>
                </div>
                
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-active">
                    <i class="bx bx-check-circle"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Active Users</span>
                    <span class="stat-value" id="activeUsersStat">0</span>
                </div>
                
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-inactive">
                    <i class="bx bx-x-circle"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Inactive Users</span>
                    <span class="stat-value" id="inactiveUsersStat">0</span>
                </div>
                
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Users Table -->
            <div class="card card-main">
                <div class="card-header">
                    <div class="header-left">
                        <h2>User Management</h2>
                        <span class="record-count">(<span id="totalRecords">0</span> records)</span>
                    </div>
                    <div class="search-input-wrapper">
                        <i class="bx bx-search"></i>
                        <input 
                            type="text" 
                            id="searchInput" 
                            class="search-input"
                            placeholder="Search by name or email..."
                        >
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th class="col-id">ID</th>
                                <th class="col-name">Name</th>
                                <th class="col-email">Email</th>
                                <th class="col-status">
                                    <div class="status-header">
                                        <span>Status</span>
                                        <select id="statusFilter" class="status-filter">
                                            <option value="both">● Both</option>
                                            <option value="Active">🟢 Active</option>
                                            <option value="Inactive">🔴 Inactive</option>
                                        </select>
                                    </div>
                                </th>
                                <th class="col-date">Registered</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <tr class="loading-row">
                                <td colspan="5" style="text-align: center; padding: 40px;">
                                    <div class="spinner"></div>
                                    <p>Loading users...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <div class="pagination-info">
                        Showing <span id="startRecord">1</span> – <span id="endRecord">10</span> of <span id="totalRecords">0</span>
                    </div>
                    <div class="pagination-buttons">
                        <button id="prevPage" class="btn-pagination" title="Previous page" disabled>
                            <i class="bx bx-chevron-left"></i>
                        </button>
                        <span id="pageNumber" class="page-number">1</span>
                        <button id="nextPage" class="btn-pagination" title="Next page" disabled>
                            <i class="bx bx-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Total Users Widget -->
                

                <!-- Weekly Analytics -->
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <h3>Weekly Registrations</h3>
                        <div class="month-controls">
                            <button id="prevMonth" class="btn-month" title="Previous month">
                                <i class="bx bx-chevron-left"></i>
                            </button>
                            <button id="nextMonth" class="btn-month" title="Next month">
                                <i class="bx bx-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="sidebar-body">
                        <p class="chart-month" id="chartMonth">This Month</p>
                        <div class="chart-container">
                            <canvas id="analyticsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script src="script.js"></script>
    <?php include '../../shared-admin/script.php'; ?>
</body>
</html>
