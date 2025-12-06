<?php
require_once 'auth_check.php';
require_once '../includes/db.php';

if (!isset($_GET['id'])) { header("Location: users.php"); exit(); }
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT fullname, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) { echo "Người dùng không tồn tại."; exit(); }
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullname = $_POST['fullname'];
  $email = $_POST['email'];
  $role = $_POST['role'];
  $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, role=? WHERE id=?");
  $stmt->bind_param("sssi", $fullname, $email, $role, $id);
  $stmt->execute();
  header("Location: users.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Sửa người dùng</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h2>Sửa thông tin người dùng</h2>
  <form method="POST">
    <div class="form-group">
      <label>Họ tên</label>
      <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" class="form-control" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
    </div>
    <div class="form-group">
      <label>Vai trò</label>
      <select name="role" class="form-control" required>
        <option value="user" <?= $user['role']=='user'?'selected':'' ?>>user</option>
        <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>admin</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Lưu</button>
    <a href="users.php" class="btn btn-secondary">Hủy</a>
  </form>
</div>
</body>
</html>
