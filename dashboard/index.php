<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bandar Hadhramaut POS</title>
    <link rel="stylesheet" href="dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="main-page.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>Bandar<br>Hadhramaut</h2>
            </div>
            
            <nav class="nav-menu">
                <a href="../dashboard/" class="active">Main Page</a>
                <a href="../tables/">Tables</a>
                <a href="../menu/menu.php">Menu List</a>
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
            <h1>Welcome to POS of<br>Bandar Hadhramaut</h1>
            
            <div class="order-options">
                <a href="../tables/" class="order-btn dine-in">Dine In</a>
                <a href="../orders/?type=takeaway" class="order-btn take-away">Take Away</a>
            </div>
        </div>
    </div>
</body>
</html> 