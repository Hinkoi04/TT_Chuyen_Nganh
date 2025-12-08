<?php
/* NHOM HAM CO BAN */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function dinh_dang_gia($gia) {
    return number_format($gia, 0, ',', '.') . "₫";
}

function chuyen_trang($url) {
    // Nếu URL đã bắt đầu bằng http hoặc https → chuyển luôn
    if (preg_match('/^https?:\/\//', $url)) {
        header("Location: $url");
        exit;
    }

    // Nếu URL bắt đầu bằng BASE_URL → không gắn BASE_URL nữa
    if (str_starts_with($url, BASE_URL)) {
        header("Location: $url");
        exit;
    }

    // Nếu URL bắt đầu bằng "/" → gắn BASE_URL vào trước
    if (str_starts_with($url, '/')) {
        header("Location: " . BASE_URL . $url);
        exit;
    }

    // Mặc định (trường hợp hiếm)
    header("Location: " . BASE_URL . "/" . ltrim($url, '/'));
    exit;
}



/* NHOM HAM DANH MUC */
function lay_danh_muc() {
    global $conn;
    return $conn->query("SELECT * FROM categories ORDER BY name ASC");
}

/* NHOM HAM BANNER */
function lay_banner() {
    global $conn;
    return $conn->query("SELECT * FROM banners ORDER BY uploaded_at DESC");
}

/* NHOM HAM SAN PHAM */
function lay_tong_san_pham($cate = 0) {
    global $conn;

    if ($cate > 0) {
        $sql = "SELECT COUNT(*) AS total FROM products WHERE category_id = $cate";
    } else {
        $sql = "SELECT COUNT(*) AS total FROM products";
    }

    $result = $conn->query($sql)->fetch_assoc();
    return intval($result['total']);
}

function lay_san_pham_phan_trang($cate, $start, $limit) {
    global $conn;

    $sql = "
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
    ";

    if ($cate > 0) {
        $sql .= " WHERE p.category_id = $cate";
    }

    $sql .= " ORDER BY p.id DESC LIMIT $start, $limit";
    return $conn->query($sql);
}

