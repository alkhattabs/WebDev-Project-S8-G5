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

try {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    $image_path = '';
    
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
        
        $image_path = 'images/menu/' . $new_filename;
    }
    
    $query = "INSERT INTO menu_item (name, category_id, price, image_path, is_available) 
              VALUES ('$name', '$category_id', '$price', '$image_path', $is_available)";
    
    if (mysqli_query($conn, $query)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Menu item added successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($conn); 