<?php
require_once '../../includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['staff_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Staff ID is required']);
    exit();
}

try {
    // Start transaction
    $conn->beginTransaction();

    // Check if staff exists
    $stmt = $conn->prepare("SELECT staff_id, job_title FROM staff WHERE staff_id = ?");
    $stmt->execute([$input['staff_id']]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$staff) {
        throw new Exception('Staff not found');
    }

    // Check if this is the last admin staff member
    if ($staff['job_title'] === 'Admin') {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM staff WHERE job_title = 'Admin'");
        $stmt->execute();
        $admin_count = $stmt->fetchColumn();

        if ($admin_count <= 1) {
            throw new Exception('Cannot delete the last admin staff member');
        }
    }

    // Check if staff has any orders
    $stmt = $conn->prepare("SELECT COUNT(*) FROM `order` WHERE staff_id = ?");
    $stmt->execute([$input['staff_id']]);
    $has_orders = $stmt->fetchColumn() > 0;

    if ($has_orders) {
        throw new Exception('Cannot delete staff member with existing orders. Please transfer orders first.');
    }

    // Delete staff
    $stmt = $conn->prepare("DELETE FROM staff WHERE staff_id = ?");
    $stmt->execute([$input['staff_id']]);

    // Commit transaction
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Staff deleted successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 