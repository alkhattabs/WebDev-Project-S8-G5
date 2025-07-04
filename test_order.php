<?php
require_once 'includes/db_connect.php';

try {
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Start transaction
    $conn->beginTransaction();

    // Get a valid staff ID
    $staff_sql = "SELECT staff_id FROM staff LIMIT 1";
    $staff_id = $conn->query($staff_sql)->fetchColumn();
    if (!$staff_id) {
        throw new Exception('No staff found in database');
    }
    echo "Using staff ID: " . $staff_id . "<br>";

    // Get a valid order type ID
    $type_sql = "SELECT type_id FROM order_type LIMIT 1";
    $type_id = $conn->query($type_sql)->fetchColumn();
    if (!$type_id) {
        throw new Exception('No order types found in database');
    }
    echo "Using order type ID: " . $type_id . "<br>";

    // Get a valid order status ID
    $status_sql = "SELECT status_id FROM order_status LIMIT 1";
    $status_id = $conn->query($status_sql)->fetchColumn();
    if (!$status_id) {
        throw new Exception('No order statuses found in database');
    }
    echo "Using order status ID: " . $status_id . "<br>";

    // Insert test order
    $order_sql = "INSERT INTO `order` (staff_id, order_type_id, order_status_id, order_date) 
                  VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($order_sql);
    $stmt->execute([$staff_id, $type_id, $status_id]);
    $order_id = $conn->lastInsertId();
    echo "Created order with ID: " . $order_id . "<br>";

    // Get a valid menu item
    $item_sql = "SELECT item_id, price FROM menu_item LIMIT 1";
    $stmt = $conn->query($item_sql);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        throw new Exception('No menu items found in database');
    }
    echo "Using menu item ID: " . $item['item_id'] . " with price: " . $item['price'] . "<br>";

    // Insert test order item
    $item_sql = "INSERT INTO order_item (order_id, item_id, quantity, unit_price, subtotal) 
                 VALUES (?, ?, 1, ?, ?)";
    $stmt = $conn->prepare($item_sql);
    $stmt->execute([$order_id, $item['item_id'], $item['price'], $item['price']]);
    echo "Added item to order<br>";

    // Update order total
    $update_sql = "UPDATE `order` SET total_amount = ? WHERE order_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->execute([$item['price'], $order_id]);
    echo "Updated order total<br>";

    // Commit transaction
    $conn->commit();
    echo "Transaction committed successfully<br>";

    // Verify the order
    $verify_sql = "SELECT o.*, oi.* FROM `order` o 
                   JOIN order_item oi ON o.order_id = oi.order_id 
                   WHERE o.order_id = ?";
    $stmt = $conn->prepare($verify_sql);
    $stmt->execute([$order_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<pre>";
    print_r($result);
    echo "</pre>";

} catch (Exception $e) {
    // Rollback on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
        echo "Transaction rolled back<br>";
    }
    
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 