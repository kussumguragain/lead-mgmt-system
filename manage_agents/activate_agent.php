<?php
session_start();

// Only admin can access
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

include '../config/db.php';

if (!isset($_GET['id'])) {
    header('Location: manage_agents.php?msg=Invalid agent ID');
    exit;
}

$agent_id = intval($_GET['id']);

// Get current status
$stmt = $conn->prepare("SELECT status FROM users WHERE id = ? AND role = 'agent'");
$stmt->bind_param('i', $agent_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Agent not found
    header('Location: manage_agents.php?msg=Agent not found');
    exit;
}

$agent = $result->fetch_assoc();
$current_status = $agent['status'];

// Toggle status
$new_status = ($current_status === 'active') ? 'inactive' : 'active';

// Update status
$stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
$stmt->bind_param('si', $new_status, $agent_id);

if ($stmt->execute()) {
    header("Location: manage_agents.php?msg=Agent status updated to $new_status");
} else {
    header("Location: manage_agents.php?msg=Failed to update status");
}
exit;
