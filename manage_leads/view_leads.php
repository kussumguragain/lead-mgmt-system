<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../config/db.php';

// Fetch all leads with address and agent name
$sql = "SELECT leads.id, leads.customer_name, leads.phone, leads.address, leads.status, leads.created_at,
               users.name AS agent_name
        FROM leads
        LEFT JOIN users ON leads.assigned_to = users.id";

$result = $conn->query($sql);

// Check for query errors
if (!$result) {
    die("Query Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Leads | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/lead-management-system/css/style.css">
</head>
<body>
<?php include '../common/topbar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <?php include '../common/sidebar.php'; ?>
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>
        <a href="../admin_dashboard.php" style="text-decoration: none; color: inherit;">
        All Leads
        </a>
       </h2>
        <a href="add_leads.php" class="btn btn-primary">Add Lead</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Status</th>
                <th>Assigned Agent</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= !empty($row['agent_name']) ? htmlspecialchars($row['agent_name']) : 'Unassigned' ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td>
                        <a href="edit_leads.php?id=<?= urlencode($row['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="delete_leads.php?id=<?= urlencode($row['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this lead?');">Delete</a>
                        <a href="assign_to_agent.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Assign</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="text-center">No leads found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
    </main>
  </div>
</div>
</body>
</html>
