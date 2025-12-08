<?php
require_once 'auth_check.php';
require_once '../includes/db.php';
$result = $conn->query("SELECT id, name, price, stock_quantity FROM products ORDER BY id DESC");
$page_title = "Quản lý sản phẩm";
$page_content = '
    <h1 class="text-center mb-4">Danh sách sản phẩm</h1>

    <a href="product_add.php" class="btn btn-primary mb-3">
        <ion-icon name="add-circle-outline"></ion-icon> 
        Thêm sản phẩm mới
    </a>

    <table class="table table-bordered table-hover">
        <thead class="thead bg-primary">
            <tr>
                <th>ID</th>
                <th>Tên sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
';
while ($row = $result->fetch_assoc()) {
    $id      = $row['id'];
    $name    = htmlspecialchars($row['name']);
    $price   = number_format($row['price']) . "₫";
    $stock   = intval($row['stock_quantity']);

    $page_content .= "
        <tr>
            <td>{$id}</td>
            <td>{$name}</td>
            <td>{$price}</td>
            <td>{$stock}</td>
            <td>
                <a href='product_edit.php?id={$id}' class='btn btn-info btn-sm'>
                    <ion-icon name=\"create-outline\"></ion-icon>
                </a>

                <a href='product_delete.php?id={$id}'
                   onclick='return confirm(\"Bạn chắc chắn muốn xóa sản phẩm này?\")'
                   class='btn btn-danger btn-sm'>
                    <ion-icon name=\"trash-outline\"></ion-icon>
                </a>
            </td>
        </tr>
    ";
}

$page_content .= "
        </tbody>
    </table>
";

include "index.php";
?>
