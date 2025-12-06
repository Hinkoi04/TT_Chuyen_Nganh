<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!defined('BASE_URL')) {
    define('BASE_URL', '/TT_Chuyen_Nganh');
}

$action     = $_POST['action'] ?? $_GET['action'] ?? '';
$product_id = (int)($_POST['product_id'] ?? $_GET['id'] ?? 0);
$quantity   = (int)($_POST['quantity'] ?? $_GET['quantity'] ?? 1);

$user_id = $_SESSION['user_id'] ?? null;

/* ================================
   XỬ LÝ TỪNG ACTION
================================= */

switch ($action) {

    /* ---------- THÊM SẢN PHẨM ---------- */
    case 'add':
        if ($product_id > 0) {

            if (!kiem_tra_ton_kho($product_id)) {
                $_SESSION['cart_error'] = "❌ Sản phẩm đã hết hàng!";
                chuyen_trang('/user/index.php');
            }

            them_vao_gio($product_id, 1);

            if ($user_id) {
                them_gio_hang_db($user_id, $product_id, 1);
            }
        }
        break;


    /* ---------- XÓA SẢN PHẨM ---------- */
    case 'remove':
        xoa_khoi_gio($product_id);
        if ($user_id) {
            xoa_gio_hang_db($user_id, $product_id);
        }
        break;


    /* ---------- CẬP NHẬT SỐ LƯỢNG ---------- */
    case 'update':
        if ($product_id > 0) {

            $stock = lay_so_luong_ton($product_id);

            if ($quantity <= 0) {
                xoa_khoi_gio($product_id);
                if ($user_id) xoa_gio_hang_db($user_id, $product_id);
            } 
            else if ($quantity > $stock) {
                $_SESSION['cart'][$product_id]['qty'] = $stock;

                if ($user_id) cap_nhat_gio_hang_db($user_id, $product_id, $stock);

                $_SESSION['cart_error'] = "Sản phẩm chỉ còn {$stock} cái trong kho!";
            }
            else {
                $_SESSION['cart'][$product_id]['qty'] = $quantity;

                if ($user_id) cap_nhat_gio_hang_db($user_id, $product_id, $quantity);
            }
        }
        break;

}

/* ---------- CHUYỂN VỀ GIỎ HÀNG ---------- */
chuyen_trang('/user/cart.php');
