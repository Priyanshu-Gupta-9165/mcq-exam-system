<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Check if exam ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_exams.php");
    exit();
}

$exam_id = $_GET['id'];

// Fetch exam details
try {
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
    $stmt->execute([$exam_id]);
    $exam = $stmt->fetch();
    
    if (!$exam) {
        $_SESSION['error'] = "Exam not found.";
        header("Location: manage_exams.php");
        exit();
    }
    
    // Fetch questions for this exam
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE exam_id = ? ORDER BY id");
    $stmt->execute([$exam_id]);
    $questions = $stmt->fetchAll();
    
    // Count total questions
    $total_questions = count($questions);
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching exam: " . $e->getMessage();
    header("Location: manage_exams.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Exam - MCQ Exam System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php"><i class="fas fa-graduation-cap me-2"></i>MCQ Exam System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link"><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-lg border-0 rounded-lg mb-4">
                    <div class="card-header bg-primary text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0"><i class="fas fa-file-alt me-2"></i>Exam Details</h3>
                            <div>
                                <a href="edit_exam.php?id=<?php echo $exam_id; ?>" class="btn btn-light btn-sm">
                                    <i class="fas fa-edit me-2"></i>Edit Exam
                                </a>
                                <a href="manage_exams.php" class="btn btn-light btn-sm ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Exams
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="mb-3"><?php echo htmlspecialchars($exam['title']); ?></h4>
                                <p class="text-muted"><?php echo htmlspecialchars($exam['description']); ?></p>
                                
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="card bg-light mb-3">
                                            <div class="card-body">
                                                <h6 class="card-title text-muted"><i class="fas fa-clock me-2"></i>Duration</h6>
                                                <p class="card-text h4"><?php echo $exam['duration']; ?> minutes</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-light mb-3">
                                            <div class="card-body">
                                                <h6 class="card-title text-muted"><i class="fas fa-percentage me-2"></i>Passing Score</h6>
                                                <p class="card-text h4"><?php echo $exam['passing_score']; ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Exam Information</h5>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-hashtag me-2"></i>Exam ID</span>
                                                <span class="badge bg-primary"><?php echo $exam['id']; ?></span>
                                            </li>
                                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-question-circle me-2"></i>Total Questions</span>
                                                <span class="badge bg-primary"><?php echo $total_questions; ?></span>
                                            </li>
                                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-calendar me-2"></i>Created On</span>
                                                <span><?php echo date('M d, Y', strtotime($exam['created_at'])); ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Questions Section -->
                <div class="card shadow-lg border-0 rounded-lg mt-4">
                    <div class="card-header bg-primary text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0"><i class="fas fa-question-circle me-2"></i>Questions</h3>
                            <a href="add_question.php?exam_id=<?= $exam_id ?>" class="btn btn-light btn-sm">
                                <i class="fas fa-plus-circle me-2"></i>Add Question
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php if (empty($questions)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                <h4>No Questions Added Yet</h4>
                                <p class="text-muted">This exam doesn't have any questions. Add some questions to make it ready for students.</p>
                                <a href="add_question.php?exam_id=<?= $exam_id ?>" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-2"></i>Add First Question
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="accordion" id="questionsAccordion">
                                <?php foreach ($questions as $index => $question): ?>
                                    <div class="accordion-item border rounded mb-3">
                                        <h2 class="accordion-header position-relative" id="heading<?= $question['id'] ?>">
                                            <div class="position-absolute end-0 top-50 translate-middle-y me-2 z-1">
                                                <a href="edit_question.php?id=<?= $question['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary me-2"
                                                   onclick="event.stopPropagation();">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="event.stopPropagation(); deleteQuestion(<?= $question['id'] ?>);">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?> pe-5" 
                                                    type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#collapse<?= $question['id'] ?>">
                                                Question <?= $index + 1 ?>
                                            </button>
                                        </h2>
                                        <div id="collapse<?= $question['id'] ?>" 
                                             class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" 
                                             data-bs-parent="#questionsAccordion">
                                            <div class="accordion-body">
                                                <p class="fw-bold mb-3"><?= htmlspecialchars($question['question_text']) ?></p>
                                                
                                                <?php 
                                                // Fetch options for this question
                                                $stmt = $pdo->prepare("SELECT * FROM options WHERE question_id = ? ORDER BY id");
                                                $stmt->execute([$question['id']]);
                                                $options = $stmt->fetchAll();
                                                ?>
                                                
                                                <div class="list-group">
                                                    <?php foreach ($options as $option): ?>
                                                        <div class="list-group-item <?= $option['is_correct'] ? 'list-group-item-success' : '' ?> d-flex justify-content-between align-items-center">
                                                            <span class="text-break"><?= htmlspecialchars($option['option_text']) ?></span>
                                                            <?php if ($option['is_correct']): ?>
                                                                <span class="badge bg-success ms-2 flex-shrink-0">
                                                                    <i class="fas fa-check me-1"></i>Correct
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Question Modal -->
    <div class="modal fade" id="deleteQuestionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this question?</p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteQuestionForm" action="delete_question.php" method="POST" class="d-inline">
                        <input type="hidden" name="question_id" id="questionId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteQuestion(questionId) {
            document.getElementById('questionId').value = questionId;
            new bootstrap.Modal(document.getElementById('deleteQuestionModal')).show();
        }
    </script>

    <style>
    .accordion-button::after {
        margin-left: 1rem;
    }
    .accordion-item {
        overflow: hidden;
    }
    .list-group-item {
        word-break: break-word;
    }
    </style>
</body>
</html> 