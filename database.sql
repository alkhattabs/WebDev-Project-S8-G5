-- Create the database
CREATE DATABASE IF NOT EXISTS hadhramaut_restaurant;
USE hadhramaut_restaurant;

-- Staff table
CREATE TABLE staff (
    staff_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    age INT,
    job_title VARCHAR(50) NOT NULL,
    phone_number VARCHAR(15),
    address TEXT,
    email VARCHAR(100) UNIQUE,
    salary DECIMAL(10,2),
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Customer table
CREATE TABLE customer (
    customer_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    age INT,
    phone_number VARCHAR(15),
    email VARCHAR(100) UNIQUE,
    total_spend DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table status enum
CREATE TABLE table_status (
    status_id INT PRIMARY KEY AUTO_INCREMENT,
    status_name VARCHAR(20) NOT NULL UNIQUE
);

-- Restaurant tables
CREATE TABLE restaurant_table (
    table_id INT PRIMARY KEY AUTO_INCREMENT,
    table_number INT NOT NULL UNIQUE,
    capacity INT NOT NULL,
    status_id INT NOT NULL,
    FOREIGN KEY (status_id) REFERENCES table_status(status_id)
);

-- Menu categories
CREATE TABLE menu_category (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(50) NOT NULL UNIQUE
);

-- Menu items
CREATE TABLE menu_item (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES menu_category(category_id)
);

-- Order status enum
CREATE TABLE order_status (
    status_id INT PRIMARY KEY AUTO_INCREMENT,
    status_name VARCHAR(20) NOT NULL UNIQUE
);

-- Order type enum
CREATE TABLE order_type (
    type_id INT PRIMARY KEY AUTO_INCREMENT,
    type_name VARCHAR(20) NOT NULL UNIQUE
);

-- Orders
CREATE TABLE `order` (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    staff_id INT NOT NULL,
    table_id INT,
    order_type_id INT NOT NULL,
    order_status_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES customer(customer_id),
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id),
    FOREIGN KEY (table_id) REFERENCES restaurant_table(table_id),
    FOREIGN KEY (order_type_id) REFERENCES order_type(type_id),
    FOREIGN KEY (order_status_id) REFERENCES order_status(status_id)
);

-- Order items (junction table between orders and menu items)
CREATE TABLE order_item (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES `order`(order_id),
    FOREIGN KEY (item_id) REFERENCES menu_item(item_id)
);

-- Insert initial data for enums
INSERT INTO table_status (status_name) VALUES 
('Available'), ('Occupied'), ('Reserved'), ('Maintenance');

INSERT INTO order_status (status_name) VALUES 
('In Progress'), ('Completed'), ('Cancelled');

INSERT INTO order_type (type_name) VALUES 
('Dine In'), ('Take Away');

-- Insert sample menu categories
INSERT INTO menu_category (category_name) VALUES 
('Appetizers'), ('Main Course'), ('Beverages'), ('Desserts'); 