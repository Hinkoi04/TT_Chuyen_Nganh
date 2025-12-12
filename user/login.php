<?php
session_start();

require_once "../includes/db.php";
require_once "../includes/functions.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("
        SELECT id, fullname, password, role 
        FROM users 
        WHERE username = ?
        LIMIT 1
    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role']     = $user['role'];

        if (function_exists('dong_bo_gio_hang')) {
            dong_bo_gio_hang($user['id']);
        }

        if ($user['role'] === 'admin') {
            header("Location: ../admin/index.php");
        } else {
            header("Location: index.php");
        }
        exit;
    }

    $error = "Tên đăng nhập hoặc mật khẩu không đúng.";
}

require_once "../includes/header.php";
?>

<div class="container col-md-5 col-sm-8 col-xs-10 border p-4 bg-white rounded shadow-sm">

    <h2 class="text-center mb-4">Đăng nhập</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label>Tên đăng nhập</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block rounded-pill">
            Đăng nhập
        </button>

        <p class="text-center mt-3">
            Bạn chưa có tài khoản?
            <a href="register.php">Đăng ký ngay</a>
        </p>
    </form>
</div>

<?php require_once "../includes/footer.php"; ?>
