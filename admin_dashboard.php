<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include_once 'config/db.php';

$agentResult = $conn->query("SELECT COUNT(*) AS total_agents FROM users WHERE role = 'agent'");
$agentRow = $agentResult->fetch_assoc();
$totalAgents = $agentRow['total_agents'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM leads");
$row = mysqli_fetch_assoc($result);
$totalLeads = $row['total'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | Lead Management System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      background-color: #f8f9fa;
    }
    .sidebar {
      min-height: 100vh;
      background-color: #343a40;
    }
    .sidebar a {
      color: #ffffff;
      text-decoration: none;
    }
    .sidebar a:hover {
      background-color: #495057;
    }
    .sidebar .nav-link.active {
      background-color: #007bff;
    }
    .content {
      padding: 20px;
    }
  </style>
</head>
<body>

<!-- Top Navbar -->
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <span class="navbar-brand">Admin Dashboard</span>
    <div class="d-flex align-items-center">
      <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
      <a class="btn btn-outline-light btn-sm" href="logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </div>
</nav>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
      <div class="position-sticky pt-3 bg-dark vh-100">
        <ul class="nav flex-column px-3">
          <li class="nav-item">
            <a class="nav-link active" href="#">
              <i class="bi bi-house-door"></i> Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="manage_agents/manage_agents.php">
              <i class="bi bi-person-lines-fill"></i> Manage Agents
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="manage_leads/view_leads.php">
              <i class="bi bi-list-task"></i> Manage Leads
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="manage_leads/view_leads.php">
              <i class="bi bi-list-task"></i> View Reports
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">
              <i class="bi bi-gear"></i> Settings
            </a>
          </li>
        </ul>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content">
      <h2>Welcome, Admin!</h2>
      <p>This is your dashboard. Here you can manage agents, leads, settings, and view system statistics.</p>

      <!-- Example Cards -->
      <div class="row">
        <div class="col-md-4">
  <div class="card text-white bg-primary mb-3">
    <div class="card-body">
      <h5 class="card-title">Total Agents</h5>
      <p class="card-text fs-4"><?php echo $totalAgents; ?></p>
    </div>
  </div>
</div>

        <div class="col-md-4">
          <div class="card text-white bg-success mb-3">
            <div class="card-body">
              <h5 class="card-title">Total Leads</h5>
              <p class="card-text fs-4"><?php echo $totalLeads; ?></p>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
