<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

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
                    <div class="card h-100 shadow-sm">

                        <img src="/uploads/<?= htmlspecialchars($sp['image']) ?>"
                             class="card-img-top"
                             style="height:200px; object-fit:cover;"
                        >

                        <div class="card-body text-center">
                            <h6><?= htmlspecialchars($sp['name']) ?></h6>
                            <p class="text-muted small">
                                <?= htmlspecialchars($sp['category_name']) ?>
                            </p>
                            <p class="text-danger font-weight-bold">
                                <?= dinh_dang_gia($sp['price']) ?>
                            </p>

                            <a href="/user/product_details.php?id=<?= $sp['id'] ?>"
                               class="btn btn-sm btn-primary rounded-pill px-3">
                                Xem chi tiết
                            </a>
                        </div>

                    </div>
                </div>
            <?php endwhile; ?>

        <?php else: ?>

            <div class="col-12 text-center text-muted">
                Không tìm thấy sản phẩm phù hợp.
            </div>

        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
