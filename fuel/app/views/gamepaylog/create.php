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
    <header>
        <p class="logo">課金ログ</p>
    </header>

    <!--  一覧に戻るボタン -->
    <div class="button-container">
        <a class="angled_button_left" href="<?= htmlspecialchars(Uri::create("gamepaylog?year={$year}&month={$month}"), ENT_QUOTES, 'UTF-8'); ?>">
            一覧に戻る
        </a>
    </div>

    <div class="box_center">
        <div class="box_left">
            <p class="number_text">課金情報を追加</p>
        </div>
        <div class="box_left">
            <form action="<?php echo Uri::create('gamepaylog/store'); ?>" method="post">
                <!-- hiddenで年月送信 -->
                <input type="hidden" name="year" value="<?= htmlspecialchars($year, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="month" value="<?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?>">

                <!-- 日付 -->
                <label>
                    <p class="label_text">日付</p>

                    <input type="date" name="payment_date"
                        value="<?= htmlspecialchars(sprintf('%04d-%02d-01', $year, $month), ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <br>

                <!-- ゲームの選択 -->
                <label>
                    <p class="label_text">タイトル</p>
                    <select name="games_id">
                        <?php foreach ($games as $game): ?>
                            <option value="<?php echo htmlspecialchars($game['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <br>

                <!-- 新規ゲームを追加 -->
                <label class="new_game">
                    <p class="nomal_text_small">新しいゲームを登録</p>
                    <input type="text" name="new_game_name" value="">
                </label>
                <br>

                <!-- 金額 -->
                <label>
                    <p class="label_text">金額</p>
                    <input type="number" name="amount" min="0" value="0">
                </label>
                <br>

                <!-- 保存ボタン -->
                <div class="box_right">
                    <button type="submit">保存</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const fields = ['payment_date', 'games_id', 'new_game_name', 'amount'];

        //Cookieに保存(有効期限24時間)
        function saveFormToCookie() {
            fields.forEach(field => {
                const input = document.querySelector(`[name="${field}"]`);
                if (input) {
                    document.cookie = `${field}=${encodeURIComponent(input.value)}; path=/; max-age=86400`;
                }
            });
        }

        //Cookieから値を取得
        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? decodeURIComponent(match[2]) : null;
        }

        //フォーム復元
        function loadFormFromCookie() {
            fields.forEach(field => {
                const input = document.querySelector(`[name="${field}"]`);
                const value = getCookie(field);
                if (input && value !== null) {
                    input.value = value;
                }
            });
        }

        //入力のたびに保存
        fields.forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.addEventListener('input', saveFormToCookie);
            }
        });

        //ページ読み込み時に復元
        window.addEventListener('DOMContentLoaded', loadFormFromCookie);
    </script>
</body>

</html>