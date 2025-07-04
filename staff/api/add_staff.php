<?php
require_once '../../includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Validate required fields
$required_fields = ['first_name', 'last_name', 'age', 'job_title', 'phone_number', 'address', 'email', 'salary', 'password'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
        exit();
    }
}

try {
    // Check if email already exists
    $stmt = $conn->prepare("SELECT staff_id FROM staff WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    if ($stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit();
    }

    // Hash password
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert new staff
    $stmt = $conn->prepare("INSERT INTO staff (first_name, last_name, age, job_title, phone_number, address, email, salary, password_hash) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['age'],
        $_POST['job_title'],
        $_POST['phone_number'],
        $_POST['address'],
        $_POST['email'],
        $_POST['salary'],
        $password_hash
    ]);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Staff added successfully']);
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?> 