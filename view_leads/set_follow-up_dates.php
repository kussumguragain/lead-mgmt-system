<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header("Location: login.php");
    exit;
}

include_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lead_id = $_POST['lead_id'] ?? '';
    $follow_up_date = $_POST['follow_up_date'] ?? '';

    if (empty($lead_id) || empty($follow_up_date)) {
        echo "Lead ID and follow-up date are required.";
        exit;
    }

    $agent_id = $_SESSION['user_id'];

    // Check if the lead is assigned to this agent
    $check = $conn->prepare("SELECT id FROM leads WHERE id = ? AND agent_id = ?");
    $check->bind_param("ii", $lead_id, $agent_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        echo "Unauthorized access.";
        exit;
    }

    // Update the follow-up date
    $stmt = $conn->prepare("UPDATE leads SET follow_up_date = ? WHERE id = ?");
    $stmt->bind_param("si", $follow_up_date, $lead_id);

    if ($stmt->execute()) {
        header("Location: lead_details.php?id=$lead_id&followup_set=1");
        exit;
    } else {
        echo "Failed to set follow-up date.";
        exit;
    }
} else {
    echo "Invalid request method.";
    exit;
}
