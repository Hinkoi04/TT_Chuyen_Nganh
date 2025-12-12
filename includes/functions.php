<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

/* ====== TIỆN ÍCH ====== */
function dinh_dang_gia($gia) {
    return number_format($gia, 0, ',', '.') . "₫";
}

/* ====== DANH MỤC ====== */
function lay_danh_muc() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    return $stmt->fetchAll();
}

/* ====== BANNER ====== */
function lay_banner() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM banners ORDER BY uploaded_at DESC");
    return $stmt;
}

/* ====== SẢN PHẨM ====== */
function lay_tong_san_pham($cate = 0) {
    global $pdo;

    if ($cate > 0) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$cate]);
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    }

    return (int)$stmt->fetchColumn();
}

function lay_san_pham_phan_trang($cate, $start, $limit) {
    global $pdo;

    $sql = "
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
    ";

    $params = [];

    if ($cate > 0) {
        $sql .= " WHERE p.category_id = ?";
        $params[] = $cate;
    }

    $sql .= " ORDER BY p.id DESC LIMIT $start, $limit";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function lay_san_pham_theo_id($id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function lay_anh_phu($product_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT image FROM product_images WHERE product_id = ?");
    $stmt->execute([$product_id]);
    return $stmt;
}

/* ====== TÌM KIẾM ====== */
function tim_kiem_san_pham($q, $cate = 0) {
    global $pdo;

    $sql = "
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.name LIKE ?
    ";

    $params = ["%$q%"];

    if ($cate > 0) {
        $sql .= " AND p.category_id = ?";
        $params[] = $cate;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/* ====== GIỎ HÀNG (SESSION) ====== */
function them_vao_gio($id, $qty = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$id] = ['qty' => $qty];
    }
}

function xoa_khoi_gio($id) {
    unset($_SESSION['cart'][$id]);
}

function lay_so_luong_ton($product_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    return (int)$stmt->fetchColumn();
}

function kiem_tra_ton_kho($product_id) {
    return lay_so_luong_ton($product_id) > 0;
}

/* ====== PHÂN TRANG ====== */
function phan_trang_tinh_trang($page, $total_pages) {
    $startPage = max(1, $page - 2);
    $endPage   = min($total_pages, $page + 2);

    if ($page <= 3) {
        $startPage = 1;
        $endPage = min(5, $total_pages);
    }

    if ($page >= $total_pages - 2) {
        $startPage = max(1, $total_pages - 4);
        $endPage = $total_pages;
    }

    return [$startPage, $endPage];
}

/* ====== SẢN PHẨM MỚI NHẤT ====== */
function lay_san_pham_moi_nhat($limit = 4) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}
function lay_danh_gia($product_id)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT r.*, u.fullname
        FROM product_reviews r
        JOIN users u ON u.id = r.user_id
        WHERE r.product_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$product_id]);
    return $stmt;
}

function lay_tb_sao($product_id)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(AVG(rating), 0) AS avg_star,
            COUNT(*) AS total_reviews
        FROM product_reviews
        WHERE product_id = ?
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetch();
}
