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
    // Fetch order details
    $order_query = "SELECT o.order_id, o.order_date, o.total_amount, ot.type_name, rt.table_number
                    FROM `order` o
                    JOIN order_type ot ON o.order_type_id = ot.type_id
                    LEFT JOIN restaurant_table rt ON o.table_id = rt.table_id
                    WHERE o.order_id = ?";
    
    $order_stmt = $conn->prepare($order_query);
    $order_stmt->execute([$_GET['order_id']]);
    $receipt = $order_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$receipt) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }

    // Fetch order items
    $items_query = "SELECT oi.quantity, oi.unit_price, oi.subtotal, mi.name
                    FROM order_item oi
                    JOIN menu_item mi ON oi.item_id = mi.item_id
                    WHERE oi.order_id = ?
                    ORDER BY mi.name";
    
    $items_stmt = $conn->prepare($items_query);
    $items_stmt->execute([$_GET['order_id']]);
    $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'receipt' => $receipt,
        'items' => $items
    ]);
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    error_log('Database error in get_receipt.php: ' . $e->getMessage());
} 