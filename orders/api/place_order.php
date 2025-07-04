<?php
require_once '../../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Log input data for debugging
error_log('Order Input: ' . print_r($input, true));

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Validate required fields
if (!isset($input['orderType']) || !isset($input['staffId']) || !isset($input['items']) || !isset($input['total'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Start transaction
    $conn->beginTransaction();

    // Get order type ID
    $type_name = $input['orderType'] === 'dinein' ? 'Dine In' : 'Take Away';
    $type_stmt = $conn->prepare("SELECT type_id FROM order_type WHERE type_name = ?");
    $type_stmt->execute([$type_name]);
    $order_type_id = $type_stmt->fetchColumn();

    if (!$order_type_id) {
        throw new Exception('Invalid order type');
    }

    // Get initial order status ID (In Progress)
    $status_stmt = $conn->query("SELECT status_id FROM order_status WHERE status_name = 'In Progress'");
    $order_status_id = $status_stmt->fetchColumn();

    if (!$order_status_id) {
        throw new Exception('Could not get order status');
    }

    // Handle customer information
    $customer_id = null;
    if ($input['customerInfo']['isGuest']) {
        // For guest customers, store their name in the notes field
        $guest_name = $input['customerInfo']['name'];
        $notes = "Guest Customer: " . $guest_name;
    } else {
        // For registered customers, use their customer_id
        $customer_id = $input['customerInfo']['customerId'];
        $notes = null;
    }

    // Insert order
    $order_stmt = $conn->prepare("INSERT INTO `order` (customer_id, staff_id, table_id, order_type_id, order_status_id, total_amount, notes) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
    $order_stmt->execute([
        $customer_id,
        $input['staffId'],
        $input['tableId'],
        $order_type_id,
        $order_status_id,
        $input['total'],
        $notes
    ]);
    $order_id = $conn->lastInsertId();

    // Log order creation
    error_log('Order created with ID: ' . $order_id);

    // If dine-in, update table status to Occupied
    if ($input['orderType'] === 'dinein' && $input['tableId']) {
        $occupied_status_stmt = $conn->query("SELECT status_id FROM table_status WHERE status_name = 'Occupied'");
        $occupied_status_id = $occupied_status_stmt->fetchColumn();

        if (!$occupied_status_id) {
            throw new Exception('Could not get occupied status ID');
        }

        $update_table_stmt = $conn->prepare("UPDATE restaurant_table SET status_id = ? WHERE table_id = ?");
        $update_table_stmt->execute([$occupied_status_id, $input['tableId']]);
    }

    // Insert order items
    $item_stmt = $conn->prepare("INSERT INTO order_item (order_id, item_id, quantity, unit_price, subtotal) 
                               VALUES (?, ?, ?, ?, ?)");

    foreach ($input['items'] as $item_id => $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $item_stmt->execute([
            $order_id,
            $item_id,
            $item['quantity'],
            $item['price'],
            $subtotal
        ]);
        // Log each item insertion
        error_log("Added item to order: Item ID: $item_id, Quantity: {$item['quantity']}, Price: {$item['price']}");
    }

    // Update customer's total spend if it's a registered customer
    if ($customer_id) {
        $update_spend_stmt = $conn->prepare("UPDATE customer 
                                           SET total_spend = total_spend + ? 
                                           WHERE customer_id = ?");
        $update_spend_stmt->execute([$input['total'], $customer_id]);
    }

    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'order_id' => $order_id]);

} catch (Exception $e) {
    // Rollback on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    // Log the error
    error_log('Order placement error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 