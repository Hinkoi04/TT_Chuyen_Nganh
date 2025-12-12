<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';


$limit = 4;
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $limit;

$cate = isset($_GET['cate']) ? (int)$_GET['cate'] : 0;

$total_products = lay_tong_san_pham($cate);
$total_pages = ceil($total_products / $limit);

$result   = lay_san_pham_phan_trang($cate, $start, $limit);
$products = $result->fetchAll();

$banners = lay_banner()->fetchAll();

$latest_products = lay_san_pham_moi_nhat(4)->fetchAll();
?>

<?php if (!empty($banners)): ?>
<div id="bannerSlider"
     class="carousel slide mb-4 shadow-lg"
     data-ride="carousel"
     data-interval="2000"
     style="width:100vw; margin-left:calc(-50vw + 50%);">

    <div class="carousel-inner">
        <?php foreach ($banners as $i => $b): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
            <img src="../uploads/banner/<?= htmlspecialchars($b['image']) ?>"
                 style="width:100%; height:450px; object-fit:cover;">
        </div>
        <?php endforeach; ?>
    </div>

    <a class="carousel-control-prev" href="#bannerSlider" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </a>
    <a class="carousel-control-next" href="#bannerSlider" role="button" data-slide="next">
        <span class="carousel-control-next-icon"></span>
    </a>
</div>
<?php endif; ?>


<style>
.khung-san-pham-moi {
    background: linear-gradient(135deg,
        rgba(134,185,229,0.55),
        rgba(0,68,255,0.55)
    );
    border-radius: 25px;
    padding: 25px 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,.12);
    margin-bottom: 40px;
    backdrop-filter: blur(6px);
}
#product-list {
    scroll-margin-top: 120px;
}

</style>

<div class="container mt-4 khung-san-pham-moi">
    <h3 class="text-center mb-4 text-light">🔥 SẢN PHẨM MỚI NHẤT 🔥</h3>
    <div class="row">
        <?php foreach ($latest_products as $sp): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-lg border-0" style="border-radius:20px">
                <div class="p-2 d-flex justify-content-center">
                    <img src="../uploads/<?= htmlspecialchars($sp['image']) ?>"
                         style="width:90%;height:200px;object-fit:cover;border-radius:15px"
                         class="shadow-sm">
                </div>
                <div class="card-body text-center">
                    <h6><?= htmlspecialchars($sp['name']) ?></h6>
                    <p class="text-muted small"><?= htmlspecialchars($sp['category_name']) ?></p>
                    <p class="text-danger font-weight-bold"><?= dinh_dang_gia($sp['price']) ?></p>

                    <a href="../user/product_details.php?id=<?= $sp['id'] ?>"
                       class="btn btn-sm btn-primary rounded-pill px-3">Xem chi tiết</a>

                    <a href="../user/cart_handler.php?action=add&id=<?= $sp['id'] ?>&quantity=1"
                       class="btn btn-sm btn-outline-primary ml-2">
                        +
                        <ion-icon name="cart-outline"></ion-icon>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="container mt-4 bg-light shadow-lg" style="border-radius:15px">
    <h3 class="mb-4 text-center" id="product-list">
        DANH SÁCH SẢN PHẨM <?= $cate ? '(Theo danh mục)' : '' ?>
    </h3>

    <div class="row">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $sp): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-lg border-0" style="border-radius:20px">
                    <div class="p-2 d-flex justify-content-center">
                        <img src="../uploads/<?= htmlspecialchars($sp['image']) ?>"
                             style="width:90%;height:200px;object-fit:cover;border-radius:15px"
                             class="shadow-sm">
                    </div>
                    <div class="card-body text-center">
                        <h6><?= htmlspecialchars($sp['name']) ?></h6>
                        <p class="text-muted"><?= htmlspecialchars($sp['category_name']) ?></p>
                        <p class="text-danger font-weight-bold"><?= dinh_dang_gia($sp['price']) ?></p>

                        <a href="../user/product_details.php?id=<?= $sp['id'] ?>"
                           class="btn btn-sm btn-primary rounded-pill px-3">Xem chi tiết</a>

                        <a href="../user/cart_handler.php?action=add&id=<?= $sp['id'] ?>&quantity=1"
                           class="btn btn-sm btn-outline-primary ml-2">
                            +
                            <ion-icon name="cart-outline"></ion-icon>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted">Không có sản phẩm nào.</div>
        <?php endif; ?>
    </div>
</div>

<div class="container text-center mt-3 mb-4">
<?php if ($total_pages > 1): ?>
<?php list($startPage, $endPage) = phan_trang_tinh_trang($page, $total_pages); ?>

<?php if ($page > 1): ?>
<a class="btn btn-outline-primary btn-sm mx-1"
   href="../user/index.php?page=<?= $page - 1 ?>&cate=<?= $cate ?>#product-list"><</a>
<?php endif; ?>

<?php for ($i = $startPage; $i <= $endPage; $i++): ?>
<a class="btn btn-sm mx-1 <?= $i == $page ? 'btn-primary' : 'btn-outline-primary' ?>"
   href="../user/index.php?page=<?= $i ?>&cate=<?= $cate ?>#product-list">
   <?= $i ?>
</a>
<?php endfor; ?>

<?php if ($page < $total_pages): ?>
<a class="btn btn-outline-primary btn-sm mx-1"
   href="../user/index.php?page=<?= $page + 1 ?>&cate=<?= $cate ?>#product-list">></a>
<?php endif; ?>
<?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
