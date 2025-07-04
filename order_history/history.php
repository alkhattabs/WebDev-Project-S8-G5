<?php
require_once '../includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch all orders with related information
$query = "SELECT o.order_id, o.order_date, ot.type_name, rt.table_number, 
                 o.total_amount, os.status_name, c.first_name as customer_fname, 
                 c.last_name as customer_lname, s.first_name as staff_fname, 
                 s.last_name as staff_lname
          FROM `order` o
          LEFT JOIN order_type ot ON o.order_type_id = ot.type_id
          LEFT JOIN restaurant_table rt ON o.table_id = rt.table_id
          LEFT JOIN order_status os ON o.order_status_id = os.status_id
          LEFT JOIN customer c ON o.customer_id = c.customer_id
          LEFT JOIN staff s ON o.staff_id = s.staff_id
          ORDER BY o.order_date DESC";
$stmt = $conn->query($query);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Restoran Bandar Hadhramaut</title>
    <link rel="stylesheet" href="../dashboard/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="order-history.css?v=<?php echo time(); ?>">
    <script>
        window.onerror = function(msg, url, lineNo, columnNo, error) {
            console.error('JavaScript error:', msg);
            console.error('Script URL:', url);
            console.error('Line:', lineNo, 'Column:', columnNo);
            console.error('Error object:', error);
            return false;
        };

        // Add current staff ID to window object
        window.currentStaffId = <?php echo $_SESSION['user_id']; ?>;
    </script>
    <script src="order-history.js?v=<?php echo time(); ?>" defer></script>
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
            <a href="../orders/">Order</a>
            <a href="../customers/">Customers</a>
            <a href="../staff/">Staff</a>
            <a href="../order_history/history.php" class="active">Order History</a>
        </nav>

        <div class="logout-container">
            <a href="../includes/logout.php" class="logout-btn">Log Out</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="header">
                <h1>Order History</h1>
                <div class="header-controls">
                    <input type="text" id="searchInput" placeholder="Search orders...">
                    <select id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="Completed">Completed</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                    <select id="typeFilter">
                        <option value="">All Types</option>
                        <option value="Dine In">Dine In</option>
                        <option value="Take Away">Take Away</option>
                    </select>
                </div>
            </div>
            <div class="order-grid">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date & Time</th>
                                <th>Type</th>
                                <th>Table No</th>
                                <th>Customer</th>
                                <th>Staff</th>
                                <th>Items</th>
                                <th>Total (RM)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                                <tr data-order-id="<?php echo $order['order_id']; ?>">
                                    <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($order['type_name']); ?></td>
                                    <td><?php echo $order['table_number'] ? htmlspecialchars($order['table_number']) : '-'; ?></td>
                                    <td>
                                        <?php 
                                        if ($order['customer_fname']) {
                                            echo htmlspecialchars($order['customer_fname'] . ' ' . $order['customer_lname']);
                                        } else {
                                            echo 'Guest';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['staff_fname'] . ' ' . $order['staff_lname']); ?></td>
                                    <td>
                                        <button onclick="viewItems(<?php echo $order['order_id']; ?>)" class="view-items-btn btn-action btn-secondary">
                                            View Items
                                        </button>
                                    </td>
                                    <td><?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $order['status_name'])); ?>">
                                            <?php echo htmlspecialchars($order['status_name']); ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <button type="button" onclick="editOrder(<?php echo $order['order_id']; ?>)" class="edit-btn btn-action">Edit</button>
                                        <button onclick="viewReceipt(<?php echo $order['order_id']; ?>)" class="receipt-btn btn-action btn-secondary">Receipt</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items Modal -->
    <div id="itemsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Items</h2>
                <span class="close">&times;</span>
            </div>
            <div id="itemsList"></div>
            <div class="modal-buttons">
                <button class="close-btn">Close</button>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div id="receiptModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Receipt</h2>
                <span class="close" onclick="closeReceiptModal()">&times;</span>
            </div>
            <div id="receiptContent"></div>
            <div class="modal-buttons">
                <button onclick="printReceipt()" class="print-btn">Print</button>
                <button onclick="closeReceiptModal()" class="close-btn">Close</button>
            </div>
        </div>
    </div>

    <!-- Edit Order Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Order #<span id="editOrderNumber"></span></h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editOrderForm">
                <input type="hidden" id="editOrderId" name="order_id">
                
                <div class="form-content">
                    <div class="form-group">
                        <label for="orderType">Order Type:</label>
                        <select id="orderType" name="order_type_id" required>
                            <option value="1">Dine In</option>
                            <option value="2">Take Away</option>
                        </select>
                    </div>

                    <div class="form-group" id="tableGroup">
                        <label for="tableSelect">Table Number:</label>
                        <select id="tableSelect" name="table_id">
                            <option value="">Select Table</option>
                            <?php
                            // Fetch available tables
                            $table_query = "SELECT table_id, table_number FROM restaurant_table ORDER BY table_number";
                            $table_stmt = $conn->query($table_query);
                            while ($table = $table_stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='" . $table['table_id'] . "'>" . $table['table_number'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Customer Type:</label>
                        <div class="customer-type-toggle">
                            <button type="button" data-type="registered" class="active">Registered Customer</button>
                            <button type="button" data-type="guest">Guest</button>
                        </div>
                    </div>

                    <div class="form-group registered-input">
                        <label for="customerId">Select Customer:</label>
                        <select id="customerId" name="customer_id">
                            <option value="">Select Customer</option>
                            <?php
                            // Fetch registered customers
                            $customer_query = "SELECT customer_id, CONCAT(first_name, ' ', last_name) as name 
                                            FROM customer 
                                            ORDER BY first_name, last_name";
                            $customer_stmt = $conn->query($customer_query);
                            while ($customer = $customer_stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='" . $customer['customer_id'] . "'>" . 
                                     htmlspecialchars($customer['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group guest-input" style="display: none;">
                        <label for="guestName">Guest Name:</label>
                        <input type="text" id="guestName" name="guest_name" placeholder="Enter guest name">
                    </div>

                    <div class="form-group">
                        <label for="orderDate">Order Date:</label>
                        <input type="text" id="orderDate" readonly>
                    </div>

                    <div class="form-group">
                        <label for="orderStatus">Order Status:</label>
                        <select id="orderStatus" name="order_status_id" required>
                            <?php
                            $status_query = "SELECT status_id, status_name FROM order_status ORDER BY status_id";
                            $status_stmt = $conn->query($status_query);
                            while ($status = $status_stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='" . $status['status_id'] . "'>" . $status['status_name'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes:</label>
                        <textarea id="notes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="order-items-section">
                        <h3>Order Items</h3>
                        <div class="order-items-controls">
                            <button type="button" class="add-item-btn" onclick="showAddItemModal()">Add Item</button>
                        </div>
                        <div id="orderItemsList"></div>
                        <div class="total-section">
                            <span>Total Amount: RM </span>
                            <span id="orderTotal">0.00</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="save-btn">Save Changes</button>
                    <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div id="addItemModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Menu Item</h2>
                <span class="close" onclick="closeAddItemModal()">&times;</span>
            </div>
            <div class="form-content">
                <div class="form-group">
                    <label for="menuItem">Select Item:</label>
                    <select id="menuItem">
                        <option value="">Select an item</option>
                        <?php
                        // Fetch menu items
                        $menu_query = "SELECT i.item_id, i.name, i.price, c.category_name 
                                     FROM menu_item i 
                                     JOIN menu_category c ON i.category_id = c.category_id 
                                     WHERE i.is_available = 1 
                                     ORDER BY c.category_name, i.name";
                        $menu_stmt = $conn->query($menu_query);
                        $current_category = '';
                        while ($item = $menu_stmt->fetch(PDO::FETCH_ASSOC)) {
                            if ($current_category != $item['category_name']) {
                                if ($current_category != '') echo '</optgroup>';
                                echo '<optgroup label="' . htmlspecialchars($item['category_name']) . '">';
                                $current_category = $item['category_name'];
                            }
                            echo "<option value='" . $item['item_id'] . "' data-price='" . $item['price'] . "'>" . 
                                 htmlspecialchars($item['name']) . " (RM " . number_format($item['price'], 2) . ")</option>";
                        }
                        if ($current_category != '') echo '</optgroup>';
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="itemQuantity">Quantity:</label>
                    <div class="quantity-control">
                        <button type="button" onclick="adjustQuantity(-1)">-</button>
                        <input type="number" id="itemQuantity" value="1" min="1">
                        <button type="button" onclick="adjustQuantity(1)">+</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="save-btn" onclick="addItemToOrder()">Add Item</button>
                <button type="button" class="cancel-btn" onclick="closeAddItemModal()">Cancel</button>
            </div>
        </div>
    </div>
</body>
</html> 