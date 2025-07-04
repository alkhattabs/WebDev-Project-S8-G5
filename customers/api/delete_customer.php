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
    // Check if customer exists
    $check_query = "SELECT customer_id FROM customer WHERE customer_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([$customerId]);

    if ($check_stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
        exit();
    }

    // Delete customer
    $delete_query = "DELETE FROM customer WHERE customer_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->execute([$customerId]);

    echo json_encode(['success' => true, 'message' => 'Customer deleted successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 