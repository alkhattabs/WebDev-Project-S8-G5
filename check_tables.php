<?php
require_once 'includes/db_connect.php';

try {
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Open file for writing
    $output = fopen('table_structure.txt', 'w');

    // Get order table structure
    $order_sql = "SHOW CREATE TABLE `order`";
    $stmt = $conn->query($order_sql);
    $order_table = $stmt->fetch(PDO::FETCH_ASSOC);
    
    fwrite($output, "Order Table Structure:\n");
    fwrite($output, print_r($order_table, true));
    fwrite($output, "\n\n");

    // Get order_item table structure
    $item_sql = "SHOW CREATE TABLE order_item";
    $stmt = $conn->query($item_sql);
    $item_table = $stmt->fetch(PDO::FETCH_ASSOC);
    
    fwrite($output, "Order Item Table Structure:\n");
    fwrite($output, print_r($item_table, true));
    fwrite($output, "\n\n");

    // Get all tables
    $tables_sql = "SHOW TABLES";
    $stmt = $conn->query($tables_sql);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    fwrite($output, "All Tables:\n");
    foreach ($tables as $table) {
        fwrite($output, "- $table\n");
    }

    // Close file
    fclose($output);

    echo "Table structure has been written to table_structure.txt";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 