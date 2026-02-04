<?php

$host = "localhost";
$username = "root";
$password = "";


// mysqli connection (NO database name yet)
$mysqli = new mysqli($host, $username, $password);
if ($mysqli->connect_errno) {
    echo "Fail to connect to MYSQL" . $mysqli->connect_error;
    exit();
}

create_database($mysqli);
function create_database($mysqli)
{
    $sql = "CREATE DATABASE IF NOT EXISTS `salon_project`
            DEFAULT CHARACTER SET utf8mb4 
            COLLATE utf8mb4_general_ci";

    if ($mysqli->query($sql)) {
        // echo "Database created successfully";
    } else {
        echo "Database creation failed";
    }
}

// RECALL Database
function select_db($mysqli)
{
    if ($mysqli->select_db("salon_project")) {
        return true;
    } else {
        return false;
    }
}
select_db($mysqli);
create_table($mysqli);

function create_table($mysqli)
{
    // //user 
    $user_sql = "CREATE TABLE IF NOT EXISTS `users`
    (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(60) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    phone VARCHAR(50) NOT NULL UNIQUE,
    role ENUM('superadmin','admin','user') NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($mysqli->query($user_sql) == false) return false;

    // categories
    $category_sql = "CREATE TABLE IF NOT EXISTS `categories`
    (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    incentive_percent DECIMAL(5,2) NOT NULL,
    capacity INT DEFAULT NULL,
    -- status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($mysqli->query($category_sql) == false) return false;

    // add capacity column if missing (for existing DBs)
    $capCol = $mysqli->query("SHOW COLUMNS FROM categories LIKE 'capacity'");
    if ($capCol && $capCol->num_rows === 0) {
        $mysqli->query("ALTER TABLE categories ADD capacity INT DEFAULT NULL");
    }

    //services
    $service_sql = "CREATE TABLE IF NOT EXISTS `services`
    (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    image VARCHAR(255) NOT NULL,
    description TEXT,
    price INT NOT NULL,
    duration INT COMMENT 'minutes',
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
)";
    if ($mysqli->query($service_sql) == false) return false;

    // staff
    $staff_sql = "CREATE TABLE IF NOT EXISTS `staff`
    (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(50) DEFAULT NULL
    )";
    if ($mysqli->query($staff_sql) == false) return false;

    // staff_categories (many-to-many staff <-> categories)
    $staff_categories_sql = "CREATE TABLE IF NOT EXISTS `staff_categories`
    (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    category_id INT NOT NULL,
    UNIQUE (staff_id, category_id),
    FOREIGN KEY (staff_id) REFERENCES staff(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
    )";
    if ($mysqli->query($staff_categories_sql) == false) return false;

    //    appointments
    $appointment_query = "CREATE TABLE IF NOT EXISTS `appointments`
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_group_id VARCHAR(30) NOT NULL,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    staff_id INT DEFAULT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    request TEXT,
    status ENUM('pending','approved','cancelled','completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (appointment_group_id, service_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE SET NULL
)";
    if ($mysqli->query($appointment_query) == false) return false;

    // notification
    $notification_sql = "CREATE TABLE IF NOT EXISTS `notifications` 
    (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    appointment_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
)";
    if ($mysqli->query($notification_sql) == false) return false;

    // promotions
    $promotion_sql = "CREATE TABLE IF NOT EXISTS `promotions`
    (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    discount VARCHAR(50), -- e.g., '20%', 'Buy 1 Get 1'
    status TINYINT(1) DEFAULT 1, -- 1 = active, 0 = inactive
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($mysqli->query($promotion_sql) == false) return false;

}
