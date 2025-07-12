<?php
$servername = "localhost";
$username = "root";
$password = "";

// Connect without selecting database
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS lead_management_system";
if ($conn->query($sql) === TRUE) {
    echo "Database created or already exists.";
} else {
    echo "Error creating database: " . $conn->error;
}

$conn->close();
?>
