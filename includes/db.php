<?php
$dsn = "mysql:host=localhost;dbname=laptop_store;charset=utf8";
$user = "root";
$pass = "";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("<p style='color:red'>Lỗi kết nối CSDL! </p>" . $e->getMessage());
}

?>