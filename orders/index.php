<?php
require_once '../includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Get staff information
$staff_id = $_SESSION['user_id'];
$staff_query = "SELECT staff_id, CONCAT(first_name, ' ', last_name) as staff_name 
                FROM staff 
                WHERE staff_id = :staff_id";
$staff_stmt = $conn->prepare($staff_query);
$staff_stmt->execute(['staff_id' => $staff_id]);
$staff = $staff_stmt->fetch(PDO::FETCH_ASSOC);

// Get registered customers
$customers_query = "SELECT customer_id, CONCAT(first_name, ' ', last_name) as customer_name 
                   FROM customer 
                   ORDER BY first_name, last_name";
$customers_stmt = $conn->query($customers_query);

// Get available tables
$tables_query = "SELECT t.table_id, t.table_number, t.capacity, s.status_name 
                FROM restaurant_table t 
                JOIN table_status s ON t.status_id = s.status_id 
                WHERE s.status_name = 'Available'
                ORDER BY t.table_number";
$tables_stmt = $conn->query($tables_query);

// Get available menu items
$menu_query = "SELECT m.item_id, m.name, m.price, m.image_path, c.category_name 
              FROM menu_item m 
              JOIN menu_category c ON m.category_id = c.category_id 
              WHERE m.is_available = 1 
              ORDER BY c.category_name, m.name";
$menu_stmt = $conn->query($menu_query);

// Get menu categories for filter
$categories_query = "SELECT DISTINCT c.category_id, c.category_name 
                    FROM menu_category c 
                    JOIN menu_item m ON c.category_id = m.category_id 
                    WHERE m.is_available = 1 
                    ORDER BY c.category_name";
$categories_stmt = $conn->query($categories_query);

// Check if this is a takeaway order
$is_takeaway = isset($_GET['type']) && $_GET['type'] === 'takeaway';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Restoran Bandar Hadhramaut</title>
    <link rel="stylesheet" href="../dashboard/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="orders.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="orders-page.css?v=<?php echo time(); ?>">
    <script src="order.js" defer></script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h2>Bandar<br>Hadhramaut</h2>
        </div>
        
        <nav class="nav-menu">
            <a href="../dashboard/">Main Page</a>
            <a href="../tables/">Tables</a>
            <a href="../menu/menu.php">Menu List</a>
            <a href="./" class="active">Order</a>
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
            <!-- Menu Section -->
            <div class="menu-section">
                <div class="header">
                    <h1>Place Order</h1>
                    <div class="menu-controls">
                        <div class="search-container">
                            <input type="text" id="searchInput" placeholder="Search menu items...">
                            <button id="applySearch" class="search-btn">Apply</button>
                            <button id="resetSearch" class="search-btn reset">Reset</button>
                        </div>
                        <select id="categoryFilter">
                            <option value="">All Categories</option>
                            <?php while($category = $categories_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?php echo htmlspecialchars($category['category_name']); ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="menu-grid">
                    <?php while($item = $menu_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="menu-item" data-id="<?php echo $item['item_id']; ?>" 
                             data-category="<?php echo htmlspecialchars($item['category_name']); ?>">
                            <div class="image-container">
                                <img src="<?php echo !empty($item['image_path']) ? '../' . $item['image_path'] : '../images/default-food.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="menu-item-details">
                                <div>
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="category"><?php echo htmlspecialchars($item['category_name']); ?></p>
                                </div>
                                <p class="price">RM <?php echo number_format($item['price'], 2); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Order Section -->
            <div class="order-section">
                <div class="order-header">
                    <h2>Current Order</h2>
                    <hr class="header-divider">
                    <!-- Staff Information -->
                    <div class="staff-info">
                        <span>Staff: <?php echo htmlspecialchars($staff['staff_name']); ?></span>
                        <input type="hidden" id="staffId" value="<?php echo $staff['staff_id']; ?>">
                    </div>

                    <!-- Customer Information -->
                    <div class="customer-info">
                        <div class="customer-type">
                            <button type="button" class="active" data-type="guest">Guest</button>
                            <button type="button" data-type="registered">Registered</button>
                        </div>
                        
                        <!-- Guest Customer Input -->
                        <div class="guest-input">
                            <input type="text" id="guestName" placeholder="Enter guest name">
                        </div>
                        
                        <!-- Registered Customer Select -->
                        <div class="registered-input" style="display: none;">
                            <select id="customerId">
                                <option value="">Select Customer</option>
                                <?php while($customer = $customers_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?php echo $customer['customer_id']; ?>">
                                        <?php echo htmlspecialchars($customer['customer_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="order-type">
                        <button type="button" class="<?php echo $is_takeaway ? 'active' : ''; ?>" data-type="takeaway">Take Away</button>
                        <button type="button" class="<?php echo !$is_takeaway ? 'active' : ''; ?>" data-type="dinein">Dine In</button>
                    </div>
                    <div class="table-select" <?php echo $is_takeaway ? 'style="display: none;"' : ''; ?>>
                        <select id="tableSelect">
                            <option value="">Select Table</option>
                            <?php while($table = $tables_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?php echo $table['table_id']; ?>">
                                    Table <?php echo htmlspecialchars($table['table_number']); ?> 
                                    (Capacity: <?php echo htmlspecialchars($table['capacity']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="order-items">
                    <!-- Order items will be dynamically added here -->
                </div>
                <div class="order-summary">
                    <div class="total">
                        <span>Total:</span>
                        <span>RM 0.00</span>
                    </div>
                    <button type="button" class="place-order-btn">Place Order</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set initial order type based on URL parameter
        window.initialOrderType = '<?php echo $is_takeaway ? "takeaway" : "dinein"; ?>';
    </script>
</body>
</html> 