<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "lead_management_system";


// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// SQL to create database
$sql = "CREATE DATABASE IF NOT EXISTS Lead_Management_System";
if ($conn->query($sql) === TRUE) {
  //echo "Database created successfully";
} else {
  echo "Error creating database: " . $conn->error;
}

//$conn->close();
?>
