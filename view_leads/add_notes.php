<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header("Location: ../login.php");
    exit;
}

include_once '../config/db.php';

$agent_id = $_SESSION['user_id'];

// Ensure lead_id is provided from POST or GET or session (depending on your logic)
$lead_id = $_GET['lead_id'] ?? $_POST['lead_id'] ?? '';
if (empty($lead_id)) {
    echo "Lead ID is required.";
    exit;
}

// ✅ Add Note Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    $note = trim($_POST['note'] ?? '');

    if (empty($note)) {
        echo "Note cannot be empty.";
    } else {
        // Optional: Check if lead is assigned to this agent
        $check = $conn->prepare("SELECT id FROM leads WHERE id = ? AND id = ?");
        $check->bind_param("ii", $lead_id, $agent_id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            echo "Unauthorized access.";
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO lead_notes (lead_id, agent_id, note, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $lead_id, $agent_id, $note);
        $stmt->execute();
    }
}

// ✅ Fetch Previous Notes
$notesQuery = $conn->prepare("SELECT id, note, created_at FROM lead_notes WHERE lead_id = ? AND agent_id = ? ORDER BY created_at DESC");
$notesQuery->bind_param("ii", $lead_id, $agent_id);
$notesQuery->execute();
$notesResult = $notesQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Notes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">

  <h3>Add Note</h3>
  <form method="POST" action="">
    <input type="hidden" name="lead_id" value="<?= htmlspecialchars($lead_id) ?>">
    <div class="mb-3">
      <textarea name="note" class="form-control" rows="3" placeholder="Write your note here..." required></textarea>
    </div>
    <button type="submit" name="add_note" class="btn btn-primary">Add Note</button>
  </form>

  <hr>

  <h4>Previous Notes</h4>
  <form method="POST" action="delete_notes.php" onsubmit="return confirm('Are you sure you want to delete selected notes?');">
    <input type="hidden" name="lead_id" value="<?= htmlspecialchars($lead_id) ?>">
    <ul class="list-group">
      <?php while ($note = $notesResult->fetch_assoc()): ?>
        <li class="list-group-item d-flex justify-content-between align-items-start">
          <div>
            <small class="text-muted"><?= $note['created_at'] ?></small><br>
            <?= nl2br(htmlspecialchars($note['note'])) ?>
          </div>
          <div>
            <input type="checkbox" name="note_ids[]" value="<?= $note['id'] ?>">
          </div>
        </li>
      <?php endwhile; ?>
    </ul>
    <button type="submit" class="btn btn-danger mt-3">Delete Selected Notes</button>
  </form>

</body>
</html>