function lay_san_pham_theo_id($id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function lay_anh_phu($product_id) {
    global $conn;
    return $conn->query("SELECT image FROM product_images WHERE product_id = $product_id");
}

/* NHOM HAM TIM KIEM */
function tim_kiem_san_pham($q, $cate = 0) {
    global $conn;

    $sql = "
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.name LIKE ?
    ";

    $params = ["%$q%"];
    $types = "s";

    if ($cate > 0) {
        $sql .= " AND p.category_id = ?";
        $params[] = $cate;
        $types .= "i";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result();
}

/* NHOM HAM DANH GIA */
function lay_danh_gia($product_id) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT r.*, u.fullname
        FROM product_reviews r
        JOIN users u ON u.id = r.user_id
        WHERE r.product_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    return $stmt->get_result();
}

function lay_tb_sao($product_id) {
    global $conn;

    $q = $conn->query("
        SELECT 
            COALESCE(AVG(rating), 0) AS avg_star,
            COALESCE(COUNT(*), 0) AS total_reviews
        FROM product_reviews
        WHERE product_id = $product_id
    ");

    return $q->fetch_assoc();
}
// THÊM HÀM KIỂM TRA ĐÃ ĐÁNH GIÁ CHƯA
function da_danh_gia_chua($user_id, $product_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, rating, comment FROM product_reviews WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0 ? $result->fetch_assoc() : false;
}
function luu_danh_gia($user_id, $product_id, $rating, $comment) {
    global $conn;
    $review_exists = da_danh_gia_chua($user_id, $product_id);
    if ($review_exists) {
        // Đã đánh giá → UPDATE (không dùng updated_at vì không có cột này)
        $stmt = $conn->prepare("
            UPDATE product_reviews 
            SET rating = ?, comment = ?
            WHERE user_id = ? AND product_id = ?
        ");
        $stmt->bind_param("isii", $rating, $comment, $user_id, $product_id);
        return $stmt->execute();
    } else {
        // Chưa đánh giá → INSERT mới
        $stmt = $conn->prepare("
            INSERT INTO product_reviews (user_id, product_id, rating, comment)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiis", $user_id, $product_id, $rating, $comment);
        return $stmt->execute();
    }
}

function xu_ly_submit_danh_gia_tai_chi_tiet() {
    if (!isset($_SESSION['user_id'])) {
        chuyen_trang('/user/login.php');
    }

    $user_id    = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id'] ?? 0);
    $rating     = intval($_POST['rating'] ?? 0);
    $comment    = trim($_POST['comment'] ?? '');

    if ($product_id <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
        chuyen_trang('/user/product_details.php?id=' . $product_id);
    }

    luu_danh_gia($user_id, $product_id, $rating, $comment);
    chuyen_trang('/user/product_details.php?id=' . $product_id);
}

/* NHOM HAM GIO HANG */
function kiem_tra_ton_kho($product_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    return ($r['stock_quantity'] ?? 0) > 0;
}

function lay_so_luong_ton($product_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['stock_quantity'] ?? 0;
}

function them_vao_gio($id, $qty = 1)
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Nếu sản phẩm đã tồn tại trong giỏ → tăng số lượng
    if (isset($_SESSION['cart'][$id])) {

        // Nếu là kiểu cũ (chỉ số), chuyển thành dạng mảng
        if (!is_array($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] = ['qty' => intval($_SESSION['cart'][$id])];
        }

        $_SESSION['cart'][$id]['qty'] += $qty;
    } 
    else {
        // Thêm sản phẩm mới
        $_SESSION['cart'][$id] = ['qty' => $qty];
    }
}


function xoa_khoi_gio($product_id) {
    unset($_SESSION['cart'][$product_id]);
}

function them_gio_hang_db($user_id, $product_id, $qty) {
    global $conn;

    $stmt = $conn->prepare("SELECT quantity FROM user_carts WHERE user_id=? AND product_id=?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $r = $stmt->get_result();

    if ($r->num_rows > 0) {
        $row = $r->fetch_assoc();
        $new_qty = $row['quantity'] + $qty;

        $up = $conn->prepare("UPDATE user_carts SET quantity=? WHERE user_id=? AND product_id=?");
        $up->bind_param("iii", $new_qty, $user_id, $product_id);
        return $up->execute();
    }

    $ins = $conn->prepare("INSERT INTO user_carts (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $ins->bind_param("iii", $user_id, $product_id, $qty);
    return $ins->execute();
}

function cap_nhat_gio_hang_db($user_id, $product_id, $qty) {
    global $conn;
    $stmt = $conn->prepare("UPDATE user_carts SET quantity=? WHERE user_id=? AND product_id=?");
    $stmt->bind_param("iii", $qty, $user_id, $product_id);
    return $stmt->execute();
}

function xoa_gio_hang_db($user_id, $product_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM user_carts WHERE user_id=? AND product_id=?");
    $stmt->bind_param("ii", $user_id, $product_id);
    return $stmt->execute();
}

/* NHOM HAM PHAN TRANG */
function phan_trang_tinh_trang($page, $total_pages) {
    $startPage = max(1, $page - 2);
    $endPage   = min($total_pages, $page + 2);

    if ($page >= $total_pages - 2) {
        $startPage = max(1, $total_pages - 4);
        $endPage   = $total_pages;
    }

    if ($page <= 3) {
        $startPage = 1;
        $endPage = min(5, $total_pages);
    }

    return [$startPage, $endPage];
}

/* NHOM HAM DONG BO GIO HANG KHI LOGIN */
function dong_bo_gio_hang($user_id) {
    global $conn;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Lấy giỏ hàng từ DB
    $db_cart = [];
    $stmt = $conn->prepare("SELECT product_id, quantity FROM user_carts WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $db_cart[$row['product_id']] = $row['quantity'];
    }
    $stmt->close();

    // Gộp SESSION CART + DB CART
    foreach ($db_cart as $pid => $qty) {
        if (!isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid] = ['qty' => $qty];
        } else {
            $_SESSION['cart'][$pid]['qty'] += $qty;
        }
    }
    // Xóa cart cũ trong DB
    $del = $conn->prepare("DELETE FROM user_carts WHERE user_id = ?");
    $del->bind_param("i", $user_id);
    $del->execute();
    $del->close();

    // Insert lại cart mới vào DB
    $insert = $conn->prepare("INSERT INTO user_carts (user_id, product_id, quantity) VALUES (?,?,?)");
    foreach ($_SESSION['cart'] as $pid => $item) {
        $insert->bind_param("iii", $user_id, $pid, $item['qty']);
        $insert->execute();
    }
    $insert->close();
}
function lay_san_pham_moi_nhat($limit = 4) {
    global $conn;
    $sql = "SELECT p.*, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.id DESC
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result();
}

?>
