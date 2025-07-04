<?php
require_once '../../includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get table data from POST
$table_id = isset($_POST['table_id']) ? intval($_POST['table_id']) : null;
$capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : null;

// Validate input
if (!$table_id || !$capacity) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Table ID and capacity are required']);
    exit();
}

if ($capacity < 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Capacity must be at least 1']);
    exit();
}

try {
    // Update table capacity
    $update_query = "UPDATE restaurant_table SET capacity = ? WHERE table_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->execute([$capacity, $table_id]);

    // Get updated table data
    $select_query = "SELECT table_id, table_number, capacity, status_id FROM restaurant_table WHERE table_id = ?";
    $select_stmt = $conn->prepare($select_query);
    $select_stmt->execute([$table_id]);
    $table = $select_stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Table updated successfully',
        'table' => $table
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log('Error in update_table.php: ' . $e->getMessage());
} 