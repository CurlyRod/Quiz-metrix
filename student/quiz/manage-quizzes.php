<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <?php include '../../shared-student/header.php'; ?>
</head>

<body style="height: 100vh; background-color: #f8f9fa;">

    <?php
    include '../../shared-student/sidebar.php';
    include '../../shared-student/navbar.php';
    ?>
    <ul class="nav nav-underline " style="padding: 20px;">
        <li class="nav-item">
            <a class="nav-link " href="index.php">Create</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="manage-quizzes.php">Manage Quizzes</a>
        </li>
    </ul>


    <div class="container mt-4" >
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5>Manage Quizzes</h5>
            <a href="index.php" class="btn" style="background-color: #6366f1; color: white;">Create New Quiz</a>
        </div>

        <!-- Search Bar -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-4" style="width: 50%;">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="searchQuiz" placeholder="Search quizzes..." maxlength="30">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">Clear</button>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info d-none" id="statusMessage"></div>
        
        <div class="table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Created</th>
                        <th>Last Updated</th>
                        <th>Questions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="quizTable">
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p>Loading quizzes...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="Quiz pagination" class="mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span id="paginationInfo">Showing 1-10 of 0 quizzes</span>
                </div>
                <ul class="pagination" id="paginationControls">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" id="prevPage" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item disabled">
                        <a class="page-link" href="#" id="nextPage" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this quiz? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quiz Results Modal -->
    <div class="modal fade" id="resultsHistoryModal" tabindex="-1" aria-labelledby="resultsHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultsHistoryModalLabel">Quiz Results History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4 id="resultsQuizTitle" class="mb-3">Quiz Title</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody id="resultsTable">
                                <tr>
                                    <td colspan="3" class="text-center">Loading results...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/manage-quizzes.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="../quiz/js/script.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src=""></script>
    <?php include '../../shared-student/script.php'; ?>

</body>


</html>