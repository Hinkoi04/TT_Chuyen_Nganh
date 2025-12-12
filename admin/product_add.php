<?php
require_once 'auth_check.php';
require_once '../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

$message = "";

/* LẤY DANH MỤC */
$stmtCat = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $category_id = intval($_POST['category_id']);

    $cpu = $_POST['cpu'] ?: null;
    $ram = $_POST['ram'] ?: null;
    $storage = $_POST['storage'] ?: null;
    $gpu = $_POST['gpu'] ?: null;
    $screen = $_POST['screen'] ?: null;

    $image_name = "";
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $image_name = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image_name);
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO products 
            (category_id, name, description, price, image, stock_quantity, cpu, ram, storage, gpu, screen)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $category_id, $name, $description, $price, $image_name,
            $stock_quantity, $cpu, $ram, $storage, $gpu, $screen
        ]);

        $product_id = $pdo->lastInsertId();

        if (!empty($_FILES['images']['name'][0])) {

            $detail_dir = "../uploads/products/";
            if (!is_dir($detail_dir)) mkdir($detail_dir, 0777, true);

            $stmtImg = $pdo->prepare("
                INSERT INTO product_images (product_id, image)
                VALUES (?, ?)
            ");

            foreach ($_FILES['images']['name'] as $i => $img) {
                $tmp = $_FILES['images']['tmp_name'][$i];
                $newName = time() . "_" . $img;

                move_uploaded_file($tmp, $detail_dir . $newName);
                $stmtImg->execute([$product_id, $newName]);
            }
        }

        $pdo->commit();
        header("Location: products.php?msg=added");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "<div class='alert alert-danger mt-3'>❌ Lỗi thêm sản phẩm</div>";
    }
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
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
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

        <div class="form-group">
            <label>Ảnh đại diện</label>
            <input type="file" name="image" class="form-control-file" required>
        </div>

        <div class="form-group">
            <label>Ảnh chi tiết</label>
            <input type="file" name="images[]" class="form-control-file" multiple>
        </div>

        <hr>
        <h5>⚙️ Thông số kỹ thuật</h5>

        <input type="text" name="cpu" class="form-control mb-2" placeholder="CPU">
        <input type="text" name="ram" class="form-control mb-2" placeholder="RAM">
        <input type="text" name="storage" class="form-control mb-2" placeholder="Storage">
        <input type="text" name="gpu" class="form-control mb-2" placeholder="GPU">
        <input type="text" name="screen" class="form-control mb-3" placeholder="Màn hình">

        <button type="submit" class="btn btn-success">Thêm sản phẩm</button>
        <a href="products.php" class="btn btn-secondary">Quay lại</a>
    </form>

</div>
</body>
</html>
