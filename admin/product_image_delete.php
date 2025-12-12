<?php
require_once 'auth_check.php';
require_once '../includes/db.php';

$image_id   = $_GET['id'] ?? 0;
$product_id = $_GET['pid'] ?? 0;

if (!is_numeric($image_id) || !is_numeric($product_id)) {
    die("ID không hợp lệ");
}

$stmt = $pdo->prepare("SELECT image FROM product_images WHERE id = ?");
$stmt->execute([$image_id]);
$filename = $stmt->fetchColumn();

if ($filename && file_exists("../uploads/products/" . $filename)) {
    unlink("../uploads/products/" . $filename);
}

$stmt_del = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
$stmt_del->execute([$image_id]);

header("Location: product_edit.php?id=" . $product_id . "&deleted=1");
exit();
