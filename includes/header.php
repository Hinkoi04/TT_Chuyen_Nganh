<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/* Tự động xác định thư mục gốc (base path) */
$basePath = "" . basename(dirname(__DIR__));

/* Lấy danh mục cho menu */
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laptop Store</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <style>
        .bg-pri { background: #3399FF; }
        .search-input::placeholder { color: #0066ff !important; }
        .search-group { position: relative; }
        li > a { background: rgba(255, 255, 255, .1); }
        .search-btn {
            position: absolute; right: 8px; top: 50%;
            transform: translateY(-50%);
            width: 34px; height: 34px;
            display: flex; justify-content: center; align-items: center;
        }
        .search-input { padding-right: 50px !important; }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg py-3 shadow-lg sticky-top bg-pri">
    <div class="container">

        <!-- LOGO -->
        <a class="navbar-brand font-weight-bold" href="<?= $basePath ?>/user/index.php">
            <img src="<?= $basePath ?>/uploads/logo.png" style="width:100px;height:50px">
        </a>

        <!-- NÚT MOBILE -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mainNavbar">
            <ion-icon name="reorder-three" style="font-size:50px;"></ion-icon>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">

            <!-- THANH TÌM KIẾM -->
            <form class="form-inline mx-auto" action="<?= $basePath ?>/user/search.php" method="get">

                <select class="form-control mr-2 rounded-pill text-primary bg-light"
                        name="cate" style="width:auto;">
                    <option value="">☰ Danh mục</option>

                    <?php while($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <div class="search-group d-flex align-items-center mr-2">
                    <input class="form-control rounded-pill search-input text-primary"
                            type="text" name="q" placeholder="Nhập tên sản phẩm..." required>

                    <button class="btn btn-outline-primary rounded-circle search-btn" type="submit">
                        <ion-icon name="search-outline" style="font-size:18px;"></ion-icon>
                    </button>
                </div>
            </form>

            <!-- MENU PHẢI -->
            <ul class="navbar-nav">

                <!-- GIỎ HÀNG -->
                <li class="nav-item">
                    <a class="nav-link text-light rounded-pill mx-2" href="<?= $basePath ?>/user/cart.php">
                        <ion-icon name="cart-outline" style="font-size:20px;vertical-align:middle;"></ion-icon>
                        Giỏ hàng
                    </a>
                </li>

                <?php if (!empty($_SESSION['user_id'])): ?>

                    <!-- XIN CHÀO USER -->
                    <li class="nav-item">
                        <a class="nav-link text-light rounded-pill bg-primary mx-1">
                            Xin chào, <?= htmlspecialchars($_SESSION['fullname']) ?>
                        </a>
                    </li>

                    <!-- ADMIN -->
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?= $basePath ?>/admin/index.php">Trang Admin</a>
                        </li>
                    <?php endif; ?>

                    <!-- ĐĂNG XUẤT -->
                    <li class="nav-item">
                        <a class="nav-link text-danger rounded-pill bg-primary" href="<?= $basePath ?>/user/logout.php">
                            Đăng xuất
                            <ion-icon name="exit-outline" style="font-size:20px;vertical-align:middle;"></ion-icon>
                        </a>
                    </li>

                <?php else: ?>

                    <!-- ĐĂNG NHẬP -->
                    <li class="nav-item">
                        <a class="nav-link text-light rounded-pill" href="<?= $basePath ?>/user/login.php">
                            <ion-icon name="person-circle-outline" style="font-size:20px;vertical-align:middle;"></ion-icon>
                            Đăng nhập
                        </a>
                    </li>

                <?php endif; ?>

            </ul>

        </div>
    </div>
</nav>

<script>
document.querySelector('select[name="cate"]').addEventListener('change', function(){
    this.form.submit();
});
</script>

<div class="container mt-4">
