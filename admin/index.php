<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Quản trị hệ thống</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    </head>

<body>

<div class="container-fluid">
    <div class="row">
        <!-- SIDEBAR -->
        <nav class="col-md-3 col-lg-2 bg-primary text-light min-vh-100 p-3">
            <a href="index.php"><h3 class="text-center mb-4 text-light" >Bảng điều khiển</h3></a>
            <div class="list-group">
                <a href="products.php" class="list-group-item list-group-item-action bg-primary text-light">
                    <ion-icon name="cube-outline" style="font-size:20px;vertical-align:middle;"></ion-icon> Quản lý Sản phẩm</a>
                <a href="orders.php" class="list-group-item list-group-item-action bg-primary text-light">
                    <ion-icon name="clipboard-outline" style="font-size:20px;vertical-align:middle;"></ion-icon> Quản lý Đơn hàng</a>
                <a href="users.php" class="list-group-item list-group-item-action bg-primary text-light">
                    <ion-icon name="accessibility-outline" style="font-size:20px;vertical-align:middle;"></ion-icon> Quản lý Người dùng</a>
                <a href="banner.php" class="list-group-item list-group-item-action bg-primary text-light">
                    <ion-icon name="image-outline" style="font-size:20px;vertical-align:middle;"></ion-icon> Quản lý Banner</a>
                <a href="../user/logout.php" class="list-group-item list-group-item-action text-danger text-center bg-primary">
                    <ion-icon name="exit-outline" style="font-size:20px;vertical-align:middle;"></ion-icon> Đăng xuất</a>
            </div>
        </nav>
        <!-- MAIN CONTENT -->
        <main class="col-md-9 col-lg-10 p-4">
            <?= $page_content ?? "<h1>Hãy chọn chức năng thực hiên</h1>" ?>
        </main>

    </div>
</div>

</body>
</html>
