<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

/* Base path cố định */
$basePath = BASE_URL;

/* PHÂN TRANG */
$limit = 4;
$page  = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$start = ($page - 1) * $limit;

/* LỌC DANH MỤC */
$cate = isset($_GET['cate']) ? intval($_GET['cate']) : 0;

/* TỔNG SẢN PHẨM */
$total_products = lay_tong_san_pham($cate);
$total_pages = ceil($total_products / $limit);

/* LẤY SẢN PHẨM */
$result = lay_san_pham_phan_trang($cate, $start, $limit);

/* LẤY BANNER */
$banner_query = lay_banner();

/* 🔥 LẤY TOP 4 SẢN PHẨM MỚI NHẤT */
$latest = lay_san_pham_moi_nhat(4);
?>

<!-- BANNER -->
<?php if ($banner_query->num_rows > 0): ?>
<div id="bannerSlider" class="carousel slide mb-4 shadow-lg" data-ride="carousel" data-interval="2000"
     style="border-radius: 20px; overflow: hidden;">
    <div class="carousel-inner">
        <?php $active = 'active'; while($b = $banner_query->fetch_assoc()): ?>
        <div class="carousel-item <?= $active ?>">
            <img src="<?= $basePath ?>/uploads/banner/<?= htmlspecialchars($b['image']) ?>" 
                 class="d-block w-100"
                 style="height: 300px; object-fit: cover;">
        </div>
        <?php $active = ''; endwhile; ?>
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
        rgba(134, 185, 229, 0.55),   /* hồng nhạt */
        rgba(0, 68, 255, 0.55)    /* xanh dương nhạt */
    );
    border-radius: 25px;
    padding: 25px 20px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
    margin-bottom: 40px;
    border: 1px solid rgba(255,255,255,0.5);
    backdrop-filter: blur(6px);
}
</style>
<!-- 🔥 TOP 4 SẢN PHẨM MỚI NHẤT -->
<div class="container mt-4 khung-san-pham-moi" >
    <h3 class="text-center mb-4 text-light">🔥SẢN PHẨM MỚI NHẤT🔥</h3>

    <div class="row">

        <?php while($sp = $latest->fetch_assoc()): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
                
                <div class="p-2 d-flex justify-content-center">
                    <img src="<?= $basePath ?>/uploads/<?= htmlspecialchars($sp['image']) ?>" 
                         style="width: 90%; height: 200px; object-fit: cover; border-radius: 15px;"
                         class="shadow-sm">
                </div>

                <div class="card-body text-center">
                    <h6 class="mb-2"><?= htmlspecialchars($sp['name']) ?></h6>
                    <p class="text-muted small"><?= htmlspecialchars($sp['category_name']) ?></p>
                    <p class="text-danger font-weight-bold"><?= dinh_dang_gia($sp['price']) ?></p>

                    <a href="<?= $basePath ?>/user/product_details.php?id=<?= $sp['id'] ?>"
                       class="btn btn-sm btn-primary rounded-pill px-3">Xem chi tiết</a>
                                                   <a href="<?= $basePath ?>/user/cart_handler.php?action=add&id=<?= $sp['id'] ?>&quantity=1"
                               class="btn btn-sm btn-outline-primary border border-primary ml-2">
                                +
                                <ion-icon name="cart-outline"></ion-icon>
                            </a>
                </div>

            </div>
        </div>
        <?php endwhile; ?>

    </div>
</div>

<!-- DANH SÁCH SẢN PHẨM -->
<div class="container mt-4 bg-light shadow-lg" style="border-radius:15px">
    <h3 class="mb-4 text-center ">DANH SÁCH SẢN PHẨM <?= $cate ? '(Theo danh mục)' : '' ?></h3>

    <div class="row">

        <?php if ($result->num_rows > 0): ?>
            <?php while ($sp = $result->fetch_assoc()): ?>

                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">

                        <div class="p-2 d-flex justify-content-center">
                            <img src="<?= $basePath ?>/uploads/<?= htmlspecialchars($sp['image']) ?>" 
                                 class="shadow-sm"
                                 style="width: 90%; height: 200px; object-fit: cover; border-radius: 15px;">
                        </div>

                        <div class="card-body text-center">
                            <h6 class="mb-2"><?= htmlspecialchars($sp['name']) ?></h6>
                            <p class="text-muted mb-1"><?= htmlspecialchars($sp['category_name']) ?></p>
                            <p class="text-danger font-weight-bold"><?= dinh_dang_gia($sp['price']) ?></p>

                            <a href="<?= $basePath ?>/user/product_details.php?id=<?= $sp['id'] ?>"
                               class="btn btn-sm btn-primary rounded-pill px-3">Xem chi tiết</a>

                            <a href="<?= $basePath ?>/user/cart_handler.php?action=add&id=<?= $sp['id'] ?>&quantity=1"
                               class="btn btn-sm btn-outline-primary border border-primary ml-2">
                                +
                                <ion-icon name="cart-outline"></ion-icon>
                            </a>
                        </div>

                    </div>
                </div>

            <?php endwhile; ?>

        <?php else: ?>
            <div class="col-12 text-center text-muted">Không có sản phẩm nào.</div>
        <?php endif; ?>

    </div>
</div>

<!-- PHÂN TRANG -->
<div class="container text-center mt-3 mb-4">
<?php if ($total_pages > 1): ?>

<?php list($startPage, $endPage) = phan_trang_tinh_trang($page, $total_pages); ?>

<?php if ($page > 1): ?>
<a class="btn btn-outline-primary btn-sm mx-1"
   href="<?= $basePath ?>/user/index.php?page=<?= $page - 1 ?><?= $cate ? '&cate='.$cate : '' ?>"><</a>
<?php endif; ?>

<?php if ($startPage > 1): ?>
<a class="btn btn-outline-primary btn-sm mx-1" 
   href="<?= $basePath ?>/user/index.php?page=1<?= $cate ? '&cate='.$cate : '' ?>">1</a>
<span class="mx-1">...</span>
<?php endif; ?>

<?php for ($i = $startPage; $i <= $endPage; $i++): ?>
<a class="btn btn-sm mx-1 <?= ($i == $page ? 'btn-primary' : 'btn-outline-primary') ?>"
   href="<?= $basePath ?>/user/index.php?page=<?= $i ?><?= $cate ? '&cate='.$cate : '' ?>">
   <?= $i ?>
</a>
<?php endfor; ?>

<?php if ($endPage < $total_pages): ?>
<span class="mx-1">...</span>
<a class="btn btn-outline-primary btn-sm mx-1"
   href="<?= $basePath ?>/user/index.php?page=<?= $total_pages ?><?= $cate ? '&cate='.$cate : '' ?>"><?= $total_pages ?></a>
<?php endif; ?>

<?php if ($page < $total_pages): ?>
<a class="btn btn-outline-primary btn-sm mx-1"
   href="<?= $basePath ?>/user/index.php?page=<?= $page + 1 ?><?= $cate ? '&cate='.$cate : '' ?>">></a>
<?php endif; ?>

<?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
