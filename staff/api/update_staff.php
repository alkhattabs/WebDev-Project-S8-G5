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
$required_fields = ['staff_id', 'first_name', 'last_name', 'age', 'job_title', 'phone_number', 'address', 'email', 'salary'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
        exit();
    }
}

try {
    // Check if email exists for another staff member
    $stmt = $conn->prepare("SELECT staff_id FROM staff WHERE email = ? AND staff_id != ?");
    $stmt->execute([$_POST['email'], $_POST['staff_id']]);
    if ($stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit();
    }

    // Start building the update query
    $query = "UPDATE staff SET 
              first_name = ?, 
              last_name = ?, 
              age = ?, 
              job_title = ?, 
              phone_number = ?, 
              address = ?, 
              email = ?, 
              salary = ?";
    $params = [
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['age'],
        $_POST['job_title'],
        $_POST['phone_number'],
        $_POST['address'],
        $_POST['email'],
        $_POST['salary']
    ];

    // Add password update if provided
    if (isset($_POST['password']) && !empty($_POST['password'])) {
        $query .= ", password_hash = ?";
        $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    $query .= " WHERE staff_id = ?";
    $params[] = $_POST['staff_id'];

    // Execute update
    $stmt = $conn->prepare($query);
    $stmt->execute($params);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Staff updated successfully']);
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?> 