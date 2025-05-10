<?php
// api/flashcards.php
header('Content-Type: application/json');
require_once '../includes/functions.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_all_sets':
        echo json_encode(['success' => true, 'data' => getAllFlashcardSets()]);
        break;
        
    case 'get_set':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $set = getFlashcardSet($id);
        
        if ($set) {
            echo json_encode(['success' => true, 'data' => $set]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Flashcard set not found']);
        }
        break;
        
    case 'get_cards':
        $setId = isset($_GET['set_id']) ? intval($_GET['set_id']) : 0;
        echo json_encode(['success' => true, 'data' => getFlashcards($setId)]);
        break;
        
    case 'create_set':
        $data = json_decode(file_get_contents('php://input'), true);
        $title = isset($data['title']) ? $data['title'] : '';
        $description = isset($data['description']) ? $data['description'] : '';
        
        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Title is required']);
            exit;
        }
        
        $id = createFlashcardSet($title, $description);
        
        if ($id) {
            echo json_encode(['success' => true, 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create flashcard set']);
        }
        break;
        
    case 'update_set':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? intval($data['id']) : 0;
        $title = isset($data['title']) ? $data['title'] : '';
        $description = isset($data['description']) ? $data['description'] : '';
        
        if (empty($id) || empty($title)) {
            echo json_encode(['success' => false, 'message' => 'ID and title are required']);
            exit;
        }
        
        $success = updateFlashcardSet($id, $title, $description);
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update flashcard set']);
        }
        break;
        
    case 'delete_set':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? intval($data['id']) : 0;
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID is required']);
            exit;
        }
        
        $success = deleteFlashcardSet($id);
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete flashcard set']);
        }
        break;
        
    case 'create_card':
        $data = json_decode(file_get_contents('php://input'), true);
        $setId = isset($data['set_id']) ? intval($data['set_id']) : 0;
        $question = isset($data['question']) ? $data['question'] : '';
        $answer = isset($data['answer']) ? $data['answer'] : '';
        $position = isset($data['position']) ? intval($data['position']) : 0;
        
        if (empty($setId) || empty($question) || empty($answer)) {
            echo json_encode(['success' => false, 'message' => 'Set ID, question, and answer are required']);
            exit;
        }
        
        $id = createFlashcard($setId, $question, $answer, $position);
        
        if ($id) {
            echo json_encode(['success' => true, 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create flashcard']);
        }
        break;
        
    case 'update_card':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? intval($data['id']) : 0;
        $question = isset($data['question']) ? $data['question'] : '';
        $answer = isset($data['answer']) ? $data['answer'] : '';
        $position = isset($data['position']) ? intval($data['position']) : 0;
        
        if (empty($id) || empty($question) || empty($answer)) {
            echo json_encode(['success' => false, 'message' => 'ID, question, and answer are required']);
            exit;
        }
        
        $success = updateFlashcard($id, $question, $answer, $position);
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update flashcard']);
        }
        break;
        
    case 'delete_card':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? intval($data['id']) : 0;
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID is required']);
            exit;
        }
        
        $success = deleteFlashcard($id);
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete flashcard']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>