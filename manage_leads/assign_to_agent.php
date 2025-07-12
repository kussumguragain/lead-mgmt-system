<?php
session_start();

// Only allow access if logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../config/config.php';

// Get the lead ID from query parameter
$lead_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch available agents
$agents = [];
$agentQuery = "SELECT id, name FROM users WHERE role = 'agent'";
$result = $conn->query($agentQuery);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $agents[] = $row;
    }
}

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agent_id'])) {
    $agent_id = intval($_POST['agent_id']);

    $stmt = $conn->prepare("UPDATE leads SET assigned_to = ?, status = 'Assigned' WHERE id = ?");
    $stmt->bind_param("ii", $agent_id, $lead_id);

    if ($stmt->execute()) {
        header("Location: view_leads.php?msg=assigned");
        exit;
    } else {
        echo "Failed to assign lead.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Lead to Agent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h4>Assign Lead to Agent</h4>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Select Agent</label>
                    <select name="agent_id" class="form-select" required>
                        <option value="">-- Choose Agent --</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?= $agent['id']; ?>"><?= htmlspecialchars($agent['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Assign</button>
                <a href="view_leads.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
