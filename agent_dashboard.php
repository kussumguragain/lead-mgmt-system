<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header("Location: login.php");
    exit;
}
?>

<h2>Welcome Agent: <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
<p>This is the agent dashboard.</p>
<a href="logout.php">Logout</a>
