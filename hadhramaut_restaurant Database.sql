-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2025 at 03:34 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- Create the database
CREATE DATABASE IF NOT EXISTS hadhramaut_restaurant;

--
-- Database: `hadhramaut_restaurant`
--

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `total_spend` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `first_name`, `last_name`, `age`, `phone_number`, `email`, `total_spend`, `created_at`, `updated_at`) VALUES
(14, 'Ahmed', 'Alamri', 28, '7001234567', 'ahmed.alamri@example.com', 0.00, '2025-06-13 01:46:49', '2025-06-13 01:46:49'),
(15, 'Fatima', 'Alshehri', 25, '7009876543', 'fatima.alshehri@example.com', 0.00, '2025-06-13 01:46:49', '2025-06-13 01:46:49'),
(16, 'Salem', 'Baomar', 35, '7012345678', 'salem.baomar@example.com', 120.50, '2025-06-13 01:46:49', '2025-06-13 01:46:49'),
(17, 'Huda', 'Bin Salem', 30, '7011223344', 'huda.salem@example.com', 642.00, '2025-06-13 01:46:49', '2025-06-17 07:38:11'),
(18, 'Mohammed', 'Alharthy', 40, '7023456789', 'mohammed.alharthy@example.com', 45.75, '2025-06-13 01:46:49', '2025-06-13 01:46:49'),
(19, 'Aisha', 'Alkaf', 22, '7034567890', 'aisha.alkaf@example.com', 200.00, '2025-06-13 01:47:12', '2025-06-13 01:47:12'),
(20, 'Yusuf', 'Binladin', 26, '7045678901', 'yusuf.binladin@example.com', 75.25, '2025-06-13 01:47:12', '2025-06-13 01:47:12'),
(21, 'Omar', 'Alhaddad', 31, '7056789012', 'omar.haddad@example.com', 310.00, '2025-06-13 01:47:12', '2025-06-13 01:47:12'),
(22, 'Rania', 'Alharbi', 29, '7067890123', 'rania.alharbi@example.com', 0.00, '2025-06-13 01:47:12', '2025-06-13 01:47:12');

-- --------------------------------------------------------

--
-- Table structure for table `menu_category`
--

CREATE TABLE `menu_category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_category`
--

INSERT INTO `menu_category` (`category_id`, `category_name`) VALUES
(1, 'Appetizers'),
(3, 'Beverages'),
(4, 'Desserts'),
(2, 'Main Course');

-- --------------------------------------------------------

--
-- Table structure for table `menu_item`
--

