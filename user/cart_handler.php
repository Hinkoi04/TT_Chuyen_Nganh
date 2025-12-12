<?php
session_start();

$action     = $_POST['action'] ?? $_GET['action'] ?? '';
$product_id = (int)($_POST['product_id'] ?? $_GET['id'] ?? 0);
$quantity   = (int)($_POST['quantity'] ?? $_GET['quantity'] ?? 1);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

switch ($action) {

    case 'add':
        if ($product_id > 0) {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['qty']++;
            } else {
                $_SESSION['cart'][$product_id] = ['qty' => 1];
            }
        }
        break;

    case 'update':
        if ($product_id > 0) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$product_id]);
            } else {
                $_SESSION['cart'][$product_id]['qty'] = $quantity;
            }
        }
        break;

    case 'remove':
        if ($product_id > 0) {
            unset($_SESSION['cart'][$product_id]);
        }
        break;
}

header("Location: cart.php");
exit;
