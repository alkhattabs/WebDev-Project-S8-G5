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
    $query = "SELECT o.order_id, o.order_date, o.total_amount, 
                     ot.type_name, rt.table_number,
                     CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                     CONCAT(s.first_name, ' ', s.last_name) as staff_name
              FROM `order` o
              LEFT JOIN order_type ot ON o.order_type_id = ot.type_id
              LEFT JOIN restaurant_table rt ON o.table_id = rt.table_id
              LEFT JOIN customer c ON o.customer_id = c.customer_id
              LEFT JOIN staff s ON o.staff_id = s.staff_id
              WHERE o.order_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$_GET['order_id']]);
    $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$receipt) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }

    // Fetch order items
    $query = "SELECT oi.quantity, oi.unit_price, oi.subtotal, mi.name
              FROM order_item oi
              JOIN menu_item mi ON oi.item_id = mi.item_id
              WHERE oi.order_id = ?
              ORDER BY mi.name";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$_GET['order_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'receipt' => $receipt,
        'items' => $items
    ]);
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?> 