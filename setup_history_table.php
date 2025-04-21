<?php
require_once 'config.php';

try {
    // Create history table
    $sql = "CREATE TABLE IF NOT EXISTS history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        activity_type VARCHAR(50) NOT NULL,
        description TEXT NOT NULL,
        exam_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE SET NULL
    )";
    
    $pdo->exec($sql);
    echo "History table created successfully!";
    
    // Add some initial data (optional)
    $stmt = $pdo->prepare("
        INSERT INTO history (user_id, activity_type, description) 
        SELECT id, 'account_created', 'Account was created' 
        FROM users 
        WHERE id NOT IN (SELECT DISTINCT user_id FROM history)
    ");
    $stmt->execute();
    
    echo "<br>Initial history records added!";
    echo "<br><a href='history.php'>Go to History Page</a>";
    
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?> 