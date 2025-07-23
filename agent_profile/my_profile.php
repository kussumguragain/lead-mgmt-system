<?php
session_start();
include_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current agent data
$stmt = $conn->prepare("SELECT name, email,  profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_name = $_POST['name'] ?? '';
    
    $profile_pic_path = $user['profile_pic'];
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $upload_dir = '../uploads/';
        $filename = time() . '_' . basename($_FILES['profile_pic']['name']);
        $target_file = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
            $profile_pic_path = $target_file;
        }
    }

    $stmt = $conn->prepare("UPDATE users SET name = ?,  profile_pic = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_name,  $profile_pic_path, $user_id);
    if ($stmt->execute()) {
        $success = "Profile updated successfully.";
        $user['name'] = $new_name;
        $user['profile_pic'] = $profile_pic_path;
    } else {
        $error = "Failed to update profile.";
    }
}

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Please fill all password fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();

        if (!password_verify($current_password, $row['password'])) {
            $error = "Current password is incorrect.";
        } else {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_password_hash, $user_id);
            if ($stmt->execute()) {
                $success = "Password changed successfully.";
            } else {
                $error = "Failed to update password. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="container">
     <h2>
      <a href="../agent_dashboard.php" style="text-decoration: none; color: inherit;">My Profile</a>
    </h2>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Profile Picture -->
    <?php if (!empty($user['profile_pic'])): ?>
      <img src="<?= $user['profile_pic'] ?>" alt="Profile Picture" class="img-thumbnail mb-3" width="150">
    <?php endif; ?>

    <!-- Update Profile Form -->
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-control" required />
      </div>


      <div class="mb-3">
        <label class="form-label">Profile Picture</label>
        <input type="file" name="profile_pic" class="form-control" />
      </div>
       <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="text" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required />
      </div>

      <button type="submit" name="update_profile" class="btn btn-success">Update Profile</button>
    </form>

    <hr>

    <h4>Change Password</h4>

    <form method="POST" class="mb-4">
      <div class="mb-3 position-relative">
  <label for="current_password" class="form-label">Current Password</label>
  <input
    type="password"
    name="current_password"
    id="current_password"
    class="form-control"
    required
  />
  <i
    class="bi bi-eye-slash toggle-password"
    style="position: absolute; top: 38px; right: 10px; cursor: pointer;"
    onclick="togglePassword('current_password', this)"
  ></i>
</div>

<div class="mb-3 position-relative">
  <label for="new_password" class="form-label">New Password</label>
  <input
    type="password"
    name="new_password"
    id="new_password"
    class="form-control"
    required
  />
  <i
    class="bi bi-eye-slash toggle-password"
    style="position: absolute; top: 38px; right: 10px; cursor: pointer;"
    onclick="togglePassword('new_password', this)"
  ></i>
</div>

<div class="mb-3 position-relative">
  <label for="confirm_password" class="form-label">Confirm New Password</label>
  <input
    type="password"
    name="confirm_password"
    id="confirm_password"
    class="form-control"
    required
  />
  <i
    class="bi bi-eye-slash toggle-password"
    style="position: absolute; top: 38px; right: 10px; cursor: pointer;"
    onclick="togglePassword('confirm_password', this)"
  ></i>
</div>

      <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
    </form>
  </div>
  
<script>
  function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
      input.type = "text";
      icon.classList.remove("bi-eye-slash");
      icon.classList.add("bi-eye");
    } else {
      input.type = "password";
      icon.classList.remove("bi-eye");
      icon.classList.add("bi-eye-slash");
    }
  }
</script>

</body>
</html>
