<?php
session_start();

// Only allow access if logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../config/db.php';

if (isset($_GET['id'])) {
    $lead_id = intval($_GET['id']);

    // Delete lead from database
    $stmt = $conn->prepare("DELETE FROM leads WHERE id = ?");
    $stmt->bind_param("i", $lead_id);

    if ($stmt->execute()) {
        header("Location: view_leads.php?msg=deleted");
        exit;
    } else {
        echo "Error deleting lead.";
    }
} else {
    header("Location: view_leads.php");
    exit;
}
?>
