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

// Update status to inactive
$stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ? AND role = 'agent'");
$stmt->bind_param('i', $agent_id);

if ($stmt->execute()) {
    header('Location: manage_agents.php?msg=Agent deactivated successfully');
} else {
    header('Location: manage_agents.php?msg=Failed to deactivate agent');
}
exit;
