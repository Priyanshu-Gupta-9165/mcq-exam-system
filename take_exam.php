<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$exam_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get exam details
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch();

if (!$exam) {
    header("Location: dashboard.php");
    exit();
}

// Check if user has already taken this exam
$stmt = $pdo->prepare("SELECT * FROM exam_results WHERE user_id = ? AND exam_id = ?");
$stmt->execute([$user_id, $exam_id]);
if ($stmt->fetch()) {
    $_SESSION['error'] = "You have already taken this exam.";
    header("Location: dashboard.php");
    exit();
}

// Log exam start
logActivity($user_id, 'exam_start', getActivityDescription('exam_start', ['exam_title' => $exam['title']]), $exam_id);

// Get questions with their options
$stmt = $pdo->prepare("
    SELECT q.*, GROUP_CONCAT(o.id) as option_ids, 
           GROUP_CONCAT(o.option_text) as option_texts,
           GROUP_CONCAT(o.is_correct) as is_correct
    FROM questions q
    LEFT JOIN options o ON q.id = o.question_id
    WHERE q.exam_id = ?
    GROUP BY q.id
");
$stmt->execute([$exam_id]);
$questions = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $score = 0;
    $total_questions = count($questions);
    
    foreach ($questions as $question) {
        if (isset($_POST['answers'][$question['id']])) {
            // Get the selected option
            $selected_option_id = $_POST['answers'][$question['id']];
            
            // Check if the selected option is correct
            $stmt = $pdo->prepare("SELECT is_correct FROM options WHERE id = ? AND question_id = ?");
            $stmt->execute([$selected_option_id, $question['id']]);
            $result = $stmt->fetch();
            
            if ($result && $result['is_correct']) {
                $score++;
            }
        }
    }
    
    // Calculate percentage score
    $percentage_score = ($score / $total_questions) * 100;
    
    // Save result
    $stmt = $pdo->prepare("INSERT INTO exam_results (user_id, exam_id, score, total_questions) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $exam_id, $percentage_score, $total_questions]);
    
    // Log exam completion
    logActivity($user_id, 'exam_complete', getActivityDescription('exam_complete', [
        'exam_title' => $exam['title'],
        'score' => round($percentage_score, 1)
    ]), $exam_id);
    
    $_SESSION['success'] = "Exam completed! Your score: " . round($percentage_score, 1) . "%";
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($exam['title']); ?> - MCQ System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">
                        <i class="fas fa-info-circle me-2"></i>Message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalMessage"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>MCQ Exam System
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <?php 
        require_once 'includes/alert.php';
        showAlert();
        ?>
        
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i><?php echo htmlspecialchars($exam['title']); ?>
                </h3>
                <div class="timer p-2 bg-light text-dark rounded">
                    <i class="fas fa-clock me-2"></i>Time Remaining: <span id="time"><?php echo $exam['duration']; ?>:00</span>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i><?php echo htmlspecialchars($exam['description']); ?>
                </div>
                
                <form method="POST" id="examForm">
                    <?php foreach ($questions as $index => $question): 
                        // Parse the grouped data
                        $option_ids = explode(',', $question['option_ids']);
                        $option_texts = explode(',', $question['option_texts']);
                        $is_correct = explode(',', $question['is_correct']);
                    ?>
                        <div class="question-card mb-4 p-4 border rounded bg-white">
                            <h5 class="mb-3">
                                <span class="badge bg-primary me-2">Question <?php echo $index + 1; ?></span>
                                <?php echo htmlspecialchars($question['question_text']); ?>
                            </h5>
                            
                            <div class="options">
                                <?php for ($i = 0; $i < count($option_ids); $i++): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" 
                                               name="answers[<?php echo $question['id']; ?>]" 
                                               id="option_<?php echo $option_ids[$i]; ?>"
                                               value="<?php echo $option_ids[$i]; ?>">
                                        <label class="form-check-label" for="option_<?php echo $option_ids[$i]; ?>">
                                            <?php echo htmlspecialchars($option_texts[$i]); ?>
                                        </label>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="d-flex justify-content-between">
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Submit Exam
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Timer functionality
        let timeLeft = <?php echo $exam['duration'] * 60; ?>;
        const timerElement = document.getElementById('time');
        
        const timer = setInterval(() => {
            timeLeft--;
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                showMessage('Time is up! Your exam will be submitted automatically.', 'warning');
                setTimeout(() => {
                    document.getElementById('examForm').submit();
                }, 2000);
            }
        }, 1000);

        // Form submission confirmation
        document.getElementById('examForm').addEventListener('submit', function(e) {
            const answered = document.querySelectorAll('input[type="radio"]:checked').length;
            const total = <?php echo count($questions); ?>;
            const unanswered = total - answered;
            
            if (unanswered > 0) {
                e.preventDefault();
                showMessage(`You have ${unanswered} unanswered questions. Are you sure you want to submit?`, 'warning', true, () => {
                    document.getElementById('examForm').submit();
                });
            }
        });

        // Message display function
        function showMessage(message, type = 'info', showConfirm = false, callback = null) {
            const modal = new bootstrap.Modal(document.getElementById('messageModal'));
            const modalTitle = document.getElementById('messageModalLabel');
            const modalMessage = document.getElementById('modalMessage');
            const modalFooter = document.querySelector('#messageModal .modal-footer');
            
            // Set icon and color based on message type
            let icon = 'info-circle';
            let color = 'primary';
            
            switch(type) {
                case 'success':
                    icon = 'check-circle';
                    color = 'success';
                    break;
                case 'error':
                    icon = 'times-circle';
                    color = 'danger';
                    break;
                case 'warning':
                    icon = 'exclamation-triangle';
                    color = 'warning';
                    break;
            }
            
            // Update modal content
            modalTitle.innerHTML = `<i class="fas fa-${icon} me-2"></i>${type.charAt(0).toUpperCase() + type.slice(1)}`;
            modalMessage.innerHTML = `<div class="text-${color}"><i class="fas fa-${icon} me-2"></i>${message}</div>`;
            
            // Update footer buttons
            if (showConfirm) {
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-${color}" id="confirmButton">Confirm</button>
                `;
                
                // Add confirm button handler
                document.getElementById('confirmButton').onclick = () => {
                    modal.hide();
                    if (callback) callback();
                };
            } else {
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-${color}" data-bs-dismiss="modal">OK</button>
                `;
            }
            
            modal.show();
        }

        <?php
        // Show error message if exists
        if (isset($_SESSION['error'])) {
            echo "showMessage(" . json_encode($_SESSION['error']) . ", 'error');";
            unset($_SESSION['error']);
        }
        // Show success message if exists
        if (isset($_SESSION['success'])) {
            echo "showMessage(" . json_encode($_SESSION['success']) . ", 'success');";
            unset($_SESSION['success']);
        }
        ?>
    </script>
</body>
</html> 