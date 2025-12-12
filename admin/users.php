<?php
require_once 'auth_check.php';
require_once '../includes/db.php';

/* Lấy danh sách người dùng */
$stmt = $pdo->query("SELECT * FROM users ORDER BY id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Quản lý người dùng";

$page_content = '
<h2 class="mb-3 text-center">Danh sách người dùng</h2>
<table class="table table-bordered table-hover">
    <thead class="thead bg-primary">
        <tr>
            <th>ID</th>
            <th>Họ tên</th>
            <th>Username</th>
            <th>Email</th>
            <th>Vai trò</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
';

foreach ($users as $u) {
    $page_content .= "
        <tr>
            <td>{$u['id']}</td>
            <td>" . htmlspecialchars($u['fullname']) . "</td>
            <td>" . htmlspecialchars($u['username']) . "</td>
            <td>" . htmlspecialchars($u['email']) . "</td>
            <td>{$u['role']}</td>
            <td>
                <a href='user_edit.php?id={$u['id']}' class='btn btn-sm btn-info'>
                    <ion-icon name=\"create-outline\"></ion-icon>
                </a>
                <a href='user_delete.php?id={$u['id']}'
                   onclick='return confirm(\"Xóa người dùng này?\")'
                   class='btn btn-sm btn-danger'>
                    <ion-icon name=\"trash-outline\"></ion-icon>
                </a>
            </td>
        </tr>
    ";
}

$page_content .= "
    </tbody>
</table>
";

include "index.php";
?>
