<?php
require_once 'auth_check.php';
require_once '../includes/db.php';

// lấy id ảnh
$image_id = $_GET['id'] ?? 0;
$product_id = $_GET['pid'] ?? 0;

if (!is_numeric($image_id) || !is_numeric($product_id)) {
    die("ID không hợp lệ");
}

// 1) Lấy file ảnh trong DB
$stmt = $conn->prepare("SELECT image FROM product_images WHERE id=?");
$stmt->bind_param("i", $image_id);
$stmt->execute();
$stmt->bind_result($filename);
$stmt->fetch();
$stmt->close();

// 2) Xóa file trên server
if ($filename && file_exists("../uploads/products/" . $filename)) {
    unlink("../uploads/products/" . $filename);
}

// 3) Xóa trong DB
$stmt_del = $conn->prepare("DELETE FROM product_images WHERE id=?");
$stmt_del->bind_param("i", $image_id);
$stmt_del->execute();
$stmt_del->close();

// quay lại trang sửa sản phẩm
header("Location: product_edit.php?id=" . $product_id . "&deleted=1");
exit();
?>
