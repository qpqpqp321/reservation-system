<?php
$pdo = new PDO('mysql:host=localhost;dbname=izakaya_db;charset=utf8', 'root', '');

$stmt = $pdo->prepare("INSERT INTO reservations (name, date, time, course, people, seat_type, seat_number, phone, memo)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->execute([
    $_POST['name'],
    $_POST['date'],
    $_POST['time'],
    $_POST['course'],
    $_POST['people'],
    $_POST['seat_type'],
    $_POST['seat_number'],
    $_POST['phone'],
    $_POST['memo']
]);

// ★ 予約登録後にメニューへ戻る
header("Location: menu.php");
exit;
