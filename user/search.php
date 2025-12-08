<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!defined('BASE_URL')) {
    define('BASE_URL', '/TT_Chuyen_Nganh');
}

/* Lấy dữ liệu từ URL */
$q    = trim($_GET['q'] ?? '');
$cate = intval($_GET['cate'] ?? 0);

/* Lấy tên danh mục nếu có */
$cateName = '';
if ($cate > 0) {
    $dm = lay_danh_muc();
    foreach ($dm as $row) {
        if ($row['id'] == $cate) {
            $cateName = $row['name'];
            break;
        }
    }
}

/* Truy vấn tìm kiếm */
$result = tim_kiem_san_pham($q, $cate);
?>

<div class="container mt-4">

    <!-- Tiêu đề tìm kiếm -->
    <?php if ($q !== ''): ?>
        <h4>Kết quả tìm kiếm cho từ khóa:
            <span class="text-primary">"<?= htmlspecialchars($q) ?>"</span>
        </h4>

    <?php elseif ($cate > 0): ?>
        <h4>Sản phẩm thuộc danh mục:
            <span class="text-primary">"<?= htmlspecialchars($cateName) ?>"</span>
        </h4>

    <?php else: ?>
        <h4>Tất cả sản phẩm</h4>
    <?php endif; ?>

    <div class="row mt-3">

        <?php if ($result->num_rows > 0): ?>
            <?php while ($sp = $result->fetch_assoc()): ?>

                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-lg border-0" style="border-radius: 15px; overflow: hidden;">

                        <!-- Ảnh sản phẩm -->
                        <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($sp['image']) ?>"
                             class="card-img-top"
                             style="height: 200px; object-fit: cover;">

                        <div class="card-body text-center">
                            <h6 class="mb-1"><?= htmlspecialchars($sp['name']) ?></h6>

                            <p class="text-muted small">
                                <?= htmlspecialchars($sp['category_name']) ?>
                            </p>

                            <p class="text-danger font-weight-bold">
                                <?= dinh_dang_gia($sp['price']) ?>
                            </p>

                            <!-- Xem chi tiết -->
                            <a href="<?= BASE_URL ?>/user/product_details.php?id=<?= $sp['id'] ?>"
                               class="btn btn-primary btn-sm rounded-pill px-3">
                                Xem chi tiết
                            </a>

                            <!-- ADD to cart -->
                            <a href="<?= BASE_URL ?>/user/cart_handler.php?action=add&id=<?= $sp['id'] ?>&quantity=1"
                               class="btn btn-outline-primary btn-sm rounded-pill ml-2">
                                +
                                <ion-icon name="cart-outline" style="font-size:18px; vertical-align:middle;"></ion-icon>
                            </a>
                        </div>

                    </div>
                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="col-12 text-center text-muted mt-4">
                Không tìm thấy sản phẩm phù hợp.
            </div>

        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
