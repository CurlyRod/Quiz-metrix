<?php
// includes/functions.php
require_once 'db.php';

// Flashcard Set Functions
function getAllFlashcardSets() {
    $conn = getConnection();
    $sql = "SELECT fs.*, COUNT(f.id) as card_count 
            FROM flashcard_sets fs
            LEFT JOIN flashcards f ON fs.id = f.set_id
            GROUP BY fs.id
            ORDER BY fs.created_at DESC";
    
    $result = $conn->query($sql);
    $sets = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $sets[] = $row;
        }
    }
    
    $conn->close();
    return $sets;
}

function getFlashcardSet($id) {
    $conn = getConnection();
    $sql = "SELECT * FROM flashcard_sets WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
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
    $sql = "INSERT INTO flashcard_sets (title, description) VALUES (?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $title, $description);
    $success = $stmt->execute();
    
    $id = $success ? $conn->insert_id : 0;
    
    $stmt->close();
    $conn->close();
    return $id;
}

function updateFlashcardSet($id, $title, $description) {
    $conn = getConnection();
    $sql = "UPDATE flashcard_sets SET title = ?, description = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $title, $description, $id);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    return $success;
}

function deleteFlashcardSet($id) {
    $conn = getConnection();
    $sql = "DELETE FROM flashcard_sets WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    return $success;
}

// Flashcard Functions
function getFlashcards($setId) {
    $conn = getConnection();
    $sql = "SELECT * FROM flashcards WHERE set_id = ? ORDER BY position ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $setId);
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
    $sql = "DELETE FROM flashcards WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    return $success;
}
?>