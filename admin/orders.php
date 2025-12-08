<?php
require_once 'auth_check.php';
require_once '../includes/db.php';

/* =======================
   XỬ LÝ CẬP NHẬT TRẠNG THÁI
   ======================= */
if (isset($_GET['done'])) {
    $id = intval($_GET['done']);

    $stmt = $conn->prepare("UPDATE orders SET status = 'Đã giao' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: orders.php");
    exit();
}

if (isset($_GET['cancel'])) {
    $id = intval($_GET['cancel']);

    // Xóa chi tiết đơn hàng
    $delDetail = $conn->prepare("DELETE FROM order_details WHERE order_id = ?");
    $delDetail->bind_param("i", $id);
    $delDetail->execute();

    // Xóa đơn hàng
    $delOrder = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $delOrder->bind_param("i", $id);
    $delOrder->execute();

    header("Location: orders.php");
    exit();
}

/* =======================
   LẤY DANH SÁCH ĐƠN HÀNG
   ======================= */
$sql = "SELECT id, customer_name, total_amount, order_date, status 
        FROM orders 
        ORDER BY order_date DESC";

$result = $conn->query($sql);

/* =======================
   GIAO DIỆN TRANG
   ======================= */

$page_title = "Quản lý Đơn hàng";

$page_content = '
    <h1 class="text-center mb-4">Danh sách Đơn hàng</h1>

    <table class="table table-bordered table-hover text-center">
        <thead class="thead bg-primary text-white">
            <tr>
                <th>ID</th>
                <th>Khách hàng</th>
                <th>Tổng tiền</th>
                <th>Ngày đặt</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
';
while ($row = $result->fetch_assoc()) {

    $status = htmlspecialchars($row['status']);
    $actions = '';

    // Nếu đơn đang xử lý → Hiển thị nút
    if ($status === "Đang xử lý") {
        $actions = '
            <a href="?done=' . $row['id'] . '" 
               class="btn btn-success btn-sm"
               onclick="return confirm(\'Xác nhận đơn hàng đã giao?\')">
               Đã giao
            </a>

            <a href="?cancel=' . $row['id'] . '" 
               class="btn btn-danger btn-sm ml-2"
               onclick="return confirm(\'Bạn chắc muốn hủy đơn hàng này? Sau khi hủy sẽ bị xóa khỏi hệ thống!\')">
               Hủy đơn
            </a>
        ';
    } else {
        $actions = '<span class="badge badge-success p-2">Đã giao</span>';
    }

    $page_content .= "
        <tr>
            <td>{$row['id']}</td>
            <td>" . htmlspecialchars($row['customer_name']) . "</td>
            <td>" . number_format($row['total_amount']) . " VNĐ</td>
            <td>{$row['order_date']}</td>
            <td>$status</td>
            <td>$actions</td>
        </tr>
    ";
}

$page_content .= '
        </tbody>
    </table>
';

include "index.php";
?>
