<?php
// Function to log user activity
function logActivity($user_id, $activity_type, $description, $exam_id = null) {
    global $pdo;
    
    // Only log exam-related activities
    $exam_activities = [
        'exam_start',
        'exam_complete',
        'exam_create',
        'exam_update',
        'exam_delete',
        'question_add',
        'question_update',
        'question_delete'
    ];
    
    if (in_array($activity_type, $exam_activities)) {
        $stmt = $pdo->prepare("INSERT INTO history (user_id, activity_type, description, exam_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $activity_type, $description, $exam_id]);
    }
}

function getActivityStyle($type) {
    $styles = [
        'exam_start' => [
            'icon' => 'fa-play-circle',
            'color' => 'primary',
            'bg' => 'bg-primary'
        ],
        'exam_complete' => [
            'icon' => 'fa-check-circle',
            'color' => 'success',
            'bg' => 'bg-success'
        ],
        'exam_create' => [
            'icon' => 'fa-plus-circle',
            'color' => 'info',
            'bg' => 'bg-info'
        ],
        'exam_update' => [
            'icon' => 'fa-edit',
            'color' => 'warning',
            'bg' => 'bg-warning'
        ],
        'exam_delete' => [
            'icon' => 'fa-trash',
            'color' => 'danger',
            'bg' => 'bg-danger'
        ],
        'question_add' => [
            'icon' => 'fa-plus',
            'color' => 'info',
            'bg' => 'bg-info'
        ],
        'question_update' => [
            'icon' => 'fa-edit',
            'color' => 'warning',
            'bg' => 'bg-warning'
        ],
        'question_delete' => [
            'icon' => 'fa-trash',
            'color' => 'danger',
            'bg' => 'bg-danger'
        ]
    ];
    
    return $styles[$type] ?? [
        'icon' => 'fa-info-circle',
        'color' => 'secondary',
        'bg' => 'bg-secondary'
    ];
}

// Function to get activity description
function getActivityDescription($type, $details = []) {
    $descriptions = [
        'exam_start' => "Started exam: {exam_title}",
        'exam_complete' => "Completed exam: {exam_title} with score {score}%",
        'exam_create' => "Created new exam: {exam_title}",
        'exam_update' => "Updated exam: {exam_title}",
        'exam_delete' => "Deleted exam: {exam_title}",
        'question_add' => "Added question to exam: {exam_title}",
        'question_update' => "Updated question in exam: {exam_title}",
        'question_delete' => "Deleted question from exam: {exam_title}"
    ];
    
    $description = $descriptions[$type] ?? $type;
    
    if (!empty($details)) {
        foreach ($details as $key => $value) {
            $description = str_replace("{{$key}}", $value, $description);
        }
    }
    
    return $description;
}

// Helper function to format duration
function formatDuration($minutes) {
    if ($minutes < 60) {
        return $minutes . ' minutes';
    }
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return $hours . ' hour' . ($hours > 1 ? 's' : '') . 
           ($mins > 0 ? ' ' . $mins . ' minute' . ($mins > 1 ? 's' : '') : '');
}

// Helper function to calculate percentage
function calculatePercentage($score, $total) {
    if ($total == 0) return 0;
    return round(($score / $total) * 100, 1);
}

// Helper function to determine if a score passes
function isPassing($score, $total, $passing_score) {
    $percentage = calculatePercentage($score, $total);
    return $percentage >= $passing_score;
}

?> 