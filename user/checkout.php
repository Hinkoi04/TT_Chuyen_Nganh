<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!defined('BASE_URL')) {
    define('BASE_URL', '/TT_Chuyen_Nganh');
}

/* =============================
   KIỂM TRA ĐĂNG NHẬP & GIỎ HÀNG
   (PHẢI ĐẶT TRƯỚC HEADER)
============================= */

if (!isset($_SESSION['user_id'])) {
    chuyen_trang('/user/login.php');
}

if (empty($_SESSION['cart'])) {
    chuyen_trang('/user/index.php');
}

$checkout_message = "";

/* =============================
   XỬ LÝ ĐẶT HÀNG (POST)
============================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $customer_name    = trim($_POST['customer_name']);
    $customer_address = trim($_POST['customer_address']);
    $customer_phone   = trim($_POST['customer_phone']);
    $user_id          = $_SESSION['user_id'];

    $cart = $_SESSION['cart'];
    $product_ids = implode(",", array_keys($cart));

    // Lấy giá sản phẩm
    $sql = "SELECT id, price FROM products WHERE id IN ($product_ids)";
    $result = $conn->query($sql);

    $total_amount = 0;
    while ($p = $result->fetch_assoc()) {
        $qty = $cart[$p['id']]['qty'];
        $total_amount += $p['price'] * $qty;
    }

    // Bắt đầu transaction
    $conn->begin_transaction();

    try {

        // Tạo đơn hàng
        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, total_amount, customer_name, customer_address, customer_phone)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("idsss", $user_id, $total_amount, $customer_name, $customer_address, $customer_phone);
        $stmt->execute();
        $order_id = $conn->insert_id;
        $stmt->close();

        // Thêm chi tiết đơn hàng
        $stmt_detail = $conn->prepare("
            INSERT INTO order_details (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");

        $result->data_seek(0);
        while ($p = $result->fetch_assoc()) {
            $pid = $p['id'];
            $qty = $cart[$pid]['qty'];
            $price = $p['price'];

            $stmt_detail->bind_param("iiid", $order_id, $pid, $qty, $price);
            $stmt_detail->execute();
        }

        $stmt_detail->close();

        // Xóa giỏ hàng session và DB
        unset($_SESSION['cart']);

        $clear = $conn->prepare("DELETE FROM user_carts WHERE user_id = ?");
        $clear->bind_param("i", $user_id);
        $clear->execute();
        $clear->close();

        $conn->commit();

        $checkout_message = "
            <div class='alert alert-success text-center'>
                ✔ Đặt hàng thành công! Cảm ơn bạn đã mua sắm.
            </div>
        ";

    } catch (Exception $e) {

        $conn->rollback();

        $checkout_message = "
            <div class='alert alert-danger text-center'>
                ❌ Đã xảy ra lỗi, vui lòng thử lại.
            </div>
        ";
    }
}

/* =============================
   GỌI HEADER SAU KHI MỌI REDIRECT ĐÃ HOÀN TẤT
============================= */
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container col-md-6 col-sm-10">

    <h2 class="text-center mb-4">Thanh toán</h2>

    <?= $checkout_message ?>

    <?php if (empty($checkout_message)): ?>
    <form method="POST">

        <h4 class="mb-3">Thông tin giao hàng</h4>

        <div class="form-group">
            <label>Họ và tên</label>
            <input type="text" name="customer_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Địa chỉ giao hàng</label>
            <textarea name="customer_address" class="form-control" rows="2" required></textarea>
        </div>

        <div class="form-group">
            <label>Số điện thoại</label>
            <input type="text" name="customer_phone" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success btn-block mt-3">
            Xác nhận đặt hàng
        </button>

    </form>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
