<?php
session_start();
require_once 'config.php';

// Debug information
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if role exists in session
if (!isset($_SESSION['role'])) {
    // If role is not set, try to get it from database
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['role'] = $user['role'];
    } else {
        // If user not found in database, destroy session and redirect
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get user statistics
$stats = [];
if ($role === 'admin') {
    // Get total students
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
    $stats['total_students'] = $stmt->fetch()['count'];
    
    // Get total exams
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM exams");
    $stats['total_exams'] = $stmt->fetch()['count'];
    
    // Get total attempts
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM exam_results");
    $stats['total_attempts'] = $stmt->fetch()['count'];
    
    // Get average score
    $stmt = $pdo->query("SELECT AVG(score) as avg FROM exam_results");
    $avg = $stmt->fetch()['avg'];
    $stats['avg_score'] = $avg === null ? 0 : round($avg, 1);
} else {
    // Get student's exam attempts
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM exam_results WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stats['attempts'] = $stmt->fetch()['count'];
    
    // Get student's average score
    $stmt = $pdo->prepare("SELECT AVG(score) as avg FROM exam_results WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $avg = $stmt->fetch()['avg'];
    $stats['avg_score'] = $avg === null ? 0 : round($avg, 1);
    
    // Get available exams
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM exams");
    $stats['available_exams'] = $stmt->fetch()['count'];
}

// Get recent activity
$recent_activity = [];
if ($role === 'admin') {
    $stmt = $pdo->query("
        SELECT er.*, u.username, e.title 
        FROM exam_results er 
        JOIN users u ON er.user_id = u.id 
        JOIN exams e ON er.exam_id = e.id 
        ORDER BY er.completed_at DESC 
        LIMIT 5
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT er.*, e.title 
        FROM exam_results er 
        JOIN exams e ON er.exam_id = e.id 
        WHERE er.user_id = ? 
        ORDER BY er.completed_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
}
$recent_activity = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MCQ Exam System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-graduation-cap me-2"></i>MCQ Exam System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link"><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="history.php">
                            <i class="fas fa-history me-2"></i>History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="dashboard-header">
        <div class="container">
            <div class="welcome-message">
                <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                <p>Here's what's happening with your <?php echo $role === 'admin' ? 'exam system' : 'exams'; ?> today.</p>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <?php 
        require_once 'includes/alert.php';
        showAlert();
        ?>
        
        <?php if ($role === 'admin'): ?>
            <!-- Admin Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-users stats-icon"></i>
                        <div class="stats-number"><?php echo $stats['total_students']; ?></div>
                        <div class="stats-label">Total Students</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-file-alt stats-icon"></i>
                        <div class="stats-number"><?php echo $stats['total_exams']; ?></div>
                        <div class="stats-label">Total Exams</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-check-circle stats-icon"></i>
                        <div class="stats-number"><?php echo $stats['total_attempts']; ?></div>
                        <div class="stats-label">Total Attempts</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-chart-line stats-icon"></i>
                        <div class="stats-number"><?php echo $stats['avg_score']; ?>%</div>
                        <div class="stats-label">Average Score</div>
                    </div>
                </div>
            </div>

            <!-- Admin Actions -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <i class="fas fa-plus-circle dashboard-icon"></i>
                            <h5 class="card-title">Create New Exam</h5>
                            <p class="card-text">Create a new MCQ exam with custom questions and settings.</p>
                            <a href="admin/create_exam.php" class="btn btn-primary">Create Exam</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <i class="fas fa-cog dashboard-icon"></i>
                            <h5 class="card-title">Manage Exams</h5>
                            <p class="card-text">View, edit, or delete existing exams and their questions.</p>
                            <a href="admin/manage_exams.php" class="btn btn-primary">Manage Exams</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <i class="fas fa-chart-bar dashboard-icon"></i>
                            <h5 class="card-title">View Results</h5>
                            <p class="card-text">Analyze student performance and exam statistics.</p>
                            <a href="admin/results.php" class="btn btn-primary">View Results</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Student Statistics -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="fas fa-file-alt stats-icon"></i>
                        <div class="stats-number"><?php echo $stats['available_exams']; ?></div>
                        <div class="stats-label">Available Exams</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="fas fa-check-circle stats-icon"></i>
                        <div class="stats-number"><?php echo $stats['attempts']; ?></div>
                        <div class="stats-label">Your Attempts</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="fas fa-chart-line stats-icon"></i>
                        <div class="stats-number"><?php echo $stats['avg_score']; ?>%</div>
                        <div class="stats-label">Your Average Score</div>
                    </div>
                </div>
            </div>

            <!-- Available Exams -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-4">Available Exams</h2>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM exams ORDER BY created_at DESC");
                    while ($exam = $stmt->fetch()) {
                        echo '<div class="card mb-3">';
                        echo '<div class="card-body">';
                        echo '<div class="d-flex justify-content-between align-items-center">';
                        echo '<div>';
                        echo '<h5 class="card-title">' . htmlspecialchars($exam['title']) . '</h5>';
                        echo '<p class="card-text">' . htmlspecialchars($exam['description']) . '</p>';
                        echo '<p class="card-text"><small class="text-muted"><i class="fas fa-clock me-2"></i>Duration: ' . $exam['duration'] . ' minutes</small></p>';
                        echo '</div>';
                        echo '<a href="take_exam.php?id=' . $exam['id'] . '" class="btn btn-primary"><i class="fas fa-play me-2"></i>Start Exam</a>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-12">
                <div class="activity-feed">
                    <h3><i class="fas fa-history me-2"></i>Recent Activity</h3>
                    <?php if (empty($recent_activity)): ?>
                        <p class="text-muted">No recent activity to display.</p>
                    <?php else: ?>
                        <?php foreach ($recent_activity as $activity): ?>
                            <div class="activity-item d-flex align-items-center">
                                <div class="activity-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <?php if ($role === 'admin'): ?>
                                            <?php echo htmlspecialchars($activity['username']); ?> completed
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($activity['title']); ?>
                                    </div>
                                    <div class="activity-time">
                                        Score: <?php echo $activity['score']; ?>% | 
                                        <?php echo date('M d, Y H:i', strtotime($activity['completed_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 