CREATE TABLE `menu_item` (
  `item_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_item`
--

INSERT INTO `menu_item` (`item_id`, `name`, `category_id`, `price`, `image_path`, `is_available`, `created_at`, `updated_at`) VALUES
(25, 'Chicken Mandi', 2, 45.00, 'images/menu/684b85a5bd2f4.jpeg', 1, '2025-06-13 01:43:58', '2025-06-13 01:57:57'),
(26, 'Fresh Lemon Juice', 3, 10.00, 'images/menu/684b851d4bb7b.jpeg', 1, '2025-06-13 01:43:58', '2025-06-13 01:55:41'),
(27, 'Baklava', 4, 15.00, 'images/menu/684b854875487.jpg', 1, '2025-06-13 01:43:58', '2025-06-13 01:56:24'),
(28, 'Beef Kabsa', 2, 50.00, 'images/menu/684b8594564f0.jpeg', 1, '2025-06-13 01:44:15', '2025-06-13 01:57:40'),
(30, 'Mango Smoothie', 3, 12.00, 'images/menu/684b853395927.jpg', 1, '2025-06-13 01:44:15', '2025-06-13 01:56:03'),
(31, 'Kunafa', 4, 18.00, 'images/menu/684b857e8b6a0.jpeg', 1, '2025-06-13 01:44:15', '2025-06-13 01:57:18'),
(32, 'Shrimp Biryani', 2, 60.00, 'images/menu/684b85b83fa97.jpeg', 1, '2025-06-13 01:44:15', '2025-06-13 01:58:16'),
(33, 'Arabic Coffee', 3, 9.00, 'images/menu/684b84faab2d9.jpg', 1, '2025-06-13 01:44:15', '2025-06-13 01:55:06'),
(35, 'Ice Cream Sundae', 4, 16.00, 'images/menu/684b8561140ce.jpg', 1, '2025-06-13 01:44:15', '2025-06-13 01:56:49');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `staff_id` int(11) NOT NULL,
  `table_id` int(11) DEFAULT NULL,
  `order_type_id` int(11) NOT NULL,
  `order_status_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`order_id`, `customer_id`, `staff_id`, `table_id`, `order_type_id`, `order_status_id`, `order_date`, `total_amount`, `notes`) VALUES
(12, 17, 1, 13, 1, 2, '2025-06-13 01:59:40', 163.00, ''),
(21, NULL, 1, 12, 1, 2, '2025-06-13 02:03:32', 120.00, 'Dine-in by guest, handled by admin'),
(22, NULL, 8, NULL, 2, 1, '2025-06-13 02:03:32', 75.50, 'Takeaway guest, handled by staff@restaurant.com'),
(23, NULL, 1, 14, 1, 2, '2025-06-13 02:03:32', 230.00, 'Walk-in guest, admin assisted'),
(24, NULL, 8, NULL, 2, 1, '2025-06-13 02:03:32', 64.00, 'Guest takeaway, served by staff'),
(25, NULL, 1, 14, 1, 2, '2025-06-16 15:22:50', 107.00, 'Guest Customer: Dr. Nur ziadah'),
(26, 17, 1, 19, 1, 2, '2025-06-17 07:38:11', 159.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`order_item_id`, `order_id`, `item_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(68, 12, 33, 1, 9.00, 9.00),
(69, 12, 28, 1, 50.00, 50.00),
(70, 12, 26, 1, 10.00, 10.00),
(72, 12, 30, 1, 12.00, 12.00),
(73, 12, 32, 1, 60.00, 60.00),
(78, 25, 33, 1, 9.00, 9.00),
(79, 25, 28, 1, 50.00, 50.00),
(80, 25, 35, 3, 16.00, 48.00),
(87, 26, 33, 1, 9.00, 9.00),
(88, 26, 28, 2, 50.00, 100.00),
(89, 26, 35, 1, 16.00, 16.00),
(91, 26, 30, 1, 12.00, 12.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_status`
--

CREATE TABLE `order_status` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status`
--

INSERT INTO `order_status` (`status_id`, `status_name`) VALUES
(3, 'Cancelled'),
(2, 'Completed'),
(1, 'In Progress');

-- --------------------------------------------------------

--
-- Table structure for table `order_type`
--

CREATE TABLE `order_type` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_type`
--

INSERT INTO `order_type` (`type_id`, `type_name`) VALUES
(1, 'Dine In'),
(2, 'Take Away');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_table`
--

CREATE TABLE `restaurant_table` (
  `table_id` int(11) NOT NULL,
  `table_number` int(11) NOT NULL,
  `capacity` int(11) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant_table`
--

INSERT INTO `restaurant_table` (`table_id`, `table_number`, `capacity`, `status_id`) VALUES
(12, 1, 4, 1),
(13, 2, 5, 1),
(14, 3, 4, 1),
(15, 4, 6, 1),
(16, 5, 6, 1),
(17, 6, 6, 1),
(18, 7, 8, 1),
(19, 8, 8, 1),
(20, 9, 10, 1),
(21, 10, 12, 1);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `job_title` varchar(50) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `first_name`, `last_name`, `age`, `job_title`, `phone_number`, `address`, `email`, `salary`, `password_hash`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'User', 19, 'Manager', '899999999999999', '0000', 'admin@restaurant.com', 22.00, '$2y$10$Oma.5n6TP752GmRw/IsXUOz28/jse.ziU.dXueto42TSxh2s4LIRC', '2025-06-10 07:01:44', '2025-06-12 04:15:32'),
(8, 'Mona', 'Alamoudi', 27, 'Receptionist', '7071234567', NULL, 'mona.alamoudi@restaurant.com', 2200.00, '$2a$10$N9qo8uLOickgx2ZMRZoMy.MrqL3Lm7Q8HkGZ5JYv6YH6d3JYJQX5K', '2025-06-13 01:47:55', '2025-06-13 01:51:30'),
(9, 'Khaled', 'Bin Jabr', 33, 'Chef', '7082345678', NULL, 'khaled.jabr@restaurant.com', 4500.00, '$2a$10$N9qo8uLOickgx2ZMRZoMy.MrqL3Lm7Q8HkGZ5JYv6YH6d3JYJQX5K', '2025-06-13 01:47:55', '2025-06-13 01:51:30'),
(10, 'Noura', 'Alsayed', 24, 'Waitress', '7093456789', NULL, 'noura.alsayed@restaurant.com', 2300.00, '$2a$10$N9qo8uLOickgx2ZMRZoMy.MrqL3Lm7Q8HkGZ5JYv6YH6d3JYJQX5K', '2025-06-13 01:47:55', '2025-06-13 01:51:30'),
(11, 'Hassan', 'Alnaqib', 38, 'Supervisor', '7104567890', NULL, 'hassan.alnaqib@restaurant.com', 5000.00, '$2a$10$N9qo8uLOickgx2ZMRZoMy.MrqL3Lm7Q8HkGZ5JYv6YH6d3JYJQX5K', '2025-06-13 01:47:55', '2025-06-13 01:51:30'),
(12, 'Staff', 'Member', 30, 'Cashier', '7111234567', 'Main Branch, Hadhramaut', 'staff@restaurant.com', 3000.00, '$2y$10$cDJec7qUFUYabqGuVNY/p.THP4.sbVQ2ZeWN7Ba1VkYjHPoXENs7S', '2025-06-13 01:49:38', '2025-06-13 01:49:38');

-- --------------------------------------------------------

--
-- Table structure for table `table_status`
--

CREATE TABLE `table_status` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `table_status`
--

INSERT INTO `table_status` (`status_id`, `status_name`) VALUES
(1, 'Available'),
(2, 'Occupied');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `matric` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `menu_category`
--
ALTER TABLE `menu_category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `menu_item`
--
ALTER TABLE `menu_item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `table_id` (`table_id`),
  ADD KEY `order_type_id` (`order_type_id`),
  ADD KEY `order_status_id` (`order_status_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `order_status`
--
ALTER TABLE `order_status`
  ADD PRIMARY KEY (`status_id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Indexes for table `order_type`
--
ALTER TABLE `order_type`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `restaurant_table`
--
ALTER TABLE `restaurant_table`
  ADD PRIMARY KEY (`table_id`),
  ADD UNIQUE KEY `table_number` (`table_number`),
  ADD KEY `status_id` (`status_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `table_status`
--
ALTER TABLE `table_status`
  ADD PRIMARY KEY (`status_id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`matric`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `menu_category`
--
ALTER TABLE `menu_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `menu_item`
--
ALTER TABLE `menu_item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `order_status`
--
ALTER TABLE `order_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_type`
--
ALTER TABLE `order_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `restaurant_table`
--
ALTER TABLE `restaurant_table`
  MODIFY `table_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `table_status`
--
ALTER TABLE `table_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu_item`
--
ALTER TABLE `menu_item`
  ADD CONSTRAINT `menu_item_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `menu_category` (`category_id`);

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`),
  ADD CONSTRAINT `order_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`),
  ADD CONSTRAINT `order_ibfk_3` FOREIGN KEY (`table_id`) REFERENCES `restaurant_table` (`table_id`),
  ADD CONSTRAINT `order_ibfk_4` FOREIGN KEY (`order_type_id`) REFERENCES `order_type` (`type_id`),
  ADD CONSTRAINT `order_ibfk_5` FOREIGN KEY (`order_status_id`) REFERENCES `order_status` (`status_id`);

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`),
  ADD CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_item` (`item_id`);

--
-- Constraints for table `restaurant_table`
--
ALTER TABLE `restaurant_table`
  ADD CONSTRAINT `restaurant_table_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `table_status` (`status_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
