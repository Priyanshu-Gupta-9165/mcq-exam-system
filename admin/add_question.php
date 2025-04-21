<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';
$exam_id = isset($_GET['exam_id']) ? $_GET['exam_id'] : null;

// Verify exam exists
if ($exam_id) {
    try {
        $stmt = $pdo->prepare("SELECT title FROM exams WHERE id = ?");
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
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = $_POST['question_text'];
    $options = [
        $_POST['option1'],
        $_POST['option2'],
        $_POST['option3'],
        $_POST['option4']
    ];
    $correct_option = $_POST['correct_option'];
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Insert question
        $stmt = $pdo->prepare("INSERT INTO questions (exam_id, question_text) VALUES (?, ?)");
        $stmt->execute([$exam_id, $question_text]);
        $question_id = $pdo->lastInsertId();
        
        // Insert options
        for ($i = 0; $i < 4; $i++) {
            $is_correct = ($i + 1 == $correct_option) ? 1 : 0;
            $stmt = $pdo->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
            $stmt->execute([$question_id, $options[$i], $is_correct]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        $success = "Question added successfully!";
        
        // Clear form data
        $question_text = '';
        $options = ['', '', '', ''];
        $correct_option = '';
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $error = "Error adding question: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Question - MCQ Exam System</title>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-header bg-primary text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add Question</h3>
                            <a href="view_exam.php?id=<?php echo $exam_id; ?>" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-2"></i>Back to Exam
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Adding question to: <strong><?php echo htmlspecialchars($exam['title']); ?></strong>
                        </div>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="question_text" class="form-label">Question Text</label>
                                <textarea class="form-control" id="question_text" name="question_text" rows="3" required><?php echo isset($question_text) ? htmlspecialchars($question_text) : ''; ?></textarea>
                                <div class="invalid-feedback">Please enter the question text.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Options</label>
                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><?php echo $i; ?></span>
                                        <input type="text" class="form-control" name="option<?php echo $i; ?>" placeholder="Option <?php echo $i; ?>" value="<?php echo isset($options[$i-1]) ? htmlspecialchars($options[$i-1]) : ''; ?>" required>
                                        <div class="input-group-text">
                                            <input class="form-check-input mt-0" type="radio" name="correct_option" value="<?php echo $i; ?>" <?php echo (isset($correct_option) && $correct_option == $i) ? 'checked' : ''; ?> required>
                                            <label class="ms-2 mb-0">Correct</label>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                                <div class="invalid-feedback">Please select the correct option.</div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="view_exam.php?id=<?php echo $exam_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Exam
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Add Question
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