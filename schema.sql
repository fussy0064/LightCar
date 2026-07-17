-- 1. Kutengeneza Database ya MySQL
CREATE DATABASE IF NOT EXISTS car_sales_db;
USE car_sales_db;

-- 2. Kutengeneza Table ya Users
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Agent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Kutengeneza Table ya Cars
CREATE TABLE IF NOT EXISTS cars (
    car_id INT AUTO_INCREMENT PRIMARY KEY,
    encrypted_make VARCHAR(500) NOT NULL,
    encrypted_model VARCHAR(500) NOT NULL,
    encrypted_price VARCHAR(500) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Kutengeneza Table ya Sales (Inayounganisha Cars na Users katika 3NF)
CREATE TABLE IF NOT EXISTS sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    user_id INT NOT NULL,
    encrypted_customer_name VARCHAR(500) NOT NULL,
    encrypted_sale_price VARCHAR(500) NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Kuingiza Akaunti ya Kwanza ya Admin (Password yake ni admin123)
INSERT INTO users (username, password_hash, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin')
ON DUPLICATE KEY UPDATE user_id=user_id;
