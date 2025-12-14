<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../includes/db.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $product_id = 0;
} else {
    $product_id = (int)$_GET['id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $product_id > 0) {

    $user_id = $_SESSION['user_id'];
    $rating  = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating >= 1 && $rating <= 5 && $comment !== '') {

        $check = $pdo->prepare("SELECT id FROM product_reviews WHERE user_id = ? AND product_id = ?");
        $check->execute([$user_id, $product_id]);

        if ($check->fetch()) {
            $up = $pdo->prepare("UPDATE product_reviews SET rating = ?, comment = ? WHERE user_id = ? AND product_id = ?");
            $up->execute([$rating, $comment, $user_id, $product_id]);
        } else {
            $ins = $pdo->prepare("INSERT INTO product_reviews (user_id, product_id, rating, comment) VALUES (?,?,?,?)");
            $ins->execute([$user_id, $product_id, $rating, $comment]);
        }

        header("Location: product_details.php?id=" . $product_id);
        exit;
    }
}

require_once "../includes/header.php";

if ($product_id <= 0) {
    echo "<div class='alert alert-danger m-4'>Sản phẩm không hợp lệ</div>";
    require_once "../includes/footer.php";
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<div class='alert alert-danger m-4'>Sản phẩm không tồn tại</div>";
    require_once "../includes/footer.php";
    exit;
}

$imgs = $pdo->prepare("SELECT image FROM product_images WHERE product_id = ?");
$imgs->execute([$product_id]);
$images = $imgs->fetchAll(PDO::FETCH_COLUMN);

$rv = $pdo->prepare("
    SELECT r.*, u.fullname
    FROM product_reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$rv->execute([$product_id]);

$avgQ = $pdo->prepare("
    SELECT COALESCE(AVG(rating),0) avg_star, COUNT(*) total
    FROM product_reviews
    WHERE product_id = ?
");
$avgQ->execute([$product_id]);
$avg = $avgQ->fetch(PDO::FETCH_ASSOC);

$avg_star  = (float)($avg['avg_star'] ?? 0);
$total_rev = (int)($avg['total'] ?? 0);
?>

<style>
.image-box{background:#fff;padding:15px;border-radius:18px;box-shadow:0 8px 20px rgba(0,0,0,.12)}
.thumb-img{width:90px;height:90px;object-fit:cover;border-radius:8px;cursor:pointer;border:2px solid #ddd}
.thumb-img:hover{border-color:#007bff}
.product-box{background:#fff;border-radius:18px;padding:25px;box-shadow:0 10px 25px rgba(0,0,0,.12)}
.spec-box{background:linear-gradient(135deg,rgba(52,152,219,.08),rgba(41,128,185,.15));border-radius:16px;padding:20px}
.star{font-size:26px;cursor:pointer;color:#ccc}
.star.selected{color:#f4c542}
</style>

<div class="container my-5">
<div class="row">

<div class="col-md-5 image-box">
    <img id="mainImage"
         src="../uploads/<?= htmlspecialchars($product['image']) ?>"
         class="img-fluid rounded mb-3"
         style="height:350px;object-fit:cover">

    <div class="d-flex flex-wrap">
        <img src="../uploads/<?= htmlspecialchars($product['image']) ?>"
             class="thumb-img mr-2 mb-2"
             onclick="mainImage.src=this.src">

        <?php foreach ($images as $img): ?>
            <img src="../uploads/products/<?= htmlspecialchars($img) ?>"
                 class="thumb-img mr-2 mb-2"
                 onclick="mainImage.src=this.src">
        <?php endforeach; ?>
    </div>
</div>

<div class="col-md-7 product-box">
    <h2><?= htmlspecialchars($product['name']) ?></h2>
    <p class="text-muted"><?= htmlspecialchars($product['category_name']) ?></p>
    <h3 class="text-danger"><?= number_format($product['price'],0,',','.') ?> VNĐ</h3>
    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
    <p><strong>Còn lại:</strong> <?= (int)$product['stock_quantity'] ?></p>

    <form action="cart_handler.php" method="get">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="id" value="<?= $product_id ?>">
        <input type="number" name="quantity" value="1" min="1"
               max="<?= (int)$product['stock_quantity'] ?>"
               class="form-control w-25 mb-2">
        <button class="btn btn-primary">🛒 Thêm vào giỏ</button>
        <a href="index.php" class="btn btn-secondary">⬅ Quay lại</a>
    </form>

    <?php if ($product['cpu'] || $product['ram'] || $product['storage'] || $product['gpu'] || $product['screen']): ?>
    <div class="spec-box mt-4">
        <h4>⚙️ Thông số kỹ thuật</h4>
        <ul class="list-group">
            <?php if ($product['cpu']): ?><li class="list-group-item">CPU: <?= htmlspecialchars($product['cpu']) ?></li><?php endif; ?>
            <?php if ($product['ram']): ?><li class="list-group-item">RAM: <?= htmlspecialchars($product['ram']) ?></li><?php endif; ?>
            <?php if ($product['storage']): ?><li class="list-group-item">Ổ cứng: <?= htmlspecialchars($product['storage']) ?></li><?php endif; ?>
            <?php if ($product['gpu']): ?><li class="list-group-item">GPU: <?= htmlspecialchars($product['gpu']) ?></li><?php endif; ?>
            <?php if ($product['screen']): ?><li class="list-group-item">Màn hình: <?= htmlspecialchars($product['screen']) ?></li><?php endif; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>

</div>

<hr>

<h3>⭐ Đánh giá sản phẩm</h3>
<p class="text-warning">
<?= str_repeat("★", floor($avg_star)) . str_repeat("☆", 5-floor($avg_star)) ?>
(<?= round($avg_star,1) ?>/5 – <?= $total_rev ?> đánh giá)
</p>

<?php while ($r = $rv->fetch(PDO::FETCH_ASSOC)): ?>
<div class="border rounded p-3 mb-3 bg-light">
    <strong><?= htmlspecialchars($r['fullname']) ?></strong>
    <div class="text-warning">
        <?= str_repeat("★",(int)$r['rating']) . str_repeat("☆", 5-(int)$r['rating']) ?>
    </div>
    <p><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
</div>
<?php endwhile; ?>

<?php if (isset($_SESSION['user_id'])): ?>
<hr>
<h4>📝 Viết / Cập nhật đánh giá</h4>

<form method="post">
    <input type="hidden" name="rating" id="rating">
    <div>
        <?php for ($i=1;$i<=5;$i++): ?>
            <span class="star" data-v="<?= $i ?>">★</span>
        <?php endfor; ?>
    </div>
    <textarea name="comment" class="form-control mt-2" required></textarea>
    <button class="btn btn-success mt-2">Gửi đánh giá</button>
</form>

<script>
document.querySelectorAll('.star').forEach(s=>{
    s.onclick=()=>{
        document.getElementById('rating').value=s.dataset.v;
        document.querySelectorAll('.star').forEach(x=>{
            x.classList.toggle('selected',x.dataset.v<=s.dataset.v)
        })
    }
})
</script>
<?php else: ?>
<div class="alert alert-info mt-3">
    🔒 Vui lòng <a href="login.php">đăng nhập</a> để đánh giá sản phẩm
</div>
<?php endif; ?>

</div>

<?php require_once "../includes/footer.php"; ?>
