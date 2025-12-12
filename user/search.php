<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../includes/db.php";
require_once "../includes/functions.php";

$q    = trim($_GET['q'] ?? '');
$cate = intval($_GET['cate'] ?? 0);

/* LẤY TÊN DANH MỤC */
$cateName = "";
if ($cate > 0) {
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$cate]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $cateName = $row['name'] ?? '';
}

/* TRUY VẤN TÌM KIẾM */
$sql = "
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.name LIKE ?
";
$params = ["%$q%"];

if ($cate > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $cate;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once "../includes/header.php";
?>

<div class="container mt-4">

    <?php if ($q !== ''): ?>
        <h4>
            Kết quả tìm kiếm cho từ khóa:
            <span class="text-primary">"<?= htmlspecialchars($q) ?>"</span>
        </h4>
    <?php elseif ($cate > 0): ?>
        <h4>
            Sản phẩm thuộc danh mục:
            <span class="text-primary">"<?= htmlspecialchars($cateName) ?>"</span>
        </h4>
    <?php else: ?>
        <h4>Tất cả sản phẩm</h4>
    <?php endif; ?>

    <div class="row mt-3">

        <?php if (!empty($products)): ?>
            <?php foreach ($products as $sp): ?>

                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-lg border-0" style="border-radius:15px; overflow:hidden;">

                        <img src="../uploads/<?= htmlspecialchars($sp['image']) ?>"
                             class="card-img-top"
                             style="height:200px; object-fit:cover;">

                        <div class="card-body text-center">
                            <h6 class="mb-1"><?= htmlspecialchars($sp['name']) ?></h6>

                            <p class="text-muted small">
                                <?= htmlspecialchars($sp['category_name']) ?>
                            </p>

                            <p class="text-danger font-weight-bold">
                                <?= dinh_dang_gia($sp['price']) ?>
                            </p>

                            <a href="product_details.php?id=<?= $sp['id'] ?>"
                               class="btn btn-primary btn-sm rounded-pill px-3">
                                Xem chi tiết
                            </a>

                            <a href="cart_handler.php?action=add&id=<?= $sp['id'] ?>&quantity=1"
                               class="btn btn-outline-primary btn-sm rounded-pill ml-2">
                                +
                                <ion-icon name="cart-outline"
                                          style="font-size:18px; vertical-align:middle;"></ion-icon>
                            </a>
                        </div>

                    </div>
                </div>

            <?php endforeach; ?>
        <?php else: ?>

            <div class="col-12 text-center text-muted mt-4">
                Không tìm thấy sản phẩm phù hợp.
            </div>

        <?php endif; ?>

    </div>
</div>

<?php require_once "../includes/footer.php"; ?>
