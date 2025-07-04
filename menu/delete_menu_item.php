<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['item_id']) || empty($_POST['item_id'])) {
    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    exit;
}

$item_id = $_POST['item_id'];

try {
    // Start transaction
    $conn->beginTransaction();

    // Check if the item exists and get its image path
    $check_query = "SELECT image_path FROM menu_item WHERE item_id = :item_id";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([':item_id' => $item_id]);

    if ($check_stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Menu item not found']);
        exit;
    }

    $item = $check_stmt->fetch(PDO::FETCH_ASSOC);
    $image_path = $item['image_path'];

    // First delete related order items
    $delete_order_items_query = "DELETE FROM order_item WHERE item_id = :item_id";
    $delete_order_items_stmt = $conn->prepare($delete_order_items_query);
    $delete_order_items_stmt->execute([':item_id' => $item_id]);

    // Then delete the menu item
    $delete_query = "DELETE FROM menu_item WHERE item_id = :item_id";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->execute([':item_id' => $item_id]);

    // If there was an image, delete it from the server
    if (!empty($image_path) && file_exists('../' . $image_path)) {
        unlink('../' . $image_path);
    }

    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Menu item deleted successfully']);
} catch(PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => 'Error deleting menu item: ' . $e->getMessage()
    ]);
} 