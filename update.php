<?php
$pdo = new PDO('mysql:host=localhost;dbname=izakaya_db;charset=utf8', 'root', '');

$id          = $_POST['id'];
$name        = $_POST['name'];
$date        = $_POST['date'];
$time        = $_POST['time'];
$people      = $_POST['people'];
$seat_number = $_POST['seat_number'];
$course      = $_POST['course'];
$phone       = $_POST['phone'];
$memo        = $_POST['memo'];

$sql = "UPDATE reservations 
        SET name=?, date=?, time=?, people=?, seat_number=?, course=?, phone=?, memo=?
        WHERE id=?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$name, $date, $time, $people, $seat_number, $course, $phone, $memo, $id]);

header("Location: list.php");
exit;
?>
