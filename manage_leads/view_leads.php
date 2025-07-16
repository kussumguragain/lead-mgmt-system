<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../config/db.php';

// Handle search and filter
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT * FROM leads WHERE 1";
$params = [];
$types = "";

// Search condition
if ($searchTerm !== '') {
    $sql .= " AND (customer_name LIKE ? OR phone LIKE ? OR address LIKE ?)";
    $wildcard = "%" . $searchTerm . "%";
    $params[] = $wildcard;
    $params[] = $wildcard;
    $params[] = $wildcard;
    $types .= "sss";
}

// Status filter
if ($statusFilter !== '') {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Leads</title>
  <link rel="stylesheet" href="/lead-management-system/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../common/topbar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <?php include '../common/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content">
      <h2 class="mt-4 mb-3"><a href="/lead-management-system/manage_leads/view_leads.php" style="text-decoration: none; color: inherit;">View Leads</a></h2>
      <a href="add_leads.php" class="btn btn-success mb-4">Add Lead</a>

      <!-- Search and Filter Form -->
      <form class="row g-3 mb-4" method="get">
        <div class="col-md-4">
          <input type="text" class="form-control" name="search" placeholder="Search name, phone, or address" value="<?= htmlspecialchars($searchTerm) ?>">
        </div>
        <div class="col-md-3">
          <select name="status" class="form-select">
            <option value="">All Statuses</option>
            <option value="Hot" <?= $statusFilter == 'Hot' ? 'selected' : '' ?>>Hot</option>
            <option value="Warm" <?= $statusFilter == 'Warm' ? 'selected' : '' ?>>Warm</option>
            <option value="Cold" <?= $statusFilter == 'Cold' ? 'selected' : '' ?>>Cold</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
        <div class="col-md-2">
          <a href="view_leads.php" class="btn btn-secondary w-100">Reset</a>
        </div>
      </form>

      <!-- Leads Table -->
      <table class="table table-bordered table-hover">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Customer Name</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Status</th>
            <th>Assigned To</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($lead = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $lead['id'] ?></td>
                <td><?= htmlspecialchars($lead['customer_name']) ?></td>
                <td><?= htmlspecialchars($lead['phone']) ?></td>
                <td><?= htmlspecialchars($lead['address']) ?></td>
                <td><?= htmlspecialchars($lead['status']) ?></td>
                <td><?= $lead['assigned_to'] ?: 'Unassigned' ?></td>
                <td><?= date('Y-m-d H:i', strtotime($lead['created_at'])) ?></td>
                <td>
                    <a href="edit_leads.php?id=<?= urlencode($row['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="delete_leads.php?id=<?= urlencode($row['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this lead?');">Delete</a>
                    <a href="assign_to_agent.php?id=<?= $lead['id'] ?>" class="btn btn-sm btn-primary">Assign</a>
                  <!-- You can add Edit/Delete buttons if needed -->
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center">No leads found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </main>
  </div>
</div>

</body>
</html>
