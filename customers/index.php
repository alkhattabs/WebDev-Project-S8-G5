<?php
require_once '../includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch all customers
$query = "SELECT customer_id, first_name, last_name, phone_number, age, email, total_spend, created_at FROM customer ORDER BY first_name, last_name";
$stmt = $conn->query($query);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Restoran Bandar Hadhramaut</title>
    <link rel="stylesheet" href="../dashboard/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="customers.css?v=<?php echo time(); ?>">
    <script src="customers.js" defer></script>
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
            <a href="./" class="active">Customers</a>
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
                <h1>Customer Management</h1>
                <button id="addCustomerBtn" class="add-btn">Add New Customer</button>
            </div>

            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search customers...">
            </div>

            <div class="customers-grid">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Customer ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Customer Age</th>
                                <th>Phone Number</th>
                                <th>Email Address</th>
                                <th>Total Spending (RM)</th>
                                <th>Registration Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                                <tr data-id="<?php echo $customer['customer_id']; ?>">
                                    <td><?php echo htmlspecialchars($customer['customer_id']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['first_name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['last_name']); ?></td>
                                    <td><?php echo $customer['age'] ? htmlspecialchars($customer['age']) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone_number']); ?></td>
                                    <td><?php echo $customer['email'] ? htmlspecialchars($customer['email']) : '-'; ?></td>
                                    <td><?php echo number_format($customer['total_spend'], 2); ?></td>
                                    <td><?php echo date('d M Y', strtotime($customer['created_at'])); ?></td>
                                    <td class="actions">
                                        <button class="edit-btn" onclick="editCustomer(<?php echo $customer['customer_id']; ?>)">Edit</button>
                                        <button class="delete-btn" onclick="deleteCustomer(<?php echo $customer['customer_id']; ?>)">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Customer Modal -->
    <div id="customerModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Add New Customer</h2>
            <form id="customerForm">
                <input type="hidden" id="customerId">
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name <span class="required">*</span></label>
                        <input type="text" id="firstName" name="firstName" required 
                               placeholder="Enter first name">
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name <span class="required">*</span></label>
                        <input type="text" id="lastName" name="lastName" required 
                               placeholder="Enter last name">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="age">Age</label>
                        <input type="number" id="age" name="age" min="1" max="150" 
                               placeholder="Enter age">
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phoneNumber" name="phoneNumber" required 
                               placeholder="Enter phone number">
                    </div>
                </div>
                <div class="form-group full-width">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" 
                           placeholder="Enter email address">
                </div>
                <div class="form-buttons">
                    <button type="button" class="cancel-btn">Cancel</button>
                    <button type="submit" class="save-btn">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 