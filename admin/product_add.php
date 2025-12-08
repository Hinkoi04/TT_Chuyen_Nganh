<?php
require_once 'auth_check.php';
require_once '../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";

// Lấy danh mục
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $category_id = intval($_POST['category_id']);

    // Thông số kỹ thuật
    $cpu = $_POST['cpu'] ?: null;
    $ram = $_POST['ram'] ?: null;
    $storage = $_POST['storage'] ?: null;
    $gpu = $_POST['gpu'] ?: null;
    $screen = $_POST['screen'] ?: null;

    // ---------------------------
    // ẢNH ĐẠI DIỆN (thumbnail)
    // ---------------------------
    $image_name = "";
    if (!empty($_FILES['image']['name'])) {

        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $image_name = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image_name);
    }

    // Insert sản phẩm
    $stmt = $conn->prepare("
        INSERT INTO products 
        (category_id, name, description, price, image, stock_quantity, cpu, ram, storage, gpu, screen)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "issdsisssss",
        $category_id, $name, $description, $price, $image_name,
        $stock_quantity, $cpu, $ram, $storage, $gpu, $screen
    );

    if ($stmt->execute()) {

        $product_id = $stmt->insert_id;

        // ---------------------------
        // ẢNH CHI TIẾT (nhiều ảnh)
        // ---------------------------
        if (!empty($_FILES['images']['name'][0])) {

            $detail_dir = "../uploads/products/";
            if (!is_dir($detail_dir)) mkdir($detail_dir, 0777, true);

            foreach ($_FILES['images']['name'] as $i => $img) {

                $tmp = $_FILES['images']['tmp_name'][$i];
                $newName = time() . "_" . $img;

                move_uploaded_file($tmp, $detail_dir . $newName);

                $stmt_img = $conn->prepare("INSERT INTO product_images (product_id, image) VALUES (?, ?)");
                $stmt_img->bind_param("is", $product_id, $newName);
                $stmt_img->execute();
            }
        }

        $message = "<div class='alert alert-success mt-3'>✅ Thêm sản phẩm thành công!</div>";
        header("Location: products.php?msg=added");
    exit();
    } else {
        $message = "<div class='alert alert-danger mt-3'>❌ Lỗi: {$stmt->error}</div>";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
<div class="container mt-4">

    <h2 class="mb-4">🛒 Thêm sản phẩm mới</h2>
    <?= $message ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">

        <div class="form-group">
            <label>Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Danh mục</label>
            <select name="category_id" class="form-control" required>
                <option value="">-- Chọn danh mục --</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Mô tả</label>
            <textarea name="description" class="form-control" rows="3" required></textarea>
        </div>

        <div class="form-group">
            <label>Giá</label>
            <input type="number" name="price" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Số lượng tồn kho</label>
            <input type="number" name="stock_quantity" class="form-control" required>
        </div>

        <!-- ẢNH ĐẠI DIỆN -->
        <div class="form-group">
            <label>Ảnh đại diện (thumbnail)</label>
            <input type="file" name="image" class="form-control-file" required>
        </div>

        <!-- ẢNH CHI TIẾT -->
        <div class="form-group">
            <label>Ảnh chi tiết (có thể chọn nhiều ảnh)</label>
            <input type="file" name="images[]" class="form-control-file" multiple>
        </div>

        <hr>
        <h5>⚙️ Thông số kỹ thuật</h5>

        <div class="form-group"><label>CPU</label><input type="text" name="cpu" class="form-control"></div>
        <div class="form-group"><label>RAM</label><input type="text" name="ram" class="form-control"></div>
        <div class="form-group"><label>Storage</label><input type="text" name="storage" class="form-control"></div>
        <div class="form-group"><label>GPU</label><input type="text" name="gpu" class="form-control"></div>
        <div class="form-group"><label>Màn hình</label><input type="text" name="screen" class="form-control"></div>

        <button type="submit" class="btn btn-success">Thêm sản phẩm</button>
        <a href="products.php" class="btn btn-secondary">Quay lại</a>
    </form>

</div>
</body>
</html>
