<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $stmt = $conn->prepare("SELECT staff_id, first_name, last_name, password_hash FROM staff WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['staff_id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            header('Location: ../dashboard.php');
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password";
            header('Location: ../index.php');
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "An error occurred. Please try again.";
        header('Location: ../index.php');
        exit();
    }
}
?> 