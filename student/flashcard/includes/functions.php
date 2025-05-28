<?php
// includes/functions.php
require_once 'db.php';
session_start();

// Flashcard Set Functions
function getAllFlashcardSets() {
    $conn = getConnection();
    
    // Get user ID from session
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }
    
    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

    // Prepare and execute query to get user ID
    $stmt = $conn->prepare("SELECT id FROM user_credential WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Execution error: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
    } else {
        throw new Exception('User not found');
    }
    $stmt->close();
    
    $sql = "SELECT fs.*, COUNT(f.id) as card_count 
            FROM flashcard_sets fs
            LEFT JOIN flashcards f ON fs.id = f.set_id
            WHERE fs.user_id = ?
            GROUP BY fs.id
            ORDER BY fs.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $sets = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $sets[] = $row;
        }
    }
    
    $stmt->close();
    $conn->close();
    return $sets;
}

function getFlashcardSet($id) {
    $conn = getConnection();
    
    // Get user ID from session
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }
    
    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

    // Prepare and execute query to get user ID
    $stmt = $conn->prepare("SELECT id FROM user_credential WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Execution error: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
    } else {
        throw new Exception('User not found');
    }
    $stmt->close();
    
    $sql = "SELECT * FROM flashcard_sets WHERE id = ? AND user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $set = null;
    
    if ($result->num_rows > 0) {
        $set = $result->fetch_assoc();
    }
    
    $stmt->close();
    $conn->close();
    return $set;
}

function createFlashcardSet($title, $description) {
    $conn = getConnection();
    
    // Get user ID from session
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }
    
    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

    // Prepare and execute query to get user ID
    $stmt = $conn->prepare("SELECT id FROM user_credential WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Execution error: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
    } else {
        throw new Exception('User not found');
    }
    $stmt->close();
    
    $sql = "INSERT INTO flashcard_sets (title, description, user_id) VALUES (?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $title, $description, $user_id);
    $success = $stmt->execute();
    
    $id = $success ? $conn->insert_id : 0;
    
    $stmt->close();
    $conn->close();
    return $id;
}

function updateFlashcardSet($id, $title, $description) {
    $conn = getConnection();
    
    // Get user ID from session
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }
    
    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

    // Prepare and execute query to get user ID
    $stmt = $conn->prepare("SELECT id FROM user_credential WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Execution error: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
    } else {
        throw new Exception('User not found');
    }
    $stmt->close();
    
    $sql = "UPDATE flashcard_sets SET title = ?, description = ? WHERE id = ? AND user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $title, $description, $id, $user_id);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    return $success;
}

function deleteFlashcardSet($id) {
    $conn = getConnection();
    
    // Get user ID from session
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }
    
    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

    // Prepare and execute query to get user ID
    $stmt = $conn->prepare("SELECT id FROM user_credential WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Execution error: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
    } else {
        throw new Exception('User not found');
    }
    $stmt->close();
    
    $sql = "DELETE FROM flashcard_sets WHERE id = ? AND user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    return $success;
}

// Flashcard Functions
function getFlashcards($setId) {
    $conn = getConnection();
    
    // Get user ID from session
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }
    
    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

    // Prepare and execute query to get user ID
    $stmt = $conn->prepare("SELECT id FROM user_credential WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Execution error: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
    } else {
        throw new Exception('User not found');
    }
    $stmt->close();
    
    $sql = "SELECT f.* FROM flashcards f
            JOIN flashcard_sets fs ON f.set_id = fs.id
            WHERE f.set_id = ? AND fs.user_id = ?
            ORDER BY f.position ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $setId, $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $cards = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $cards[] = $row;
        }
    }
    
    $stmt->close();
    $conn->close();
    return $cards;
}

function createFlashcard($setId, $question, $answer, $position) {
    $conn = getConnection();
    
    // Get user ID from session
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }
    
    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

    // Prepare and execute query to get user ID
    $stmt = $conn->prepare("SELECT id FROM user_credential WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Execution error: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
    } else {
        throw new Exception('User not found');
    }
    $stmt->close();
    
    // Verify the set belongs to the user
    $check_stmt = $conn->prepare("SELECT id FROM flashcard_sets WHERE id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $setId, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $check_stmt->close();
        $conn->close();
        throw new Exception('Flashcard set not found or not owned by user');
    }
    $check_stmt->close();
    
    $sql = "INSERT INTO flashcards (set_id, question, answer, position) VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $setId, $question, $answer, $position);
    $success = $stmt->execute();
    
    $id = $success ? $conn->insert_id : 0;
    
    $stmt->close();
    $conn->close();
    return $id;
}

function updateFlashcard($id, $question, $answer, $position) {
    $conn = getConnection();
    
    // Get user ID from session
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }
    
    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

    // Prepare and execute query to get user ID
    $stmt = $conn->prepare("SELECT id FROM user_credential WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Execution error: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
    } else {
        throw new Exception('User not found');
    }
    $stmt->close();
    
    // Verify the flashcard belongs to the user
    $check_stmt = $conn->prepare("SELECT f.id FROM flashcards f
                                JOIN flashcard_sets fs ON f.set_id = fs.id
                                WHERE f.id = ? AND fs.user_id = ?");
    $check_stmt->bind_param("ii", $id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $check_stmt->close();
        $conn->close();
        throw new Exception('Flashcard not found or not owned by user');
    }
    $check_stmt->close();
    
    $sql = "UPDATE flashcards SET question = ?, answer = ?, position = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $question, $answer, $position, $id);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    return $success;
}

function deleteFlashcard($id) {
    $conn = getConnection();
    
    // Get user ID from session
    if (!isset($_SESSION['USER_EMAIL'])) {
        throw new Exception('User not authenticated');
    }
    
    $email = $_SESSION['USER_EMAIL'];
    $user_id = null;

    // Prepare and execute query to get user ID
    $stmt = $conn->prepare("SELECT id FROM user_credential WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Execution error: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
    } else {
        throw new Exception('User not found');
    }
    $stmt->close();
    
    // Verify the flashcard belongs to the user
    $check_stmt = $conn->prepare("SELECT f.id FROM flashcards f
                                JOIN flashcard_sets fs ON f.set_id = fs.id
                                WHERE f.id = ? AND fs.user_id = ?");
    $check_stmt->bind_param("ii", $id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $check_stmt->close();
        $conn->close();
        throw new Exception('Flashcard not found or not owned by user');
    }
    $check_stmt->close();
    
    $sql = "DELETE FROM flashcards WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    return $success;
}
?>