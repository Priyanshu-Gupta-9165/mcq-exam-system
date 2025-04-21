<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Get all exam results with user and exam details
$stmt = $pdo->query("
    SELECT er.*, u.username, e.title as exam_title, e.passing_score
    FROM exam_results er
    JOIN users u ON er.user_id = u.id
    JOIN exams e ON er.exam_id = e.id
    ORDER BY er.completed_at DESC
");
$results = $stmt->fetchAll();

// Calculate statistics
$total_attempts = count($results);
$passed_attempts = count(array_filter($results, function($r) { return $r['score'] >= $r['passing_score']; }));
$average_score = $total_attempts > 0 ? round(array_sum(array_column($results, 'score')) / $total_attempts, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results - MCQ Exam System</title>
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
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="fas fa-chart-bar me-2"></i>Exam Results</h2>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-file-alt stats-icon"></i>
                    <div class="stats-number"><?php echo $total_attempts; ?></div>
                    <div class="stats-label">Total Attempts</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-check-circle stats-icon"></i>
                    <div class="stats-number"><?php echo $passed_attempts; ?></div>
                    <div class="stats-label">Passed Attempts</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-chart-line stats-icon"></i>
                    <div class="stats-number"><?php echo $average_score; ?>%</div>
                    <div class="stats-label">Average Score</div>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Exam</th>
                                <th>Score</th>
                                <th>Status</th>
                                <th>Completed</th>
                                <th>Time Taken</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $result): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($result['username']); ?></td>
                                    <td><?php echo htmlspecialchars($result['exam_title']); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                <div class="progress-bar <?php echo $result['score'] >= $result['passing_score'] ? 'bg-success' : 'bg-danger'; ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $result['score']; ?>%">
                                                </div>
                                            </div>
                                            <span><?php echo $result['score']; ?>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($result['score'] >= $result['passing_score']): ?>
                                            <span class="badge bg-success">Passed</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Failed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($result['completed_at'])); ?></td>
                                    <td><?php echo $result['time_taken']; ?> minutes</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 