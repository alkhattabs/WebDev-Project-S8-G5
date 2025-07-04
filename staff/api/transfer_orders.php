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

if (!isset($input['from_staff_id']) || !isset($input['to_staff_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Both staff IDs are required']);
    exit();
}

try {
    // Start transaction
    $conn->beginTransaction();

    // Check if both staff members exist
    $stmt = $conn->prepare("SELECT staff_id, job_title FROM staff WHERE staff_id IN (?, ?)");
    $stmt->execute([$input['from_staff_id'], $input['to_staff_id']]);
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($staff) !== 2) {
        throw new Exception('One or both staff members not found');
    }

    // Check if the staff member being transferred from is the last admin
    $from_staff = array_filter($staff, function($s) use ($input) {
        return $s['staff_id'] == $input['from_staff_id'];
    });
    $from_staff = reset($from_staff);

    if ($from_staff['job_title'] === 'Admin') {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM staff WHERE job_title = 'Admin'");
        $stmt->execute();
        $admin_count = $stmt->fetchColumn();

        if ($admin_count <= 1) {
            throw new Exception('Cannot transfer orders from the last admin staff member');
        }
    }

    // Transfer all orders
    $stmt = $conn->prepare("UPDATE `order` SET staff_id = ? WHERE staff_id = ?");
    $stmt->execute([$input['to_staff_id'], $input['from_staff_id']]);

    // Commit transaction
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Orders transferred successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 