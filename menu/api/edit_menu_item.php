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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No item ID provided']);
    exit;
}

try {
    $item_id = $_GET['id'];
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    // Fetch current item data for image handling
    $current_query = "SELECT image_path FROM menu_item WHERE item_id = :item_id";
    $current_stmt = $conn->prepare($current_query);
    $current_stmt->execute([':item_id' => $item_id]);
    $current_item = $current_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$current_item) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to fetch current item data']);
        exit;
    }
    
    $image_path = $current_item['image_path']; // Keep existing image by default
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed types: jpg, jpeg, png, gif']);
            exit;
        }
        
        $new_filename = uniqid() . '.' . $file_ext;
        $upload_path = '../../images/menu/' . $new_filename;
        
        // Create directory if it doesn't exist
        if (!file_exists('../../images/menu')) {
            mkdir('../../images/menu', 0777, true);
        }
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
            exit;
        }
        
        // Delete old image if it exists
        if (!empty($current_item['image_path']) && file_exists('../../' . $current_item['image_path'])) {
            unlink('../../' . $current_item['image_path']);
        }
        $image_path = 'images/menu/' . $new_filename;
    }
    
    $query = "UPDATE menu_item SET name = :name, category_id = :category_id, price = :price, image_path = :image_path, is_available = :is_available WHERE item_id = :item_id";
    $stmt = $conn->prepare($query);
    $success = $stmt->execute([
        ':name' => $name,
        ':category_id' => $category_id,
        ':price' => $price,
        ':image_path' => $image_path,
        ':is_available' => $is_available,
        ':item_id' => $item_id
    ]);
    if ($success) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Menu item updated successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: Could not update item.']);
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} 