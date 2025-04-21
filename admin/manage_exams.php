<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Get all exams
try {
    $stmt = $pdo->query("SELECT e.*, 
                                (SELECT COUNT(*) FROM questions q WHERE q.exam_id = e.id) as question_count,
                                (SELECT COUNT(*) FROM exam_results er WHERE er.exam_id = e.id) as attempt_count 
                         FROM exams e 
                         ORDER BY e.created_at DESC");
    $exams = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching exams: " . $e->getMessage();
    $exams = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exams - MCQ Exam System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>MCQ Exam System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user me-2"></i><?= htmlspecialchars($_SESSION['username']) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2><i class="fas fa-cog me-2"></i>Manage Exams</h2>
                    <a href="create_exam.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Create New Exam
                    </a>
                </div>
            </div>
        </div>

        <?php if (empty($exams)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h3>No Exams Found</h3>
                    <p class="text-muted">Start by creating a new exam.</p>
                    <a href="create_exam.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Create New Exam
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($exams as $exam): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0"><?= htmlspecialchars($exam['title']) ?></h5>
                                    <span class="badge bg-primary">
                                        <i class="fas fa-clock me-1"></i><?= $exam['duration'] ?> min
                                    </span>
                                </div>
                                <p class="card-text text-muted"><?= htmlspecialchars($exam['description']) ?></p>
                                <div class="mb-3">
                                    <small class="text-muted me-3">
                                        <i class="fas fa-percentage me-1"></i>Passing Score: <?= $exam['passing_score'] ?>%
                                    </small>
                                    <small class="text-muted me-3">
                                        <i class="fas fa-question-circle me-1"></i>Questions: <?= $exam['question_count'] ?>
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1"></i>Attempts: <?= $exam['attempt_count'] ?>
                                    </small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>Created: <?= date('M d, Y', strtotime($exam['created_at'])) ?>
                                    </small>
                                    <div class="btn-group">
                                        <a href="edit_exam.php?id=<?= $exam['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="view_exam.php?id=<?= $exam['id'] ?>" class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                onclick="deleteExam(<?= $exam['id'] ?>, '<?= htmlspecialchars($exam['title']) ?>', <?= $exam['attempt_count'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the exam "<strong><span id="examTitle"></span></strong>"?</p>
                    <div id="warningText" class="alert alert-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This exam has been attempted by students. Deleting it will also remove all exam results.
                    </div>
                    <p class="text-danger">
                        <small><i class="fas fa-info-circle me-1"></i>This action cannot be undone.</small>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <form id="deleteForm" action="delete_exam.php" method="POST" class="d-inline">
                        <input type="hidden" name="exam_id" id="examId">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteExam(examId, examTitle, attemptCount) {
            document.getElementById('examId').value = examId;
            document.getElementById('examTitle').textContent = examTitle;
            document.getElementById('warningText').style.display = attemptCount > 0 ? 'block' : 'none';
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html> 