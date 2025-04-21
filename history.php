<?php
session_start();
require_once 'config.php';
require_once 'includes/alert.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Check if history table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'history'");
    if ($stmt->rowCount() == 0) {
        $_SESSION['warning'] = "History table not found. Please run the setup file first.";
        header("Location: setup_history_table.php");
        exit();
    }

    // Get user history with detailed information
    $history_query = "";
    if ($role === 'admin') {
        // For admin, show all exam-related activities
        $history_query = "
            SELECT 
                h.*,
                u.username,
                u.role as user_role,
                e.title as exam_title,
                e.duration,
                e.passing_score,
                er.score as exam_score,
                er.total_questions
            FROM history h
            LEFT JOIN users u ON h.user_id = u.id
            LEFT JOIN exams e ON h.exam_id = e.id
            LEFT JOIN exam_results er ON (h.exam_id = er.exam_id AND h.user_id = er.user_id)
            WHERE h.activity_type IN ('exam_start', 'exam_complete', 'exam_create', 'exam_update', 'exam_delete', 'question_add', 'question_update', 'question_delete')
            ORDER BY h.created_at DESC
        ";
        $stmt = $pdo->query($history_query);
    } else {
        // For students, show only their exam-related activities
        $history_query = "
            SELECT 
                h.*,
                u.username,
                u.role as user_role,
                e.title as exam_title,
                e.duration,
                e.passing_score,
                er.score as exam_score,
                er.total_questions
            FROM history h
            LEFT JOIN users u ON h.user_id = u.id
            LEFT JOIN exams e ON h.exam_id = e.id
            LEFT JOIN exam_results er ON (h.exam_id = er.exam_id AND h.user_id = er.user_id)
            WHERE h.user_id = ? 
            AND h.activity_type IN ('exam_start', 'exam_complete')
            ORDER BY h.created_at DESC
        ";
        $stmt = $pdo->prepare($history_query);
        $stmt->execute([$user_id]);
    }

    $history = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error accessing history: " . $e->getMessage();
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity History - MCQ Exam System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 50px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .timeline-item {
            position: relative;
            padding-left: 80px;
            padding-bottom: 30px;
        }
        .timeline-icon {
            position: absolute;
            left: 35px;
            top: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            color: white;
        }
        .timeline-content {
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background: white;
        }
        .timeline-date {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 15px;
        }
        .activity-details {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .activity-details i {
            width: 20px;
            text-align: center;
            margin-right: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>MCQ Exam System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php 
        showAlert();
        ?>
        
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white py-3">
                <h3 class="mb-0">
                    <i class="fas fa-history me-2"></i>Exam Activity History
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($history)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h4>No Exam Activity History</h4>
                        <p class="text-muted">There are no exam activities to display yet.</p>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($history as $activity): 
                            $style = getActivityStyle($activity['activity_type']);
                        ?>
                            <div class="timeline-item">
                                <div class="timeline-icon bg-<?php echo $style['color']; ?>">
                                    <i class="fas <?php echo $style['icon']; ?>"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="activity-icon bg-<?php echo $style['color']; ?>">
                                            <i class="fas <?php echo $style['icon']; ?> fa-lg"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0">
                                                <?php if ($role === 'admin'): ?>
                                                    <span class="badge bg-<?php echo $activity['user_role'] === 'admin' ? 'danger' : 'primary'; ?> me-2">
                                                        <?php echo htmlspecialchars($activity['username']); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($activity['description']); ?>
                                            </h5>
                                            <div class="timeline-date">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo date('F j, Y g:i A', strtotime($activity['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($activity['exam_id'] && ($activity['activity_type'] === 'exam_start' || $activity['activity_type'] === 'exam_complete')): ?>
                                        <div class="activity-details">
                                            <?php if ($activity['exam_title']): ?>
                                                <div class="mb-1">
                                                    <i class="fas fa-file-alt text-primary"></i>
                                                    Exam: <?php echo htmlspecialchars($activity['exam_title']); ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($activity['duration']): ?>
                                                <div class="mb-1">
                                                    <i class="fas fa-clock text-info"></i>
                                                    Duration: <?php echo $activity['duration']; ?> minutes
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($activity['exam_score'] !== null && $activity['activity_type'] === 'exam_complete'): ?>
                                                <div class="mb-1">
                                                    <i class="fas fa-star text-warning"></i>
                                                    Score: <?php echo $activity['exam_score']; ?>/<?php echo $activity['total_questions']; ?>
                                                    (<?php echo round(($activity['exam_score'] / $activity['total_questions']) * 100, 1); ?>%)
                                                </div>
                                                
                                                <?php if ($activity['passing_score']): ?>
                                                    <div>
                                                        <i class="fas fa-check-circle <?php echo ($activity['exam_score'] >= $activity['passing_score']) ? 'text-success' : 'text-danger'; ?>"></i>
                                                        Status: <?php echo ($activity['exam_score'] >= $activity['passing_score']) ? 'Passed' : 'Failed'; ?>
                                                        (Passing Score: <?php echo $activity['passing_score']; ?>%)
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 