<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Database connection
try {
    $conn = new PDO('mysql:host=localhost;dbname=hadhramaut_restaurant', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    exit;
}

try {
    $item_id = $_GET['id'];
    $query = "SELECT m.*, c.category_name FROM menu_item m LEFT JOIN menu_category c ON m.category_id = c.category_id WHERE m.item_id = :item_id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':item_id' => $item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Menu item not found']);
        exit;
    }
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'item' => [
            'item_id' => $item['item_id'],
            'name' => $item['name'],
            'category_id' => $item['category_id'],
            'category_name' => $item['category_name'],
            'price' => $item['price'],
            'image_path' => $item['image_path'],
            'is_available' => $item['is_available']
        ]
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} 