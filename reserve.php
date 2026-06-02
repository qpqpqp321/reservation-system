<?php
$pdo = new PDO('mysql:host=localhost;dbname=izakaya_db;charset=utf8', 'root', '');

// ▼ 選択された日付（なければ今日）
$date = $_GET['date'] ?? date('Y-m-d');

// ▼ 選択された日の予約席を取得
$stmt = $pdo->prepare("SELECT seat_number FROM reservations WHERE date = ?");
$stmt->execute([$date]);
$reservedSeats = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
    <title>予約フォーム</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1 class="title">予約フォーム</h1>

<form action="save.php" method="post" class="form-box">

    <!-- ① 名前 -->
    <label>名前：</label>
    <input type="text" name="name" required>

    <!-- ② 日付（変更したら自動で再読み込み） -->
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
            echo "<option value='$time'>$time</option>";
        }
        ?>
    </select>

    <!-- ④ コース -->
    <label>コース：</label>
    <select name="course" required>
        <option value="seat_only">席のみ</option>
        <option value="4400">4400円コース</option>
        <option value="5500">5500円コース</option>
        <option value="6600">6600円コース</option>
    </select>

    <!-- ⑤ 人数 -->
    <label>人数：</label>
    <input type="number" name="people" id="people" min="1" required>

    <!-- ⑥ 席タイプ -->
    <label>席タイプ：</label>
    <select name="seat_type" required>
        <option value="counter">カウンター</option>
        <option value="table">テーブル</option>
    </select>

    <!-- ⑦ 座席番号 -->
    <label>座席番号（自動入力）：</label>
    <input type="text" name="seat_number" id="seat_number" readonly required>

    <h3>座席を選択してください</h3>

    <div class="seats">
        <h4>カウンター（C1〜C9）</h4>
        <?php for ($i = 1; $i <= 9; $i++):
            $seat = "C$i";
            $disabled = in_array($seat, $reservedSeats);
        ?>
            <button 
                type="button"
                class="seat-btn <?= $disabled ? 'disabled' : '' ?>"
                data-seat="<?= $seat ?>"
                onclick="<?= $disabled ? '' : "selectCounter('$seat')" ?>"
                <?= $disabled ? 'disabled' : '' ?>
            >
                <?= $seat ?> <?= $disabled ? '(満席)' : '' ?>
            </button>
        <?php endfor; ?>

        <h4>テーブル（T1〜T19）</h4>
        <?php for ($i = 1; $i <= 19; $i++):
            $seat = "T$i";
            $disabled = in_array($seat, $reservedSeats);
        ?>
            <button 
                type="button"
                class="seat-btn <?= $disabled ? 'disabled' : '' ?>"
                data-seat="<?= $seat ?>"
                onclick="<?= $disabled ? '' : "toggleTable('$seat')" ?>"
                <?= $disabled ? 'disabled' : '' ?>
            >
                <?= $seat ?> <?= $disabled ? '(満席)' : '' ?>
            </button>
        <?php endfor; ?>
    </div>

<script>
let selectedTables = [];

// ▼ 日付変更 → ページ再読み込み
function changeDate() {
    const date = document.getElementById('date').value;
    if (date) {
        location.href = "reserve.php?date=" + date;
    }
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
    if (!people) {
        alert("先に人数を入力してください");
        return;
    }

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
            alert(`この人数だと最大 ${maxSeats} 席まで選べます`);
            return;
        }
        selectedTables.push(seat);
    }

    document.getElementById('seat_number').value = selectedTables.join(',');
    updateSeatHighlight();
}
</script>

    <!-- ⑧ 電話番号 -->
    <label>電話番号：</label>
    <input type="tel" name="phone" required placeholder="090-1234-5678">

    <!-- ⑨ メモ -->
    <label>メモ：</label>
    <textarea name="memo"></textarea>

    <div class="form-buttons">
        <button type="submit" class="action-btn">予約を登録</button>
        <a href="menu.php" class="action-btn">メニューに戻る</a>
    </div>

</form>

</body>
</html>
