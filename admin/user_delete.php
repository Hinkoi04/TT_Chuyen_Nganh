<?php
require_once 'auth_check.php';
require_once '../includes/db.php';

if (!isset($_GET['id'])) { header("Location: users.php"); exit(); }
$id = intval($_GET['id']);
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
header("Location: users.php");
exit();
?>