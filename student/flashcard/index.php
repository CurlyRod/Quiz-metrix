<?php
// index.php
require_once 'includes/functions.php';
$flashcardSets = getAllFlashcardSets();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flashcard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php include '../../shared-student/header.php'; ?>
</head>

<body style="padding: 0;">
    <?php
    // Check if user is logged in
    if (!isset($_SESSION['user'])) {
        header("Location: ../../landing-page/");
        exit();
    }

    $userData = $_SESSION['user'];
    $_SESSION['USER_NAME'] = $userData['displayName'];
    ?>
    <?php
      $currentPage = 'flashcard'; 

    include '../../shared-student/sidebar.php';
    include '../../shared-student/navbar.php';
    ?>
    
    <div class="container" style="margin-top: 50px; padding: 20px;">
        <header>
            <h4>My Flashcards</h4>
            <a href="create.php" class="btn " style="background-color: #6366f1; color: white;">+ Create New</a>
        </header>

        <div class="flashcard-grid">
            <?php if (count($flashcardSets) > 0): ?>
                <?php foreach ($flashcardSets as $set): ?>
                    <div class="flashcard-set">
                        <div class="set-header">
                            <h2 style="color: #6366f1"><?php echo htmlspecialchars($set['title']); ?></h2>
                            <div class="dropdown">
                                <button class="dropdown-btn">⋮</button>
                                <div class="dropdown-content">
                                    <a href="view.php?id=<?php echo $set['id']; ?>">View</a>
                                    <a href="edit.php?id=<?php echo $set['id']; ?>">Edit</a>
                                    <a href="#" class="delete-set" data-id="<?php echo $set['id']; ?>">Delete</a>
                                </div>
                            </div>
                        </div>
                        <p><?php echo $set['card_count']; ?> cards</p>
                        <p class="set-description"><?php echo htmlspecialchars($set['description']); ?></p>
                        <div class="set-footer">
                            <p class="created-date">
                                <span class="icon">📅</span> Created: <?php echo date('m/d/Y', strtotime($set['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>You don't have any flashcard sets yet.</p>
                    <a href="create.php" style="background-color: #6366f1; color: white; text-decoration: none;" class="btn-primary">Create your first set</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="toast-container"></div>

    <script src="assets/js/toast.js"></script>
    <script src="assets/js/main.js"></script>
    <?php include '../../shared-student/script.php'; ?>
</body>

</html>