<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Log the login activity
        logActivity($pdo, $user['id'], 'login', getActivityDescription('login'));
        
        header("Location: ../dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid username or password";
        header("Location: ../index.php");
        exit();
    }
}
?> 