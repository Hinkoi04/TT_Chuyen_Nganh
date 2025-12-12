<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['customer_name']);
    $address = trim($_POST['customer_address']);
    $phone   = trim($_POST['customer_phone']);
    $user_id = $_SESSION['user_id'];

    if ($name === '' || $address === '' || $phone === '') {
        $message = "<div class='alert alert-danger'>Vui lòng nhập đầy đủ thông tin.</div>";
    } else {

        $cart = $_SESSION['cart'];
        $ids  = array_keys($cart);
        $in   = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($in)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = 0;
        foreach ($products as $p) {
            $qty = $cart[$p['id']]['qty'];
            $total += $p['price'] * $qty;
        }

        try {
            $pdo->beginTransaction();

            $orderStmt = $pdo->prepare("
                INSERT INTO orders (user_id, total_amount, customer_name, customer_address, customer_phone)
                VALUES (?, ?, ?, ?, ?)
            ");
            $orderStmt->execute([$user_id, $total, $name, $address, $phone]);
            $order_id = $pdo->lastInsertId();

            $detailStmt = $pdo->prepare("
                INSERT INTO order_details (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($products as $p) {
                $detailStmt->execute([
                    $order_id,
                    $p['id'],
                    $cart[$p['id']]['qty'],
                    $p['price']
                ]);
            }

            $pdo->commit();
            unset($_SESSION['cart']);

            $message = "<div class='alert alert-success text-center'>✔ Đặt hàng thành công!</div>";

        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "<div class='alert alert-danger'>❌ Lỗi khi đặt hàng.</div>";
        }
    }
}
?>

<div class="container col-md-6 mt-4">
    <h2 class="text-center mb-3">Thanh toán</h2>
    <?= $message ?>

    <?php if ($message === ""): ?>
    <form method="POST">
        <div class="form-group">
            <label>Họ và tên</label>
            <input type="text" name="customer_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Địa chỉ</label>
            <textarea name="customer_address" class="form-control" required></textarea>
        </div>
        <div class="form-group">
            <label>Số điện thoại</label>
            <input type="text" name="customer_phone" class="form-control" required>
        </div>
        <button class="btn btn-success btn-block mt-3">Xác nhận đặt hàng</button>
    </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
