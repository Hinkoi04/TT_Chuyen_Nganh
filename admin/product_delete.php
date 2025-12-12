<?php
require_once 'auth_check.php';
require_once '../includes/db.php';

$id = $_GET['id'] ?? 0;
if (!is_numeric($id)) {
    die('ID không hợp lệ');
}

$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$success = $stmt->execute([$id]);

if ($success) {
    header("Location: products.php");
    exit();
} else {
    echo "Lỗi khi xóa sản phẩm";
}
