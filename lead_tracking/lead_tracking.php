<?php
session_start();
include_once '../config/db.php';

// Only allow logged-in agents
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header("Location:../login.php");
    exit;
}

$agent_id = $_SESSION['user_id'];

// Fetch leads grouped by lead_stage
$stages = ['New', 'Contacted', 'Converted', 'Lost'];
$leads_by_stage = [];

foreach ($stages as $stage) {
    $stmt = $conn->prepare("SELECT id, customer_name, phone, status, follow_up_date FROM leads WHERE assigned_to = ? AND lead_stage = ? ORDER BY follow_up_date ASC");
    $stmt->bind_param("is", $agent_id, $stage);
    $stmt->execute();
    $result = $stmt->get_result();
    $leads_by_stage[$stage] = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lead Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h2>
              <a href="../agent_dashboard.php" style="text-decoration: none; color: inherit;">Lead Tracking</a>
    </h2>

    <hr>

    <div class="row">
        <?php foreach ($leads_by_stage as $stage => $leads): ?>
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-header text-white 
                        <?= $stage == 'New' ? 'bg-primary' : ($stage == 'Contacted' ? 'bg-warning' : ($stage == 'Converted' ? 'bg-success' : 'bg-danger')) ?>">
                        <?= $stage ?>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($leads)): ?>
                            <?php foreach ($leads as $lead): ?>
                                <li class="list-group-item">
                                    <strong><?= htmlspecialchars($lead['customer_name']) ?></strong><br>
                                    <?= htmlspecialchars($lead['phone']) ?><br>
                                    Status: <?= htmlspecialchars($lead['status']) ?><br>
                                    <?php if ($lead['follow_up_date']): ?>
                                        Follow-Up: <?= htmlspecialchars($lead['follow_up_date']) ?>
                                    <?php endif; ?>
                                    <br>
                                    <a href="../view_leads/lead_details.php?id=<?= $lead['id'] ?>" class="btn btn-sm btn-outline-primary mt-1">View / Update</a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted">No leads</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
