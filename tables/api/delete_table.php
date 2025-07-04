<?php
require_once '../../includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get table ID from POST data
$table_id = isset($_POST['table_id']) ? intval($_POST['table_id']) : null;

if (!$table_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Table ID is required']);
    exit();
}

try {
    // Check if table is currently occupied
    $check_query = "SELECT status_id FROM restaurant_table WHERE table_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([$table_id]);
    $status = $check_stmt->fetchColumn();

    if ($status == 2) { // 2 = Occupied
        throw new Exception('Cannot delete an occupied table');
    }

    // Check if table has any orders in history
    $order_check = "SELECT COUNT(*) FROM `order` WHERE table_id = ?";
    $order_stmt = $conn->prepare($order_check);
    $order_stmt->execute([$table_id]);
    $has_orders = $order_stmt->fetchColumn() > 0;

    if ($has_orders) {
        throw new Exception('Cannot delete table with order history');
    }

    // Delete the table
    $delete_query = "DELETE FROM restaurant_table WHERE table_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->execute([$table_id]);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Table deleted successfully']);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log('Error in delete_table.php: ' . $e->getMessage());
} 