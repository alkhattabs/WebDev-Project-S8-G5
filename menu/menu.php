<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Database connection
try {
    $conn = new PDO('mysql:host=localhost;dbname=hadhramaut_restaurant', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Fetch categories for dropdowns
$category_query = "SELECT * FROM menu_category ORDER BY category_name";
$category_stmt = $conn->query($category_query);
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);
$category_stmt->execute(); // Reset pointer for later use

// Build the query based on search and filter
$query = "SELECT m.*, c.category_name 
          FROM menu_item m 
          LEFT JOIN menu_category c ON m.category_id = c.category_id";

$where_clauses = [];
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_clauses[] = "m.name LIKE :search";
    $params[':search'] = '%' . $_GET['search'] . '%';
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where_clauses[] = "m.category_id = :category";
    $params[':category'] = $_GET['category'];
}

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

$query .= " ORDER BY c.category_name, m.name";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu List - Restoran Bandar Hadhramaut</title>
    <link rel="stylesheet" href="../dashboard/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="menu.css?v=<?php echo time(); ?>">
    <script src="menu.js" defer></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>Bandar<br>Hadhramaut</h2>
            </div>
            
            <nav class="nav-menu">
                <a href="../dashboard/">Main Page</a>
                <a href="../tables/">Tables</a>
                <a href="menu.php" class="active">Menu List</a>
                <a href="../orders/">Order</a>
                <a href="../customers/">Customers</a>
                <a href="../staff/">Staff</a>
                <a href="../order_history/history.php">Order History</a>
            </nav>

            <div class="logout-container">
                <a href="../includes/logout.php" class="logout-btn">Log Out</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container">
                <div class="header">
                    <h1>Menu Management</h1>
                    <button class="add-btn" onclick="openAddPopup()">Add New Item</button>
                </div>
                
                <div class="controls">
                    <form method="GET" class="search-filter">
                        <input type="text" name="search" placeholder="Search menu items..." 
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        
                        <select name="category">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>"
                                        <?php echo (isset($_GET['category']) && $_GET['category'] == $category['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button type="submit">Search</button>
                        <a href="menu.php" class="reset-btn">Reset</a>
                    </form>
                </div>

                <div class="menu-list">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price (RM)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($result) > 0): ?>
                                <?php foreach($result as $item): ?>
                                    <tr class="<?php echo $item['is_available'] ? 'available' : 'unavailable'; ?>">
                                        <td class="item-image">
                                            <img src="<?php echo !empty($item['image_path']) ? '../' . $item['image_path'] : '../images/default-food.jpg'; ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        </td>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                        <td class="price"><?php echo number_format($item['price'], 2); ?></td>
                                        <td class="status">
                                            <span class="status-badge">
                                                <?php echo $item['is_available'] ? 'Available' : 'Not Available'; ?>
                                            </span>
                                        </td>
                                        <td class="actions">
                                            <button onclick="editMenuItem(<?php echo $item['item_id']; ?>)" class="edit-btn btn-action">Edit</button>
                                            <button onclick="deleteMenuItem(<?php echo $item['item_id']; ?>)" class="delete-btn btn-action">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="no-items">No menu items found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Menu Item Popup -->
    <div id="addMenuPopup" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closeAddPopup()">&times;</span>
            <h2>Add New Menu Item</h2>
            <form id="addMenuForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="add_name">Item Name</label>
                    <input type="text" id="add_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="add_category_id">Category</label>
                    <select id="add_category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="add_price">Price (RM)</label>
                    <input type="number" id="add_price" name="price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="add_image">Image</label>
                    <input type="file" id="add_image" name="image" accept="image/*">
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="add_is_available" name="is_available" checked>
                    <label for="add_is_available">Available</label>
                </div>
                
                <div class="form-group form-buttons">
                    <button type="button" onclick="closeAddPopup()" class="back-btn">Cancel</button>
                    <button type="submit" class="submit-btn">Add Item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Menu Item Popup -->
    <div id="editMenuPopup" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closeEditPopup()">&times;</span>
            <h2>Edit Menu Item</h2>
            <form id="editMenuForm" enctype="multipart/form-data">
                <input type="hidden" id="edit_item_id" name="item_id">
                
                <div class="form-group">
                    <label for="edit_name">Item Name</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_category_id">Category</label>
                    <select id="edit_category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_price">Price (RM)</label>
                    <input type="number" id="edit_price" name="price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_image">Image</label>
                    <div id="current_image_preview"></div>
                    <input type="file" id="edit_image" name="image" accept="image/*">
                    <small>Leave empty to keep current image</small>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="edit_is_available" name="is_available">
                    <label for="edit_is_available">Available</label>
                </div>
                
                <div class="form-group form-buttons">
                    <button type="button" onclick="closeEditPopup()" class="back-btn">Cancel</button>
                    <button type="submit" class="submit-btn">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 