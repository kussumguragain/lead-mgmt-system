<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <span class="navbar-brand">Admin Dashboard</span>
    <div class="d-flex align-items-center">
      <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
      <a class="btn btn-outline-light btn-sm" href="/lead-management-system/logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </div>
</nav>