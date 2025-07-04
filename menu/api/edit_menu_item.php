<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'hadhramaut_restaurant');

// Check connection
if (!$conn) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
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
    $item_id = mysqli_real_escape_string($conn, $_GET['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    // Fetch current item data for image handling
    $current_query = "SELECT image_path FROM menu_item WHERE item_id = '$item_id'";
    $current_result = mysqli_query($conn, $current_query);
    
    if (!$current_result) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to fetch current item data']);
        exit;
    }
    
    $current_item = mysqli_fetch_assoc($current_result);
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
    
    $query = "UPDATE menu_item 
              SET name = '$name', 
                  category_id = '$category_id', 
                  price = '$price', 
                  image_path = '$image_path', 
                  is_available = $is_available 
              WHERE item_id = '$item_id'";
    
    if (mysqli_query($conn, $query)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Menu item updated successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($conn); 