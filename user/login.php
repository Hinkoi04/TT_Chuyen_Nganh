<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = "";

/* XU LY DANG NHAP */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, fullname, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role']     = $user['role'];

            // Đồng bộ giỏ hàng từ DB ↔ Session
            if (function_exists('dong_bo_gio_hang')) {
                dong_bo_gio_hang($user['id']);
            }

            // Điều hướng user / admin
            if ($user['role'] === 'admin') {
                header("Location: /TT_Chuyen_Nganh/admin/index.php");
            } else {
                header("Location: /TT_Chuyen_Nganh/user/index.php");
            }
            exit();
        }
    }

    $error = "Tên đăng nhập hoặc mật khẩu không đúng.";
    $stmt->close();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container col-md-5 col-sm-8 col-xs-10 border p-4 bg-white rounded shadow-sm">

    <h2 class="text-center mb-4">Đăng nhập</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center"><?= $error ?></div>
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
            <a href="/TT_Chuyen_Nganh/user/register.php">Đăng ký ngay</a>
        </p>

    </form>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
