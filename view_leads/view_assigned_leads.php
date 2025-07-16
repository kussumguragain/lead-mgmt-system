<?php
session_start();

// Check if the agent is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header("Location:../login.php");
    exit;
}

include_once '../config/db.php'; // Adjust path if needed

$agent_id = $_SESSION['user_id'];

// Fetch leads assigned to this agent
$sql = "SELECT * FROM leads WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Assigned Leads | Agent</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
  <h2>
  <a href="../agent_dashboard.php" style="text-decoration: none; color: inherit;">
    View Leads
  </a>
</h2>


  <?php if ($result->num_rows > 0): ?>
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Lead Id</th>
        <th>Lead Name</th>
        <th>Contact</th>
        <th>Status</th>
        <th>Follow-up Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php $sn = 1; while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $sn++ ?></td>
        <td><?= htmlspecialchars($row['customer_name']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
               <td><?= htmlspecialchars($row['follow_up_date']) ?></td>
        <td>
<a href="lead_details.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">View</a>


        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  <?php else: ?>
    <div class="alert alert-warning">No leads assigned to you yet.</div>
  <?php endif; ?>
</div>

</body>
</html>
