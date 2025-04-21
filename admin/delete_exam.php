<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exam_id'])) {
    $exam_id = $_POST['exam_id'];
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Check if exam exists
        $stmt = $pdo->prepare("SELECT id FROM exams WHERE id = ?");
        $stmt->execute([$exam_id]);
        if (!$stmt->fetch()) {
            throw new Exception("Exam not found");
        }

        // Delete exam results if the table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'exam_results'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("DELETE FROM exam_results WHERE exam_id = ?");
            $stmt->execute([$exam_id]);
        }
        
        // Delete options if the table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'options'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("DELETE o FROM options o 
                                  INNER JOIN questions q ON o.question_id = q.id 
                                  WHERE q.exam_id = ?");
            $stmt->execute([$exam_id]);
        }
        
        // Delete questions if the table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'questions'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("DELETE FROM questions WHERE exam_id = ?");
            $stmt->execute([$exam_id]);
        }
        
        // Finally, delete the exam
        $stmt = $pdo->prepare("DELETE FROM exams WHERE id = ?");
        $stmt->execute([$exam_id]);
        
        // Commit transaction
        $pdo->commit();
        
        $_SESSION['success'] = "Exam deleted successfully!";
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = "Error deleting exam: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

// Redirect back to manage exams page
header("Location: manage_exams.php");
exit();
?> 