<?php
session_start();

// Only allow admin access
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Include your database connection file
include '../config/db.php';

// Fetch all agents
$sql = "SELECT id, name, email, status FROM users WHERE role = 'agent'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Agents | Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
  <h2>
  <a href="../admin_dashboard.php" style="text-decoration: none; color: inherit;">
    Manage Agents
  </a>
</h2>

  <a href="add_agent.php" class="btn btn-primary mb-3">Add New Agent</a>

  <table class="table table-bordered table-hover">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while($agent = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($agent['id']) ?></td>
            <td><?= htmlspecialchars($agent['name']) ?></td>
            <td><?= htmlspecialchars($agent['email']) ?></td>
            <td><?= htmlspecialchars(ucfirst($agent['status'])) ?></td>
            <td>
              <a href="edit_agent.php?id=<?= $agent['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
              <?php if ($agent['status'] === 'active'): ?>
                <a href="deactivate_agent.php?id=<?= $agent['id'] ?>" class="btn btn-sm btn-secondary">Deactivate</a>
              <?php else: ?>
                <a href="activate_agent.php?id=<?= $agent['id'] ?>" class="btn btn-sm btn-success">Activate</a>
              <?php endif; ?>
              <a href="delete_agent.php?id=<?= $agent['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this agent?');">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center">No agents found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
