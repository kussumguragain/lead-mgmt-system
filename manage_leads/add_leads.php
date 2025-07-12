<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../config/db.php';

// Fetch agents
$agentsResult = $conn->query("SELECT id, name FROM users WHERE role = 'agent'");

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = $_POST['customer_name'];
    $phone       = $_POST['phone'];
    $address     = $_POST['address'];
    $status      = $_POST['status'];
    $assigned_to = $_POST['assigned_to'];

    $stmt = $conn->prepare("INSERT INTO leads (customer_name, phone, address, status, assigned_to, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssi", $name, $phone, $address, $status, $assigned_to);

    if ($stmt->execute()) {
        header("Location: view_leads.php");
        exit;
    } else {
        $error = "Failed to add lead. " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Lead</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Add New Lead</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label for="customer_name" class="form-label">Customer Name</label>
            <input type="text" class="form-control" name="customer_name" id="customer_name" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" id="phone" required>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" name="address" id="address" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" name="status" id="status" required>
                <option value="">Select Status</option>
                <option value="Hot">Hot</option>
                <option value="Warm">Warm</option>
                <option value="Cold">Cold</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="assigned_to" class="form-label">Assign to Agent</label>
            <select class="form-select" name="assigned_to" id="assigned_to" required>
                <option value="">Select Agent</option>
                <?php while ($agent = $agentsResult->fetch_assoc()): ?>
                    <option value="<?= $agent['id'] ?>"><?= htmlspecialchars($agent['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Add Lead</button>
        <a href="view_leads.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
