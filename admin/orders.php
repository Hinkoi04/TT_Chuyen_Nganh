<?php
require_once 'auth_check.php';
require_once '../includes/db.php';

if (isset($_GET['done'])) {
    $id = intval($_GET['done']);

    $stmt = $pdo->prepare("UPDATE orders SET status = 'Đã giao' WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: orders.php");
    exit;
}

if (isset($_GET['cancel'])) {
    $id = intval($_GET['cancel']);

    $pdo->beginTransaction();
    try {
        $stmt1 = $pdo->prepare("DELETE FROM order_details WHERE order_id = ?");
        $stmt1->execute([$id]);

        $stmt2 = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt2->execute([$id]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }

    header("Location: orders.php");
    exit;
}

$stmt = $pdo->query("
    SELECT id, customer_name, total_amount, order_date, status
    FROM orders
    ORDER BY order_date DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

foreach ($orders as $row) {

    $status = htmlspecialchars($row['status']);
    $actions = '';

    if ($status === 'Đang xử lý') {
        $actions = '
            <a href="?done=' . $row['id'] . '" 
               class="btn btn-success btn-sm"
               onclick="return confirm(\'Xác nhận đơn hàng đã giao?\')">
               Đã giao
            </a>

            <a href="?cancel=' . $row['id'] . '" 
               class="btn btn-danger btn-sm ml-2"
               onclick="return confirm(\'Bạn chắc muốn hủy đơn hàng này?\')">
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
            <td>{$status}</td>
            <td>{$actions}</td>
        </tr>
    ";
}

$page_content .= '
    </tbody>
</table>
';

include "index.php";
