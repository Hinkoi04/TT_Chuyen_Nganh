<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

/* ============================
   XỬ LÝ SUBMIT ĐÁNH GIÁ — PHẢI ĐẶT TRƯỚC HEADER
   ============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    xu_ly_submit_danh_gia_tai_chi_tiet();
}

/* SAU KHI ĐÃ XỬ LÝ XONG → MỚI GỌI HEADER */
require_once __DIR__ . '/../includes/header.php';

/* ============================
   KIỂM TRA & LẤY DỮ LIỆU SẢN PHẨM
   ============================ */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    chuyen_trang("/user/index.php");
}
$product_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<div class='alert alert-danger mt-4'>❌ Sản phẩm không tồn tại.</div>");
}

$product = $result->fetch_assoc();
$stmt->close();
?>

<style>
.thumb-img {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border: 2px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.2s;
}
.thumb-img:hover {
    border-color: #007bff;
    transform: scale(1.05);
}

.star-rating {
    font-size: 28px;
    cursor: pointer;
}
.star-rating .star {
    color: #ccc;
    transition: 0.2s;
}
.star-rating .star.selected {
    color: #f4c542;
}
</style>

<div class="container my-5">
<div class="row">

<!-- ẢNH SẢN PHẨM -->
<div class="col-md-5">

    <!-- ẢNH CHÍNH -->
    <img id="mainImage"
         src="../uploads/<?= htmlspecialchars($product['image']) ?>"
         class="img-fluid rounded border mb-3"
         style="width:100%; height:350px; object-fit:cover;">

    <?php
    // Lấy ảnh phụ
    $imgs = $conn->query("SELECT image FROM product_images WHERE product_id = $product_id");

    $all_images = ["../uploads/" . $product['image']];
    while ($i = $imgs->fetch_assoc()) {
        $all_images[] = "../uploads/products/" . $i['image'];
    }

    $chunks = array_chunk($all_images, 4);
    ?>

    <!-- CAROUSEL ẢNH PHỤ -->
    <div id="thumbCarousel" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
            <?php $first = true; ?>
            <?php foreach ($chunks as $chunk): ?>
                <div class="carousel-item <?= $first ? 'active' : '' ?>">
                    <div class="d-flex">
                        <?php foreach ($chunk as $img): ?>
                            <img src="<?= $img ?>"
                                 class="thumb-img mx-1"
                                 onclick="document.getElementById('mainImage').src='<?= $img ?>'">
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php $first = false; ?>
            <?php endforeach; ?>
        </div>

        <a class="carousel-control-prev" href="#thumbCarousel" data-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </a>
        <a class="carousel-control-next" href="#thumbCarousel" data-slide="next">
            <span class="carousel-control-next-icon"></span>
        </a>
    </div>

</div>

<!-- THÔNG TIN SP -->
<div class="col-md-7">
    <h2><?= htmlspecialchars($product['name']) ?></h2>
    <p class="text-muted">
        <strong>Danh mục:</strong> <?= htmlspecialchars($product['category_name']) ?>
    </p>

    <h3 class="text-danger"><?= number_format($product['price'], 0, ',', '.') ?> VNĐ</h3>

    <p><strong>Mô tả:</strong><br><?= nl2br(htmlspecialchars($product['description'])) ?></p>

    <p><strong>Còn lại:</strong> <?= intval($product['stock_quantity']) ?> sản phẩm</p>

    <form action="cart_handler.php" method="GET" class="mt-3">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="id" value="<?= $product['id'] ?>">

        <label>Số lượng:</label>
        <input type="number" name="quantity" value="1" min="1"
               max="<?= $product['stock_quantity'] ?>"
               class="form-control d-inline-block"
               style="width: 100px;">
        <button class="btn btn-primary mt-2">🛒 Thêm vào giỏ hàng</button>
        <a href="index.php" class="btn btn-secondary mt-2">⬅ Quay lại</a>
    </form>

    <!-- THÔNG SỐ KỸ THUẬT -->
    <?php if ($product['cpu'] || $product['ram'] || $product['storage'] || $product['gpu'] || $product['screen']): ?>
        <hr>
        <h4>⚙️ Thông số kỹ thuật</h4>
        <ul class="list-group">
            <?php if ($product['cpu']): ?>
                <li class="list-group-item"><strong>CPU:</strong> <?= $product['cpu'] ?></li>
            <?php endif; ?>
            <?php if ($product['ram']): ?>
                <li class="list-group-item"><strong>RAM:</strong> <?= $product['ram'] ?></li>
            <?php endif; ?>
            <?php if ($product['storage']): ?>
                <li class="list-group-item"><strong>Ổ cứng:</strong> <?= $product['storage'] ?></li>
            <?php endif; ?>
            <?php if ($product['gpu']): ?>
                <li class="list-group-item"><strong>GPU:</strong> <?= $product['gpu'] ?></li>
            <?php endif; ?>
            <?php if ($product['screen']): ?>
                <li class="list-group-item"><strong>Màn hình:</strong> <?= $product['screen'] ?></li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>

