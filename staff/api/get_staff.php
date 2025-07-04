<?php
require_once '../../includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Staff ID is required']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT staff_id, first_name, last_name, age, job_title, phone_number, address, email, salary 
                           FROM staff WHERE staff_id = ?");
    $stmt->execute([$_GET['id']]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($staff) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'staff' => $staff]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Staff not found']);
    }
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?> 