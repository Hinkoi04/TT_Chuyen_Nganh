<?php
/* Định nghĩa đường dẫn gốc */
if (!defined('BASE_URL')) {
    define('BASE_URL', '/TT_Chuyen_Nganh');
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "laptop_store";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

/* Khởi động session nếu chưa có */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
