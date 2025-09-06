<?php
session_start();
include_once '../config/db.php';

//  Allow only agents
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header("Location: ../login.php");
    exit;
}

$agent_id = $_SESSION['user_id'];

// Delete lead (only if assigned to this agent)
if (isset($_GET['delete'])) {
    $lead_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM leads WHERE id = ? AND assigned_to = ?");
    $stmt->bind_param("ii", $lead_id, $agent_id);
    $stmt->execute();
    header("Location: manage_leads.php?deleted=1");
    exit;
}

// Fetch all leads assigned to this agent
$stmt = $conn->prepare("SELECT id, customer_name, phone, status, lead_stage, follow_up_date 
                        FROM leads WHERE assigned_to = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Leads</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
  <h2>     
     <a href="../agent_dashboard.php" style="text-decoration: none; color: inherit;">Manage Leads</a>
    </h2>
  <hr>

  <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Lead deleted successfully.</div>
  <?php endif; ?>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Phone</th>
        <th>Status</th>
        <th>Stage</th>
        <th>Follow-Up Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($lead = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $lead['id'] ?></td>
          <td><?= htmlspecialchars($lead['customer_name']) ?></td>
          <td><?= htmlspecialchars($lead['phone']) ?></td>
          <td><?= htmlspecialchars($lead['status']) ?></td>
          <td><?= htmlspecialchars($lead['lead_stage']) ?></td>
          <td><?= $lead['follow_up_date'] ?: '-' ?></td>
          <td>
            <a href="../view_leads/lead_details.php?id=<?= $lead['id'] ?>" class="btn btn-sm btn-primary">View / Update</a>
            <a href="manage_leads.php?delete=<?= $lead['id'] ?>" class="btn btn-sm btn-danger"
               onclick="return confirm('Are you sure you want to delete this lead?');">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
