<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header("Location: ../login.php");
    exit;
}
include_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lead_id = $_POST['lead_id'] ?? '';
    $note_ids = $_POST['note_ids'] ?? [];

    if (empty($lead_id) || empty($note_ids)) {
        header("Location: lead_details.php?id=$lead_id&error=1");
        exit;
    }

    $agent_id = $_SESSION['user_id'];

    // Dynamic placeholders for note IDs
    $placeholders = implode(',', array_fill(0, count($note_ids), '?'));
    $sql = "DELETE FROM lead_notes WHERE id IN ($placeholders) AND lead_id = ? AND agent_id = ?";
    $stmt = $conn->prepare($sql);

    // Create types string (i = integer)
    $types = str_repeat('i', count($note_ids) + 2); // note_ids + lead_id + agent_id

    // Merge values
    $params = array_merge($note_ids, [$lead_id, $agent_id]);

    // Prepare binding arguments
    $bind_names[] = $types;
    foreach ($params as $key => $value) {
        $bind_names[] = &$params[$key];
    }

    // Bind and execute
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
    $stmt->execute();

    // Redirect after deletion
    header("Location: lead_details.php?id=$lead_id&deleted=1");
    exit;
} else {
    echo "Invalid request.";
    exit;
}
