<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../includes/db_connect.php';

// Fetch table statuses from database
try {
    $stmt = $conn->query("SELECT table_id, table_number, capacity, status_id FROM restaurant_table ORDER BY table_number");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching tables: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Table - Bandar Hadhramaut POS</title>
    <link rel="stylesheet" href="../dashboard/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="tables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="table-page.css?v=<?php echo time(); ?>">
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
                <a href="../tables/" class="active">Tables</a>
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
            <div class="container">
                <div class="content-wrapper">
                    <h1>Kindly select a table:</h1>
                    
                    <div class="tables-header">
                        <button id="addTableBtn" class="add-table-btn">Add New Table</button>
                    </div>

                    <div class="tables-grid">
                    <?php 
                    // Get the highest table number
                    $highest_table = 0;
                    foreach($tables as $table) {
                        if ($table['table_number'] > $highest_table) {
                            $highest_table = $table['table_number'];
                        }
                    }
                    
                    // Display all tables up to the highest number
                    for($i = 1; $i <= max(10, $highest_table); $i++): 
                        $table = array_filter($tables, function($t) use ($i) {
                            return $t['table_number'] == $i;
                        });
                        $table = reset($table);
                        $isOccupied = $table && $table['status_id'] == 2; // 2 = Occupied in our database
                    ?>
                        <div class="table-item <?php echo $isOccupied ? 'occupied' : 'available'; ?>" 
                             data-table-id="<?php echo $table ? $table['table_id'] : $i; ?>">
                            <div class="table-icon">
                                <span class="table-number"><?php echo $i; ?></span>
                                <?php if ($table): ?>
                                <span class="table-capacity"><?php echo $table['capacity']; ?> seats</span>
                                <?php if (!$isOccupied): ?>
                                <div class="table-actions">
                                    <button class="edit-table-btn" title="Edit Table">✎</button>
                                    <button class="delete-table-btn" title="Delete Table">×</button>
                                </div>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                    </div>

                    <div class="table-controls">
                        <div class="table-legend">
                            <div class="legend-item">
                                <span class="status-dot occupied"></span>
                                <span>Occupied</span>
                            </div>
                            <div class="legend-item">
                                <span class="status-dot available"></span>
                                <span>Available</span>
                            </div>
                        </div>

                        <button id="selectTableBtn" class="select-table-btn" disabled>Select Table</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Table Modal -->
    <div id="addTableModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Table</h2>
                <span class="close">&times;</span>
            </div>
            <form id="addTableForm">
                <div class="form-group">
                    <label for="tableNumber">Table Number:</label>
                    <input type="number" id="tableNumber" name="table_number" min="1" required>
                </div>
                <div class="form-group">
                    <label for="capacity">Capacity:</label>
                    <input type="number" id="capacity" name="capacity" min="1" value="4" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="save-btn">Add Table</button>
                    <button type="button" class="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Table Modal -->
    <div id="editTableModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Table</h2>
                <span class="close">&times;</span>
            </div>
            <form id="editTableForm">
                <input type="hidden" id="editTableId" name="table_id">
                <div class="form-group">
                    <label for="editTableNumber">Table Number:</label>
                    <input type="number" id="editTableNumber" disabled>
                </div>
                <div class="form-group">
                    <label for="editCapacity">Capacity:</label>
                    <input type="number" id="editCapacity" name="capacity" min="1" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="save-btn">Save Changes</button>
                    <button type="button" class="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <script src="tables.js?v=<?php echo time(); ?>"></script>
</body>
</html> 