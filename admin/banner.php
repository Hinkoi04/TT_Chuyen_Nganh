<?php
require_once 'auth_check.php';
require_once '../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";

/* ===================== THÊM BANNER ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['banner'])) {

    $target_dir = "../uploads/banner/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $image_name = time() . "_" . basename($_FILES['banner']['name']);
    $target_file = $target_dir . $image_name;

    if (move_uploaded_file($_FILES['banner']['tmp_name'], $target_file)) {

        $stmt = $pdo->prepare("INSERT INTO banners (image) VALUES (?)");
        $stmt->execute([$image_name]);

        $message = "<div class='alert alert-success mt-3'>✅ Đã thêm banner mới!</div>";
    } else {
        $message = "<div class='alert alert-danger mt-3'>❌ Lỗi khi tải ảnh banner lên.</div>";
    }
}

/* ===================== XÓA BANNER ===================== */
if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $stmt = $pdo->prepare("SELECT image FROM banners WHERE id = ?");
    $stmt->execute([$id]);
    $filename = $stmt->fetchColumn();

    if ($filename && file_exists("../uploads/banner/" . $filename)) {
        unlink("../uploads/banner/" . $filename);
    }

    $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
    $stmt->execute([$id]);

    $message = "<div class='alert alert-info mt-3'>🗑️ Đã xóa banner.</div>";
}

/* ===================== DANH SÁCH BANNER ===================== */
$stmt = $pdo->query("SELECT * FROM banners ORDER BY uploaded_at DESC");
$banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Quản lý Banner";
$page_content = '
    <h2 class="mb-4 text-center">Quản lý Banner Trang Chủ</h2>
    ' . $message . '

    <form method="POST" enctype="multipart/form-data"
          class="m-auto bg-white p-3 rounded shadow-lg mb-4 col-md-6">

        <div class="form-group">
            <label class="font-weight-bold">Chọn ảnh banner (JPEG/PNG, tỉ lệ 16:9)</label>
            <input type="file" id="bannerInput" name="banner"
                   accept="image/*" class="form-control-file" required>
        </div>

        <div class="text-center mb-3">
            <img id="previewBanner" src=""
                 style="display:none; width:100%; max-height:250px;
                        object-fit:cover; border-radius:10px; border:1px solid #ddd;">
        </div>

        <button type="submit" class="btn btn-success">
            <ion-icon name="add-circle-outline"></ion-icon> Thêm banner
        </button>
    </form>

    <h3 class="mt-5 text-center">Danh sách banner</h3>
    <div class="row mt-4">
';

if ($banners) {
    foreach ($banners as $b) {

        $img  = htmlspecialchars($b['image']);
        $date = $b['uploaded_at'];
        $id   = $b['id'];

        $page_content .= '
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm" style="height:350px;">

                    <div class="container" style="height:80%;">
                        <img src="../uploads/banner/' . $img . '"
                             class="card-img mt-3"
                             style="object-fit:cover; height:100%;">
                    </div>

                    <div class="card-body text-center">
                        <small class="text-muted">Ngày thêm: ' . $date . '</small><br>

                        <a href="?delete=' . $id . '"
                           class="btn btn-danger btn-sm mt-2"
                           onclick="return confirm(\'Bạn có chắc muốn xóa banner này không?\')">
                           <ion-icon name="trash-outline"></ion-icon> Xóa
                        </a>
                    </div>
                </div>
            </div>
        ';
    }
} else {
    $page_content .= '
        <div class="col-12 text-center text-muted">
            Chưa có banner nào.
        </div>
    ';
}

$page_content .= '</div>';

include "index.php";
?>

<script>
document.getElementById('bannerInput').addEventListener('change', function (event) {
    const file = event.target.files[0];
    const preview = document.getElementById('previewBanner');

    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = "block";
    } else {
        preview.src = "";
        preview.style.display = "none";
    }
});
</script>
