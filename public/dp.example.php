<?php
// dp.example.php
// Copy this file to public/dp.php and update credentials for your local setup

$host = "localhost";       // Your database host
$user = "root";            // Your database username
$password = "";            // Your database password
$database = "salon_project"; // Database name

// DO NOT CHANGE ANYTHING BELOW
$mysqli = new mysqli($host, $user, $password, $database);

// Check connection
if ($mysqli->connect_errno) {
    echo "Fail to connect to MySQL: " . $mysqli->connect_error;
    exit();
}
