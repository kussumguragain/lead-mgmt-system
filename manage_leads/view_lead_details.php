<?php
session_start();

// Only allow access if logged in as admin or agent
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'agent'])) {
    header("Location: ../login.php");
    exit;
}

include '../config/config.php';

$lead_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($lead_id <= 0) {
    die("Invalid lead ID.");
}

// Fetch lead details with assigned agent name
$sql = "SELECT leads.*, users.name AS agent_name 
        FROM leads 
        LEFT JOIN users ON leads.assigned_to = users.id 
        WHERE leads.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lead_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Lead not found.");
}

$lead = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Lead Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2>Lead Details</h2>
    <div class="card p-4">
        <h4><?= htmlspecialchars($lead['name']); ?></h4>
        <p><strong>Email:</strong> <?= htmlspecialchars($lead['email']); ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($lead['phone']); ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($lead['status']); ?></p>
        <p><strong>Assigned To:</strong> <?= $lead['agent_name'] ? htmlspecialchars($lead['agent_name']) : '<em>Not Assigned</em>'; ?></p>
        <p><strong>Created At:</strong> <?= htmlspecialchars($lead['created_at']); ?></p>
        <p><strong>Updated At:</strong> <?= htmlspecialchars($lead['updated_at']); ?></p>
        
        <a href="view_leads.php" class="btn btn-secondary mt-3">Back to Leads</a>
    </div>
</div>

</body>
</html>
