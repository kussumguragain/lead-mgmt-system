<?php
// update_lead_status.php
session_start();
include_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lead_id = intval($_POST['lead_id'] ?? 0);
    $lead_stage = trim($_POST['lead_stage'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $follow_up_date = $_POST['follow_up_date'] ?? null;

    // Validation
    if (empty($lead_id) || empty($lead_stage) || empty($status)) {
        die("Lead ID, Lead Stage, and Status are required.");
    }

    // Ensure follow_up_date is either a valid date or NULL
    if (!empty($follow_up_date)) {
        $date_check = date_create($follow_up_date);
        if (!$date_check) {
            die("Invalid follow-up date.");
        }
    } else {
        $follow_up_date = null;
    }

    // Update lead
    $stmt = $conn->prepare("UPDATE leads SET lead_stage = ?, status = ?, follow_up_date = ? WHERE id = ? AND assigned_to = ?");
    $stmt->bind_param("sssii", $lead_stage, $status, $follow_up_date, $lead_id, $_SESSION['user_id']);

   if ($stmt->execute()) {
    header("Location: view_assigned_leads.php?success=1");
    exit;
}
else {
        die("Failed to update lead.");
    }
} else {
    die("Invalid request.");
}
