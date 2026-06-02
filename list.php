<?php
$pdo = new PDO('mysql:host=localhost;dbname=izakaya_db;charset=utf8', 'root', '');

// ▼ 選択された日付（なければ今日）
$date = $_GET['date'] ?? date('Y-m-d');

// ▼ 選択された日の予約を取得
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE date = ? ORDER BY time ASC");
$stmt->execute([$date]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>予約一覧</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1 class="title">予約一覧</h1>

<!-- ★ メニューに戻るボタン -->
<a href="menu.php" class="back-menu-btn">← メニューに戻る</a>

<!-- ★ 日付選択フォーム（中央揃え） -->
<form method="get" class="form-box" style="text-align:center;">
    <label>日付を選択：</label>
    <input type="date" name="date" value="<?= $date ?>" required>
    <button type="submit" class="action-btn">表示</button>
</form>

<!-- ★ 不要な「前日 / 翌日」ボタンは削除済み -->

<!-- ★ 予約一覧テーブル -->
<table class="reservation-table">
    <tr>
        <th>名前</th>
        <th>時間</th>
        <th>人数</th>
        <th>席番号</th>
        <th>コース</th>
        <th>電話番号</th>
        <th>メモ</th>
        <th>操作</th>
    </tr>

    <?php if (empty($reservations)): ?>
        <tr>
            <td colspan="8">予約はありません</td>
        </tr>
    <?php else: ?>
        <?php foreach ($reservations as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['time']) ?></td>
                <td><?= htmlspecialchars($row['people']) ?></td>
                <td><?= htmlspecialchars($row['seat_number']) ?></td>

                <!-- コースタグ -->
                <td>
                    <?php
                        $course = $row['course'];
                        $label = [
                            "seat_only" => "席のみ",
                            "4400" => "4400円",
                            "5500" => "5500円",
                            "6600" => "6600円"
                        ][$course];

                        $class = "course-tag course-" . $course;
                    ?>
                    <span class="<?= $class ?>"><?= $label ?></span>
                </td>

                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['memo'])) ?></td>

                <td>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="action-btn" style="padding:6px 12px;">編集</a>
                    <a href="delete.php?id=<?= $row['id'] ?>" class="action-btn" style="padding:6px 12px; background:#a85f68;">削除</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

</body>
</html>
