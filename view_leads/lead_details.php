<?php
// lead_details.php
session_start();
include_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header("Location:../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Lead ID is required.");
}

$lead_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// ✅ Handle Add Note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    $note = trim($_POST['note'] ?? '');
    if (!empty($note)) {
        $stmt = $conn->prepare("INSERT INTO lead_notes (lead_id, agent_id, note, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $lead_id, $user_id, $note);
        $stmt->execute();
    }
}

// ✅ Handle Delete Notes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notes']) && !empty($_POST['note_ids'])) {
    $note_ids = $_POST['note_ids'];
    $placeholders = implode(',', array_fill(0, count($note_ids), '?'));
    $types = str_repeat('i', count($note_ids));
    $params = array_merge($note_ids);

    $stmt = $conn->prepare("DELETE FROM lead_notes WHERE id IN ($placeholders) AND agent_id = ?");
    $types .= 'i';
    $params[] = $user_id;
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
}

// ✅ Fetch Lead Details
$stmt = $conn->prepare("SELECT * FROM leads WHERE id = ? AND assigned_to = ?");
$stmt->bind_param("ii", $lead_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Lead not found or not assigned to you.");
}

$lead = $result->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lead Details</title>


  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="container">
    <h2>
  <a href="view_assigned_leads.php" style="text-decoration: none; color: inherit;">
    Leads Details
  </a>
</h2>

    <p><strong>Name:</strong> <?= htmlspecialchars($lead['customer_name']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($lead['phone']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($lead['status']) ?></p>
  <?php if (!empty($lead['follow_up_date'])): ?>
  <p><strong>Follow-Up Date:</strong> <?= htmlspecialchars($lead['follow_up_date']) ?></p>
<?php endif; ?>


    <hr>

    <!-- 🔽 Place the update status form here -->
    <form action="update_lead_status.php" method="POST" class="mt-4">
      <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">

      <div class="mb-3">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select" required>
          <option value="">-- Select Status --</option>
          <option value="New" <?= $lead['status'] === 'New' ? 'selected' : '' ?>>New</option>
          <option value="Contacted" <?= $lead['status'] === 'Contacted' ? 'selected' : '' ?>>Contacted</option>
          <option value="-Converted" <?= $lead['status'] === 'Converted' ? 'selected' : '' ?>>Converted</option>
          <option value="Lost" <?= $lead['status'] === 'Lost' ? 'selected' : '' ?>>Lost</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="follow_up_date" class="form-label">Follow-Up Date</label>
        <input type="date" name="follow_up_date" id="follow_up_date" class="form-control" value="<?= $lead['follow_up_date'] ?>">
      </div>

      <button type="submit" class="btn btn-primary">Update Lead</button>
    </form>
    <!-- 🔽 Add Note Form -->
<form method="POST" class="mt-4">
  <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">

  <div class="mb-3">
    <label for="note" class="form-label">Add Note</label>
    <textarea name="note" id="note" class="form-control" rows="4" required></textarea>
  </div>

<button type="submit" name="add_note" class="btn btn-primary">Add Note</button>


</form>

<!-- 🔽 View Previous Notes -->
<?php
$notesQuery = $conn->prepare("SELECT id, note, created_at FROM lead_notes WHERE lead_id = ? ORDER BY created_at DESC");
$notesQuery->bind_param("i", $lead['id']);
$notesQuery->execute();
$notesResult = $notesQuery->get_result();
?>

<h5 class="mt-5">Previous Notes</h5>

<form method="POST">
  <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
  <ul class="list-group">
    <?php while ($note = $notesResult->fetch_assoc()): ?>
      <li class="list-group-item">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="note_ids[]" value="<?= $note['id'] ?>" id="note<?= $note['id'] ?>">
          <label class="form-check-label" for="note<?= $note['id'] ?>">
            <small class="text-muted"><?= $note['created_at'] ?></small><br>
            <?= nl2br(htmlspecialchars($note['note'])) ?>
          </label>
        </div>
      </li>
    <?php endwhile; ?>
  </ul>
<button type="submit" name="delete_notes" class="btn btn-danger mt-2">Delete Selected Notes</button>
</form>




  </div>
</body>
</html>
