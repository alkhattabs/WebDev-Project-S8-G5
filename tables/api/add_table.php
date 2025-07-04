<?php
require_once '../../includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get POST data
$table_number = isset($_POST['table_number']) ? intval($_POST['table_number']) : null;
$capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 4; // Default capacity of 4

// Validate input
if (!$table_number) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Table number is required']);
    exit();
}

try {
    // Check if table number already exists
    $check_query = "SELECT COUNT(*) FROM restaurant_table WHERE table_number = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([$table_number]);
    $exists = $check_stmt->fetchColumn();

    if ($exists) {
        throw new Exception('A table with this number already exists');
    }

    // Get the 'Available' status ID
    $status_query = "SELECT status_id FROM table_status WHERE status_name = 'Available'";
    $status_stmt = $conn->query($status_query);
    $status_id = $status_stmt->fetchColumn();

    if (!$status_id) {
        throw new Exception('Could not find Available status');
    }

    // Insert new table
    $insert_query = "INSERT INTO restaurant_table (table_number, capacity, status_id) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->execute([$table_number, $capacity, $status_id]);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Table added successfully',
        'table' => [
            'table_id' => $conn->lastInsertId(),
            'table_number' => $table_number,
            'capacity' => $capacity,
            'status_id' => $status_id
        ]
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log('Error in add_table.php: ' . $e->getMessage());
} 