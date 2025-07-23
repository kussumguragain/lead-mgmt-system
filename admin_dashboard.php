<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include_once 'config/db.php';

$leadData = [];
$query = "SELECT DATE(created_at) AS date, COUNT(*) AS total FROM leads GROUP BY DATE(created_at) ORDER BY DATE(created_at)";
$leadResult = $conn->query($query);

$chartDates = [];
$chartTotals = [];

while ($row = $leadResult->fetch_assoc()) {
    $chartDates[] = $row['date'];
    $chartTotals[] = $row['total'];
}


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
<?php 
include 'common/topbar.php';
?>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
     <?php include 'common/sidebar.php';?>

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

      <div class="row">
     <div class="col-12 mt-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Leads Created Over Time</h5>
      </div>
      <div class="card-body">
        <canvas id="leadsLineChart" height="80"></canvas>
      </div>
    </div>
  </div>
</div>
    </main>
  </div>
</div>



<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const chartDates = <?= json_encode($chartDates) ?>;
  const chartTotals = <?= json_encode($chartTotals) ?>;
</script>
<script src="js/script.js"></script>

</body>
</html>
