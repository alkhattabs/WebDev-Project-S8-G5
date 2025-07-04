<?php
require_once '../../includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Validate input
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
    exit();
}

$customerId = trim($_GET['id']);

// Validate data
if (empty($customerId)) {
    echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
    exit();
}

try {
    // Get customer details
    $query = "SELECT customer_id, first_name, last_name, phone_number, age, email, total_spend, created_at FROM customer WHERE customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$customerId]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
        exit();
    }

    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'customer' => $customer]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 