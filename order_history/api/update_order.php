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
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;
$order_type_id = isset($_POST['order_type_id']) ? intval($_POST['order_type_id']) : null;
$table_id = isset($_POST['table_id']) && !empty($_POST['table_id']) ? intval($_POST['table_id']) : null;
$customer_id = isset($_POST['customer_id']) && !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
$order_status_id = isset($_POST['order_status_id']) ? intval($_POST['order_status_id']) : null;
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;
$items = isset($_POST['items']) ? json_decode($_POST['items'], true) : [];

// Validate required fields
if (!$order_id || !$order_type_id || !$order_status_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    // Start transaction
    $conn->beginTransaction();

    // Get current order details
    $current_order_query = "SELECT o.table_id, o.order_type_id, o.order_status_id, os.status_name as current_status 
                           FROM `order` o 
                           JOIN order_status os ON o.order_status_id = os.status_id 
                           WHERE o.order_id = ?";
    $current_order_stmt = $conn->prepare($current_order_query);
    $current_order_stmt->execute([$order_id]);
    $current_order = $current_order_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$current_order) {
        throw new Exception('Order not found');
    }

    // Get status names
    $status_query = "SELECT status_id, status_name FROM order_status WHERE status_id = ?";
    $status_stmt = $conn->prepare($status_query);
    $status_stmt->execute([$order_status_id]);
    $new_status = $status_stmt->fetch(PDO::FETCH_ASSOC);

    // Handle table status changes
    $should_free_table = false;

    // Case 1: Table or order type changed
    if ($current_order['order_type_id'] != $order_type_id || $current_order['table_id'] != $table_id) {
        // If old order had a table, free it
        if ($current_order['table_id']) {
            $should_free_table = true;
            $old_table_id = $current_order['table_id'];
        }

        // If new order type is dine-in and has a table, mark it as occupied
        if ($order_type_id == 1 && $table_id) { // Assuming type_id 1 is 'Dine In'
            $occupied_status_query = "SELECT status_id FROM table_status WHERE status_name = 'Occupied'";
            $occupied_status_stmt = $conn->query($occupied_status_query);
            $occupied_status_id = $occupied_status_stmt->fetchColumn();

            if ($occupied_status_id) {
                $occupy_table_stmt = $conn->prepare("UPDATE restaurant_table SET status_id = ? WHERE table_id = ?");
                $occupy_table_stmt->execute([$occupied_status_id, $table_id]);
            }
        }
    }

    // Case 2: Order status changed to Completed or Cancelled
    if ($new_status['status_name'] === 'Completed' || $new_status['status_name'] === 'Cancelled') {
        if ($table_id) {
            $should_free_table = true;
            $old_table_id = $table_id;
        }
    }

    // Free table if needed
    if ($should_free_table) {
        $available_status_query = "SELECT status_id FROM table_status WHERE status_name = 'Available'";
        $available_status_stmt = $conn->query($available_status_query);
        $available_status_id = $available_status_stmt->fetchColumn();

        if ($available_status_id) {
            $free_table_stmt = $conn->prepare("UPDATE restaurant_table SET status_id = ? WHERE table_id = ?");
            $free_table_stmt->execute([$available_status_id, $old_table_id]);
        }
    }

    // Update order items
    if (!empty($items)) {
        // First, get current items to calculate price differences
        $current_items_query = "SELECT item_id, quantity FROM order_item WHERE order_id = ?";
        $current_items_stmt = $conn->prepare($current_items_query);
        $current_items_stmt->execute([$order_id]);
        $current_items = $current_items_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Delete all current items
        $delete_items_query = "DELETE FROM order_item WHERE order_id = ?";
        $delete_items_stmt = $conn->prepare($delete_items_query);
        $delete_items_stmt->execute([$order_id]);

        // Insert new items and calculate total
        $total_amount = 0;
        $insert_item_query = "INSERT INTO order_item (order_id, item_id, quantity, unit_price, subtotal) 
                             VALUES (?, ?, ?, (SELECT price FROM menu_item WHERE item_id = ?), 
                             ? * (SELECT price FROM menu_item WHERE item_id = ?))";
        $insert_item_stmt = $conn->prepare($insert_item_query);

        foreach ($items as $item) {
            $item_id = intval($item['item_id']);
            $quantity = intval($item['quantity']);

            // Get item price
            $price_query = "SELECT price FROM menu_item WHERE item_id = ?";
            $price_stmt = $conn->prepare($price_query);
            $price_stmt->execute([$item_id]);
            $price = $price_stmt->fetchColumn();

            if ($price === false) {
                throw new Exception('Invalid item ID: ' . $item_id);
            }

            // Insert order item
            $insert_item_stmt->execute([
                $order_id,
                $item_id,
                $quantity,
                $item_id,
                $quantity,
                $item_id
            ]);

            $total_amount += ($price * $quantity);
        }

        // Update order total
        $update_total_query = "UPDATE `order` SET total_amount = ? WHERE order_id = ?";
        $update_total_stmt = $conn->prepare($update_total_query);
        $update_total_stmt->execute([$total_amount, $order_id]);
    }

    // Update order
    $update_query = "UPDATE `order` 
                    SET order_type_id = ?,
                        table_id = ?,
                        customer_id = ?,
                        order_status_id = ?,
                        notes = ?
                    WHERE order_id = ?";

    $update_stmt = $conn->prepare($update_query);
    $update_stmt->execute([
        $order_type_id,
        $table_id,
        $customer_id,
        $order_status_id,
        $notes,
        $order_id
    ]);

    // Commit transaction
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Order updated successfully']);

} catch (Exception $e) {
    // Rollback on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error updating order: ' . $e->getMessage()]);
    error_log('Error in update_order.php: ' . $e->getMessage());
}
?> 