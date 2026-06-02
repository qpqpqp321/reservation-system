<?php
$pdo = new PDO('mysql:host=localhost;dbname=izakaya_db;charset=utf8', 'root', '');

$id = $_GET['id'];

// ▼ 編集対象の予約を取得
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
$stmt->execute([$id]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

// ▼ 選択された日付（編集対象の日付）
$date = $_GET['date'] ?? $res['date'];

// ▼ 選択された日の予約席を取得（自分の席は除外）
$stmt2 = $pdo->prepare("SELECT seat_number FROM reservations WHERE date = ? AND id != ?");
$stmt2->execute([$date, $id]);
$reservedSeats = [];

while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    $seats = explode(',', $row['seat_number']);
    foreach ($seats as $s) {
        $reservedSeats[] = trim($s);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>予約編集</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1 class="title">予約編集</h1>

<form action="update.php" method="post" class="form-box">

    <input type="hidden" name="id" value="<?= $id ?>">

    <!-- ① 名前 -->
    <label>名前：</label>
    <input type="text" name="name" value="<?= $res['name'] ?>" required>

    <!-- ② 日付（変更したら再読み込み） -->
    <label>日付：</label>
    <input type="date" name="date" id="date" value="<?= $date ?>" required onchange="changeDate()">

    <!-- ③ 時間 -->
    <label>時間：</label>
    <select name="time" required>
        <?php
        $start = strtotime("17:00");
        $end   = strtotime("24:00");

        for ($t = $start; $t <= $end; $t += 1800) {
            $time = date("H:i", $t);
            $selected = ($time == $res['time']) ? "selected" : "";
            echo "<option value='$time' $selected>$time</option>";
        }
        ?>
    </select>

    <!-- ④ コース -->
    <label>コース：</label>
    <select name="course" required>
        <option value="seat_only" <?= $res['course']=="seat_only"?"selected":"" ?>>席のみ</option>
        <option value="4400" <?= $res['course']=="4400"?"selected":"" ?>>4400円コース</option>
        <option value="5500" <?= $res['course']=="5500"?"selected":"" ?>>5500円コース</option>
        <option value="6600" <?= $res['course']=="6600"?"selected":"" ?>>6600円コース</option>
    </select>

    <!-- ⑤ 人数 -->
    <label>人数：</label>
    <input type="number" name="people" id="people" min="1" value="<?= $res['people'] ?>" required>

    <!-- ⑥ 席タイプ -->
    <label>席タイプ：</label>
    <select name="seat_type" required>
        <option value="counter" <?= $res['seat_type']=="counter"?"selected":"" ?>>カウンター</option>
        <option value="table" <?= $res['seat_type']=="table"?"selected":"" ?>>テーブル</option>
    </select>

    <!-- ⑦ 座席番号 -->
    <label>座席番号（自動入力）：</label>
    <input type="text" name="seat_number" id="seat_number" value="<?= $res['seat_number'] ?>" readonly required>

    <h3>座席を選択してください</h3>

    <div class="seats">
        <h4>カウンター（C1〜C9）</h4>
        <?php for ($i = 1; $i <= 9; $i++):
            $seat = "C$i";
            $disabled = in_array($seat, $reservedSeats);
            $active = ($seat == $res['seat_number']) ? "active" : "";
        ?>
            <button 
                type="button"
                class="seat-btn <?= $disabled ? 'disabled' : $active ?>"
                data-seat="<?= $seat ?>"
                onclick="<?= $disabled ? '' : "selectCounter('$seat')" ?>"
                <?= $disabled ? 'disabled' : '' ?>
            >
                <?= $seat ?> <?= $disabled ? '(満席)' : '' ?>
            </button>
        <?php endfor; ?>

        <h4>テーブル（T1〜T19）</h4>
        <?php 
        $currentSeats = explode(",", $res['seat_number']);
        for ($i = 1; $i <= 19; $i++):
            $seat = "T$i";
            $disabled = in_array($seat, $reservedSeats);
            $active = in_array($seat, $currentSeats) ? "active" : "";
        ?>
            <button 
                type="button"
                class="seat-btn <?= $disabled ? 'disabled' : $active ?>"
                data-seat="<?= $seat ?>"
                onclick="<?= $disabled ? '' : "toggleTable('$seat')" ?>"
                <?= $disabled ? 'disabled' : '' ?>
            >
                <?= $seat ?> <?= $disabled ? '(満席)' : '' ?>
            </button>
        <?php endfor; ?>
    </div>

<script>
let selectedTables = <?= json_encode($currentSeats) ?>;

// ▼ 日付変更 → ページ再読み込み
function changeDate() {
    const date = document.getElementById('date').value;
    location.href = "edit.php?id=<?= $id ?>&date=" + date;
}

// ▼ 座席ハイライト更新
function updateSeatHighlight() {
    document.querySelectorAll('.seat-btn').forEach(btn => btn.classList.remove('active'));
    selectedTables.forEach(seat => {
        const btn = document.querySelector(`button[data-seat="${seat}"]`);
        if (btn) btn.classList.add('active');
    });
}

// ▼ カウンター席（1席のみ）
function selectCounter(seat) {
    selectedTables = [seat];
    document.getElementById('seat_number').value = seat;
    updateSeatHighlight();
}

// ▼ テーブル席（人数に応じて複数）
function toggleTable(seat) {
    const people = Number(document.getElementById('people').value);
    const maxSeats = Math.ceil(people / 4);

    if (maxSeats === 1) {
        selectedTables = [seat];
        document.getElementById('seat_number').value = seat;
        updateSeatHighlight();
        return;
    }

    if (selectedTables.length > 0) {
        const last = selectedTables[selectedTables.length - 1];
        const lastNum = Number(last.replace("T", ""));
        const nowNum = Number(seat.replace("T", ""));
        if (Math.abs(lastNum - nowNum) !== 1) {
            alert("隣り合う席のみ選択できます");
            return;
        }
    }

    if (selectedTables.includes(seat)) {
        selectedTables = selectedTables.filter(s => s !== seat);
    } else {
        if (selectedTables.length >= maxSeats) {
            alert(`最大 ${maxSeats} 席まで選べます`);
            return;
        }
        selectedTables.push(seat);
    }

    document.getElementById('seat_number').value = selectedTables.join(',');
    updateSeatHighlight();
}

updateSeatHighlight();
</script>

    <!-- ⑧ 電話番号 -->
    <label>電話番号：</label>
    <input type="tel" name="phone" value="<?= $res['phone'] ?>" required>

    <!-- ⑨ メモ -->
    <label>メモ：</label>
    <textarea name="memo"><?= $res['memo'] ?></textarea>

    <div class="form-buttons">
        <button type="submit" class="action-btn">更新する</button>
        <a href="menu.php" class="action-btn">メニューに戻る</a>
    </div>

</form>

</body>
</html>
