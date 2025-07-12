<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../config/db.php';

if (!isset($_GET['id'])) {
    die("Lead ID is missing.");
}

$lead_id = $_GET['id'];

// Fetch lead and agents
$lead_stmt = $conn->prepare("SELECT * FROM leads WHERE id = ?");
$lead_stmt->bind_param("i", $lead_id);
$lead_stmt->execute();
$lead_result = $lead_stmt->get_result();
$lead = $lead_result->fetch_assoc();

if (!$lead) {
    die("Lead not found.");
}

$agentsResult = $conn->query("SELECT id, name FROM users WHERE role = 'agent'");

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = $_POST['customer_name'];
    $phone       = $_POST['phone'];
    $address     = $_POST['address'];
    $status      = $_POST['status'];
    $assigned_to = $_POST['assigned_to'];

    $update_stmt = $conn->prepare("UPDATE leads SET customer_name=?, phone=?, address=?, status=?, assigned_to=? WHERE id=?");
    $update_stmt->bind_param("ssssii", $name, $phone, $address, $status, $assigned_to, $lead_id);

    if ($update_stmt->execute()) {
        header("Location: view_leads.php");
        exit;
    } else {
        $error = "Failed to update lead.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Lead</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Edit Lead</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="customer_name" class="form-label">Customer Name</label>
            <input type="text" class="form-control" name="customer_name" value="<?= htmlspecialchars($lead['customer_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($lead['phone']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($lead['address']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" name="status" required>
                <option value="Hot" <?= $lead['status'] === 'Hot' ? 'selected' : '' ?>>Hot</option>
                <option value="Warm" <?= $lead['status'] === 'Warm' ? 'selected' : '' ?>>Warm</option>
                <option value="Cold" <?= $lead['status'] === 'Cold' ? 'selected' : '' ?>>Cold</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="assigned_to" class="form-label">Assign to Agent</label>
            <select class="form-select" name="assigned_to" required>
                <?php while ($agent = $agentsResult->fetch_assoc()): ?>
                    <option value="<?= $agent['id'] ?>" <?= $agent['id'] == $lead['assigned_to'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($agent['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Update Lead</button>
        <a href="view_leads.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
