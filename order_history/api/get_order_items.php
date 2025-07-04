<?php
require_once '../../includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit();
}

try {
    // Fetch order items with menu item details
    $query = "SELECT oi.quantity, oi.unit_price, oi.subtotal, mi.name
              FROM order_item oi
              JOIN menu_item mi ON oi.item_id = mi.item_id
              WHERE oi.order_id = ?
              ORDER BY mi.name";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$_GET['order_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'items' => $items]);
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    error_log('Database error in get_order_items.php: ' . $e->getMessage());
} 