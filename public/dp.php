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

    //services
    $service_sql = "CREATE TABLE IF NOT EXISTS `services`
    (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    image VARCHAR(255) NOT NULL,
    description TEXT,
    price INT NOT NULL,
    duration INT COMMENT 'minutes',
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
    if ($mysqli->query($service_sql) == false) return false;

    // appointment
    $appointment_sql = "CREATE TABLE IF NOT EXISTS `appointments`
    (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    if ($mysqli->query($appointment_sql) == false) return false;

    // appointment_service
    $appointmentS_sql = "CREATE TABLE IF NOT EXISTS `appointment_services`
    (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    service_id INT NOT NULL,
    status ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
    )";
    if ($mysqli->query($appointmentS_sql) == false) return false;


    // notification
    $notification_sql = "CREATE TABLE IF NOT EXISTS `notifications` 
    (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    appointment_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
    if ($mysqli->query($notification_sql) == false) return false;
}
