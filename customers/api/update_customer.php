<?php
require_once '../../includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Validate input
if (!isset($_POST['customerId']) || !isset($_POST['firstName']) || !isset($_POST['lastName']) || !isset($_POST['phoneNumber'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$customerId = trim($_POST['customerId']);
$firstName = trim($_POST['firstName']);
$lastName = trim($_POST['lastName']);
$phoneNumber = trim($_POST['phoneNumber']);
$age = isset($_POST['age']) && !empty($_POST['age']) ? intval($_POST['age']) : null;
$email = isset($_POST['email']) && !empty($_POST['email']) ? trim($_POST['email']) : null;

// Validate data
if (empty($customerId) || empty($firstName) || empty($lastName) || empty($phoneNumber)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'First name, last name, and phone number are required']);
    exit();
}

try {
    // Check if phone number already exists for other customers
    $check_query = "SELECT customer_id FROM customer WHERE phone_number = ? AND customer_id != ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([$phoneNumber, $customerId]);

    if ($check_stmt->rowCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Phone number already exists']);
        exit();
    }

    // Update customer
    $update_query = "UPDATE customer SET first_name = ?, last_name = ?, phone_number = ?, age = ?, email = ? WHERE customer_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->execute([$firstName, $lastName, $phoneNumber, $age, $email, $customerId]);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Customer updated successfully']);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 