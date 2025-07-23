<?php
session_start();

// Only admin can access
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

include '../config/db.php';

$msg = '';
$id = $_GET['id'] ?? null;

// Redirect if no id
if (!$id) {
    header('Location: manage_agents.php');
    exit;
}

// Fetch existing agent data
$stmt = $conn->prepare("SELECT id, name, email, status FROM users WHERE id = ? AND role = 'agent'");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$agent = $result->fetch_assoc();

if (!$agent) {
    // Agent not found
    header('Location: manage_agents.php?msg=Agent not found');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $status = $_POST['status'] === 'active' ? 'active' : 'inactive';

    // Validate inputs
    if (empty($name) || empty($email)) {
        $msg = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email format.";
    } else {
        // Check if email already exists for another agent
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param('si', $email, $id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $msg = "Email already registered with another agent.";
        } else {
            // Update agent
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, status = ? WHERE id = ?");
            $stmt->bind_param('sssi', $name, $email, $status, $id);
            if ($stmt->execute()) {
                header("Location: manage_agents.php?msg=Agent updated successfully");
                exit;
            } else {
                $msg = "Error updating agent: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit Agent</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4" style="max-width: 600px;">
  <h2>Edit Agent</h2>

  <?php if ($msg): ?>
    <div class="alert alert-warning"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <form method="post" action="">
    <div class="mb-3">
      <label for="name" class="form-label">Agent Name</label>
      <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($agent['name']) ?>">
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Agent Email</label>
      <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($agent['email']) ?>">
    </div>
    <div class="mb-3">
      <label for="status" class="form-label">Status</label>
      <select class="form-select" id="status" name="status">
        <option value="active" <?= $agent['status'] === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="inactive" <?= $agent['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Update Agent</button>
    <a href="manage_agents.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
