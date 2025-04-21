<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';
$exam = null;

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
} catch (PDOException $e) {
    $error = "Error fetching exam: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $duration = intval($_POST['duration']);
    $passing_score = intval($_POST['passing_score']);
    
    try {
        // Validate passing_score
        if ($passing_score < 0 || $passing_score > 100) {
            throw new Exception("Passing score must be between 0 and 100");
        }
        
        $stmt = $pdo->prepare("UPDATE exams SET title = ?, description = ?, duration = ?, passing_score = ? WHERE id = ?");
        if (!$stmt->execute([$title, $description, $duration, $passing_score, $exam_id])) {
            throw new Exception("Failed to update exam");
        }
        $success = "Exam updated successfully!";
        
        // Refresh exam data
        $stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
        $stmt->execute([$exam_id]);
        $exam = $stmt->fetch();
        
    } catch (Exception $e) {
        $error = "Error updating exam: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Exam - MCQ Exam System</title>
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
                            <i class="fas fa-user me-2"></i><?= htmlspecialchars($_SESSION['username'] ?? '') ?>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-header bg-primary text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">
                                <i class="fas fa-edit me-2"></i>Edit Exam
                            </h3>
                            <a href="manage_exams.php" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-2"></i>Back to Exams
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?= $success ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="title" class="form-label">Exam Title</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-heading"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="title" 
                                           name="title" 
                                           value="<?= htmlspecialchars($exam['title'] ?? '') ?>" 
                                           required>
                                </div>
                                <div class="invalid-feedback">Please enter an exam title.</div>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label">Description</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-align-left"></i>
                                    </span>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="3" 
                                              required><?= htmlspecialchars($exam['description'] ?? '') ?></textarea>
                                </div>
                                <div class="invalid-feedback">Please enter a description.</div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="duration" class="form-label">Duration (minutes)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-clock"></i>
                                        </span>
                                        <input type="number" 
                                               class="form-control" 
                                               id="duration" 
                                               name="duration" 
                                               value="<?= intval($exam['duration'] ?? 0) ?>" 
                                               min="1" 
                                               required>
                                    </div>
                                    <div class="invalid-feedback">Please enter a valid duration.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="passing_score" class="form-label">Passing Score (%)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-percentage"></i>
                                        </span>
                                        <input type="number" 
                                               class="form-control" 
                                               id="passing_score" 
                                               name="passing_score" 
                                               value="<?= intval($exam['passing_score'] ?? 0) ?>" 
                                               min="0" 
                                               max="100" 
                                               required>
                                    </div>
                                    <div class="invalid-feedback">Please enter a valid passing score.</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="manage_exams.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Exams
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Exam
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html> 