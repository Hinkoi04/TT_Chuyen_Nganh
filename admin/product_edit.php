<?php
require_once 'auth_check.php';
require_once '../includes/db.php';

// lấy id
$id = $_GET['id'] ?? 0;
if (!is_numeric($id)) die('ID không hợp lệ');

// =======================
//  UPDATE sản phẩm
// =======================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name        = $_POST['ten_san_pham'];
    $description = $_POST['mo_ta'];
    $price       = $_POST['gia'];
    $stock       = $_POST['so_luong_ton'];

    $cpu     = $_POST['cpu'] ?: NULL;
    $ram     = $_POST['ram'] ?: NULL;
    $storage = $_POST['storage'] ?: NULL;
    $gpu     = $_POST['gpu'] ?: NULL;
    $screen  = $_POST['screen'] ?: NULL;

    // UPDATE ảnh đại diện
    $image_sql = "";
    $types = "ssdiissssi";
    $params = [$name, $description, $price, $stock, $cpu, $ram, $storage, $gpu, $screen, $id];

    if (!empty($_FILES['hinh_anh']['name'])) {
        $image = time() . "_" . $_FILES['hinh_anh']['name'];
        move_uploaded_file($_FILES['hinh_anh']['tmp_name'], "../uploads/" . $image);

        $image_sql = ", image=?";
        $types = "ssdissssssi";
        $params = [$name, $description, $price, $stock, $image, $cpu, $ram, $storage, $gpu, $screen, $id];
    }

    // update sản phẩm
    $sql = "UPDATE products 
            SET name=?, description=?, price=?, stock_quantity=? $image_sql,
                cpu=?, ram=?, storage=?, gpu=?, screen=?
            WHERE id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    // ===============================
    // XỬ LÝ THÊM NHIỀU ẢNH CHI TIẾT
    // ===============================
    if (!empty($_FILES['detail_images']['name'][0])) {

        $target_dir = "../uploads/products/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        foreach ($_FILES['detail_images']['name'] as $i => $img) {
            $newName = time() . "_" . $img;
            move_uploaded_file($_FILES['detail_images']['tmp_name'][$i], $target_dir . $newName);

            $insert = $conn->prepare("INSERT INTO product_images (product_id, image) VALUES (?, ?)");
            $insert->bind_param("is", $id, $newName);
            $insert->execute();
        }
    }

    if ($stmt->execute()) {
    header("Location: products.php?msg=updated");
    exit();
}

}

// =======================
//  LẤY SẢN PHẨM
// =======================
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) die("Sản phẩm không tồn tại!");

// lấy ảnh chi tiết
$images = $conn->query("SELECT * FROM product_images WHERE product_id=$id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sửa sản phẩm</title>
    <link rel="stylesheet" 
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-dark">
<div class="container border mt-3 bg-light mb-3 p-3">

    <h2 class="text-center text-white bg-primary p-2">
        Sửa sản phẩm: <?= htmlspecialchars($product['name']) ?>
    </h2>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">✔ Cập nhật thành công!</div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">

    <div class="row">

        <!-- CỘT TRÁI -->
        <div class="col-md-6 border-right">
            <h4 class="text-primary text-center">Thông tin sản phẩm</h4>

            <div class="form-group">
                <label>Tên sản phẩm</label>
                <input type="text" name="ten_san_pham" class="form-control"
                       value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Mô tả</label>
                <textarea name="mo_ta" class="form-control" rows="4" required>
                    <?= htmlspecialchars($product['description']) ?>
                </textarea>
            </div>

            <div class="form-group">
                <label>Giá</label>
                <input type="number" name="gia" class="form-control"
                       value="<?= $product['price'] ?>" required>
            </div>

            <div class="form-group">
                <label>Số lượng tồn kho</label>
                <input type="number" name="so_luong_ton" class="form-control"
                       value="<?= $product['stock_quantity'] ?>" required>
            </div>

            <div class="form-group">
                <label>Ảnh đại diện hiện tại</label><br>
                <img src="../uploads/<?= $product['image'] ?>" width="150"
                     class="rounded shadow border">
            </div>

            <div class="form-group">
                <label>Đổi ảnh đại diện</label>
                <input type="file" name="hinh_anh" class="form-control-file">
            </div>
        </div>

        <!-- CỘT PHẢI -->
        <div class="col-md-6">
            <h4 class="text-success text-center">Thông số kỹ thuật</h4>

            <div class="form-group">
                <label>CPU</label>
                <input type="text" name="cpu" class="form-control"
                       value="<?= htmlspecialchars($product['cpu'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>RAM</label>
                <input type="text" name="ram" class="form-control"
                       value="<?= htmlspecialchars($product['ram'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Storage</label>
                <input type="text" name="storage" class="form-control"
                       value="<?= htmlspecialchars($product['storage'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>GPU</label>
                <input type="text" name="gpu" class="form-control"
                       value="<?= htmlspecialchars($product['gpu'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Màn hình</label>
                <input type="text" name="screen" class="form-control"
                       value="<?= htmlspecialchars($product['screen'] ?? '') ?>">
            </div>

            <hr>

            <h4 class="text-info">Ảnh chi tiết sản phẩm</h4>

            <div class="row">
                <?php while ($img = $images->fetch_assoc()): ?>
                    <div class="col-4 text-center mb-3">
                        <img src="../uploads/products/<?= $img['image'] ?>" 
                             width="100" class="border rounded mb-1">
                        <br>
                        <a href="product_image_delete.php?id=<?= $img['id'] ?>&pid=<?= $id ?>"
                           onclick="return confirm('Xóa ảnh này?')"
                           class="btn btn-danger btn-sm">Xóa</a>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="form-group mt-3">
                <label>Thêm ảnh chi tiết (nhiều ảnh)</label>
                <input type="file" name="detail_images[]" multiple class="form-control-file">
            </div>

        </div>

    </div>

    <div class="text-center mt-4">
        <button class="btn btn-primary px-4">Cập nhật</button>
        <a href="products.php" class="btn btn-secondary px-4">Hủy</a>
    </div>

    </form>
</div>
</body>
</html>
