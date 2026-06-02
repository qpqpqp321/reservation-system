<?php
$id = $_GET['id'];

$pdo = new PDO('mysql:host=localhost;dbname=izakaya_db;charset=utf8', 'root', '');

$stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
$stmt->execute([$id]);

header("Location: list.php");
exit;
?>