</div>
</div>

<hr>
<!-- ============================
     ĐÁNH GIÁ SẢN PHẨM
     ============================ -->
<h3 class="mt-4">⭐ Đánh giá sản phẩm</h3>

<?php
$reviews = lay_danh_gia($product_id);
$avg = lay_tb_sao($product_id);
?>

<!-- Điểm trung bình -->
<div class="mb-3">
    <h4>
        <span class="text-warning">
            <?= str_repeat("★", floor($avg['avg_star'])) . str_repeat("☆", 5 - floor($avg['avg_star'])) ?>
        </span>
        <small class="text-muted">
            (<?= round($avg['avg_star'], 1) ?>/5 - <?= $avg['total_reviews'] ?> đánh giá)
        </small>
    </h4>
</div>

<!-- Danh sách đánh giá -->
<?php if ($reviews->num_rows > 0): ?>
    <?php while ($r = $reviews->fetch_assoc()): ?>
        <div class="border rounded p-3 mb-3 bg-light">
            <strong><?= htmlspecialchars($r['fullname']) ?></strong>
            <div class="text-warning">
                <?= str_repeat("★", $r['rating']) . str_repeat("☆", 5 - $r['rating']) ?>
            </div>
            <p><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
            <small class="text-muted"><?= $r['created_at'] ?></small>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p class="text-muted">Chưa có đánh giá nào.</p>
<?php endif; ?>

<!-- Form đánh giá -->
<?php if (isset($_SESSION['user_id'])): ?>
    <hr>
    <h4>📝 Viết đánh giá</h4>

    <form method="POST">
        <input type="hidden" name="product_id" value="<?= $product_id ?>">
        <input type="hidden" name="rating" id="ratingInput">

        <label>Chọn số sao:</label>
        <div class="star-rating mb-2">
            <span class="star" data-value="1">★</span>
            <span class="star" data-value="2">★</span>
            <span class="star" data-value="3">★</span>
            <span class="star" data-value="4">★</span>
            <span class="star" data-value="5">★</span>
        </div>

        <textarea name="comment" class="form-control" rows="3" required></textarea>
        <button class="btn btn-success mt-2">Gửi đánh giá</button>
    </form>

    <script>
    const stars = document.querySelectorAll('.star-rating .star');
    const ratingInput = document.getElementById('ratingInput');
    let currentRating = 0;

    stars.forEach(star => {
        star.addEventListener('click', () => {
            currentRating = star.dataset.value;
            ratingInput.value = currentRating;
            updateStars();
        });
    });

    function updateStars() {
        stars.forEach(s => {
            s.classList.toggle('selected', s.dataset.value <= currentRating);
        });
    }
    </script>

<?php else: ?>
    <p class="text-muted">
        Bạn cần <a href="login.php">đăng nhập</a> để đánh giá.
    </p>
<?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
