<?php
session_start();

require_once "../includes/db.php";
require_once "../includes/functions.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $address  = trim($_POST['address']);

    if ($fullname === '' || $username === '' || $email === '' || $password === '') {
        $errors[] = "Vui lòng điền đầy đủ thông tin.";
    }

    if (empty($errors)) {

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $errors[] = "Tên đăng nhập hoặc email đã tồn tại.";
        } else {

            $stmt = $conn->prepare("
                INSERT INTO users (fullname, username, email, password, address)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssss", $fullname, $username, $email, $hashed, $address);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $errors[] = "Đăng ký thất bại, vui lòng thử lại.";
            }

            $stmt->close();
        }
        $check->close();
    }
}

require_once "../includes/header.php";
?>

<div class="container col-md-5 col-sm-8 col-xs-10 border p-4 mt-4 bg-white rounded shadow-sm">

    <h2 class="text-center mb-3">Đăng ký tài khoản</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger text-center">
            <?= htmlspecialchars($errors[0]) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label>Họ và Tên</label>
            <input type="text" name="fullname" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Tên đăng nhập</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Địa chỉ</label>
            <textarea name="address" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block mb-3">
            Đăng ký
        </button>

        <p class="text-center">
            Đã có tài khoản?
            <a href="login.php">Đăng nhập</a>
        </p>

    </form>

</div>

<?php require_once "../includes/footer.php"; ?>
