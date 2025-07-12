<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lead_management_system";

// 1. Connect to MySQL server (no DB selected)
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Create database if not exists
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql_create_db) === TRUE) {
    echo "Database '$dbname' created or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// 3. Close connection and reconnect selecting the database
$conn->close();
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 4. Create users table (store both admin and agents)
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'agent') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active'
)";

if ($conn->query($sql_users) === TRUE) {
    echo "Table 'users' created successfully.<br>";
} else {
    die("Error creating 'users' table: " . $conn->error);
}

// 5. Create leads table
$sql_leads = "CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    contact VARCHAR(255),
    address VARCHAR(255),
    status ENUM('new', 'contacted', 'converted', 'lost') DEFAULT 'new',
    assigned_to INT NULL,
    created_at DATE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql_leads) === TRUE) {
    echo "Table 'leads' created successfully.<br>";
} else {
    die("Error creating 'leads' table: " . $conn->error);
}

// 6. Insert demo admin and agent users only if users table is empty
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Hash passwords with PHP function
    $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
    $agent_password = password_hash("agent123", PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");

    // Insert Admin
    $stmt->bind_param("ssss", $admin_name, $admin_email, $admin_pass, $admin_role);
    $admin_name = "Kusum Guragain";
    $admin_email = "kusum@gmail.com";
    $admin_pass = $admin_password;
    $admin_role = "admin";
    $stmt->execute();

    // Insert Agent
    $stmt->bind_param("ssss", $agent_name, $agent_email, $agent_pass, $agent_role);
    $agent_name = "Karuna Timsina";
    $agent_email = "karuna@gmail.com";
    $agent_pass = $agent_password;
    $agent_role = "agent";
    $stmt->execute();

    echo "Demo admin and agent users inserted successfully.<br>";
} else {
    echo "Users already exist. Skipping user insertion.<br>";
}

// 7. Insert demo leads only if leads table is empty
$result = $conn->query("SELECT COUNT(*) as count FROM leads");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Get agent id to assign leads
    $agent_result = $conn->query("SELECT id FROM users WHERE role='agent' LIMIT 1");
    $agent = $agent_result->fetch_assoc();
    $agent_id = $agent['id'];

    $stmt = $conn->prepare("INSERT INTO leads (customer_name, contact, address, status, assigned_to, created_at) VALUES (?, ?, ?, ?, ?, CURDATE())");

    // Lead 1
    $stmt->bind_param("sssis", $customer_name, $contact, $address, $status, $assigned_to);
    $customer_name = "Hari BK";
    $contact = "9845123456";
    $address = "Lalitpur";
    $status = "new";
    $assigned_to = $agent_id;
    $stmt->execute();

    // Lead 2
    $customer_name = "Gita Rana";
    $contact = "9876543210";
    $address = "Kathmandu";
    $status = "contacted";
    $assigned_to = $agent_id;
    $stmt->execute();

    echo "Demo leads inserted successfully.<br>";
} else {
    echo "Leads already exist. Skipping lead insertion.<br>";
}

$conn->close();
?>
