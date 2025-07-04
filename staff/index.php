<?php
require_once '../includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch all staff members
$query = "SELECT staff_id, first_name, last_name, age, job_title, phone_number, address, email, salary, created_at 
          FROM staff ORDER BY first_name, last_name";
$stmt = $conn->query($query);
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - Restoran Bandar Hadhramaut</title>
    <link rel="stylesheet" href="../dashboard/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="staff.css?v=<?php echo time(); ?>">
    <script>
        // Add current staff ID to window object
        window.currentStaffId = <?php echo $_SESSION['user_id']; ?>;
    </script>
    <script src="staff.js" defer></script>
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
            <a href="./" class="active">Staff</a>
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
                <h1>Staff Management</h1>
                <button id="addStaffBtn" class="add-btn">Add New Staff</button>
            </div>

            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search staff...">
            </div>

            <div class="staff-grid">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Staff ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Age</th>
                                <th>Job Title</th>
                                <th>Phone Number</th>
                                <th>Address</th>
                                <th>Email</th>
                                <th>Salary (RM)</th>
                                <th>Join Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($staff as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['staff_id']); ?></td>
                                    <td><?php echo htmlspecialchars($member['first_name']); ?></td>
                                    <td><?php echo htmlspecialchars($member['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($member['age']); ?></td>
                                    <td><?php echo htmlspecialchars($member['job_title']); ?></td>
                                    <td><?php echo htmlspecialchars($member['phone_number']); ?></td>
                                    <td><?php echo htmlspecialchars($member['address']); ?></td>
                                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                                    <td><?php echo number_format($member['salary'], 2); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($member['created_at'])); ?></td>
                                    <td class="actions">
                                        <button onclick="editStaff(<?php echo $member['staff_id']; ?>)" class="edit-btn btn-action">Edit</button>
                                        <button onclick="deleteStaff(<?php echo $member['staff_id']; ?>)" class="delete-btn btn-action">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Staff Modal -->
    <div id="staffModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">Add New Staff</h2>
            <form id="staffForm">
                <input type="hidden" id="staffId" name="staff_id">
                
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="first_name" required>
                </div>

                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="last_name" required>
                </div>

                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" min="18" max="100" required>
                </div>

                <div class="form-group">
                    <label for="jobTitle">Job Title</label>
                    <input type="text" id="jobTitle" name="job_title" required>
                </div>

                <div class="form-group">
                    <label for="phoneNumber">Phone Number</label>
                    <input type="tel" id="phoneNumber" name="phone_number" required>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" required></textarea>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="salary">Salary (RM)</label>
                    <input type="number" id="salary" name="salary" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" minlength="6">
                    <small>Leave empty to keep existing password when editing</small>
                </div>

                <div class="form-buttons">
                    <button type="button" onclick="closeModal()" class="cancel-btn">Cancel</button>
                    <button type="submit" class="submit-btn">Save</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 