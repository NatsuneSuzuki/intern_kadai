<?php
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>課金ログ</title>
    <script src="https://cdn.jsdelivr.net/npm/knockout@3.5.1/build/output/knockout-latest.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@600&display=swap" rel="stylesheet">
</head>
<body>
<header><p class="logo">課金ログ</p></header>
<p class="number_text">課金情報を追加</p>

<form action="<?php echo Uri::create('gamepaylog/store'); ?>" method="post">
    <!-- hiddenで年月送信 -->
    <input type="hidden" name="year" value="<?= $year ?>">
    <input type="hidden" name="month" value="<?= $month ?>">

    <!-- 課金日 -->
    <label>課金日：
        
    <input type="date" name="payment_date"
       value="<?= htmlspecialchars(sprintf('%04d-%02d-01', $year, $month), ENT_QUOTES, 'UTF-8') ?>">
    </label>
    
    <br>
    
    <!-- ゲーム選択 -->
    <label>ゲーム名：
        <select name="games_id">
            <?php foreach ($games as $game): ?>
                <option value="<?php echo $game['id']; ?>"><?php echo htmlspecialchars($game['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <br>

    <!-- 新規ゲーム名 -->
    <label>新しいゲーム名（まだ登録されていない場合）： 
        <input type="text" name="new_game_name" value="">
    </label>
    <br>

    

    <!-- 金額 -->
    <label>金額：
        <input type="number" name="amount" min="0" value="0">
    </label>
    <br>

    <button type="submit">登録</button>
</form>

<p><a href="<?php echo Uri::create("gamepaylog?year={$year}&month={$month}"); ?>">一覧に戻る</a></p>

<script>
// 保存対象のフォーム要素
const fields = ['payment_date', 'games_id', 'new_game_name', 'amount'];

// Cookieに保存する
function saveFormToCookie() {
    fields.forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        if (input) {
            document.cookie = `${field}=${encodeURIComponent(input.value)}; path=/; max-age=86400`; // 1日有効
        }
    });
}

// Cookieから値を取得
function getCookie(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? decodeURIComponent(match[2]) : null;
}

// フォームを復元
function loadFormFromCookie() {
    fields.forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        const value = getCookie(field);
        if (input && value !== null) {
            input.value = value;
        }
    });
}

// 入力のたびに保存
fields.forEach(field => {
    const input = document.querySelector(`[name="${field}"]`);
    if (input) {
        input.addEventListener('input', saveFormToCookie);
    }
});

// ページ読み込み時に復元
window.addEventListener('DOMContentLoaded', loadFormFromCookie);
</script>
</body>
