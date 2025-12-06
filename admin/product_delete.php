<?php
require_once 'auth_check.php';
require_once '../includes/db.php';

$id = $_GET['id'];
if (!is_numeric($id)) die('ID không hợp lệ');

$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: products.php");
    exit();
} else {
    echo "Lỗi khi xóa sản phẩm: " . $stmt->error;
}
$stmt->close();