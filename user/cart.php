<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/header.php";

if (!defined('BASE_URL')) {
    define('BASE_URL', '/TT_Chuyen_Nganh');
}

/* Chuẩn hóa giỏ hàng */
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $pid => $item) {
        if (!is_array($item)) {
            $_SESSION['cart'][$pid] = ['qty' => (int)$item];
        }
    }
}
?>

<div class="container col-10">

<?php if (!empty($_SESSION['cart_error'])): ?>
    <div class="alert alert-warning">
        <?= $_SESSION['cart_error']; unset($_SESSION['cart_error']); ?>
    </div>
<?php endif; ?>

<div class="row mb-3 border-bottom">
    <a href="<?= BASE_URL ?>/user/index.php" class="btn btn-primary ml-3 mb-1">
        <ion-icon name="arrow-back-sharp"></ion-icon>
    </a>
    <h3 class="m-auto">Giỏ hàng của bạn</h3>
</div>

<?php if (!empty($_SESSION['cart'])): ?>

<?php
$cart = $_SESSION['cart'];
$ids  = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("
    SELECT id, name, price, image 
    FROM products 
    WHERE id IN ($placeholders)
");
$stmt->execute($ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_price = 0;
?>

<table class="table table-bordered">
    <thead class="bg-primary text-light">
        <tr>
            <th>Sản phẩm</th>
            <th>Tên sản phẩm</th>
            <th>Giá</th>
            <th>Số lượng</th>
            <th>Thành tiền</th>
            <th>Xóa</th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($products as $row): ?>
        <?php
            $qty = $cart[$row['id']]['qty'];
            $subtotal = $row['price'] * $qty;
            $total_price += $subtotal;
        ?>
        <tr>
            <td>
                <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($row['image']) ?>"
                     width="55" height="55"
                     class="rounded" style="object-fit:cover;">
            </td>

            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= dinh_dang_gia($row['price']) ?></td>

            <td>
                <form action="<?= BASE_URL ?>/user/cart_handler.php" method="post" class="form-inline">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">

                    <input type="number" name="quantity"
                           value="<?= $qty ?>" min="0"
                           class="form-control form-control-sm"
                           style="width:70px;">

                    <button class="btn btn-sm btn-success ml-2">Cập nhật</button>
                </form>
            </td>

            <td><?= dinh_dang_gia($subtotal) ?></td>

            <td>
                <a href="<?= BASE_URL ?>/user/cart_handler.php?action=remove&id=<?= $row['id'] ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Xóa sản phẩm này?');">
                    <ion-icon name="trash-outline"></ion-icon>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>

    <tfoot>
        <tr>
            <td colspan="4" class="text-right"><strong>Tổng cộng:</strong></td>
            <td colspan="2"><strong><?= dinh_dang_gia($total_price) ?></strong></td>
        </tr>
    </tfoot>
</table>

<div class="text-center mb-4">
    <a href="<?= BASE_URL ?>/user/checkout.php" class="btn btn-success px-4 py-2">
        Tiến hành thanh toán
    </a>
</div>

<?php else: ?>

<div class="alert alert-info">
    Giỏ hàng của bạn đang trống. Hãy tiếp tục mua sắm nhé!
</div>

<?php endif; ?>

</div>

<?php require_once "../includes/footer.php"; ?>
