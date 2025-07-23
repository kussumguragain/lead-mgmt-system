<?php
session_start();

// Only allow logged-in agents
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header("Location:../login.php");
    exit;
}

include_once '../config/db.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lead_id = $_POST['lead_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $follow_up_date = $_POST['follow_up_date'] ?? '';

    if (empty($lead_id) || empty($status)) {
        echo "Lead ID and status are required.";
        exit;
    }

    $agent_id = $_SESSION['user_id'];

    // Update the lead (only if assigned to this agent)
    $sql = "UPDATE leads SET status = ?, follow_up_date = ? WHERE id = ? AND id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $status, $follow_up_date, $lead_id, $agent_id);

    if ($stmt->execute()) {
        header("Location: view_assigned_leads.php?success=1");
        exit;
    } else {
        echo "Failed to update lead.";
        exit;
    }
} else {
    echo "Invalid request method.";
    exit;
}
?>
