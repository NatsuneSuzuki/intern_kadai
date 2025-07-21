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

    <!-- 前月・来月へのリンク -->
    <?php
    $prev_month = $month - 1;
    $prev_year = $year;
    if ($prev_month < 1) {
        $prev_month = 12;
        $prev_year--;
    }

    $next_month = $month + 1;
    $next_year = $year;
    if ($next_month > 12) {
        $next_month = 1;
        $next_year++;
    }
    ?>
    <!--前月・来月への移動ボタン-->
    <div class="button-container">
        <a class="angled_button_left" href="<?php echo htmlspecialchars(Uri::create('gamepaylog') . '?year=' . $prev_year . '&month=' . $prev_month, ENT_QUOTES, 'UTF-8'); ?>">
            <?= htmlspecialchars($prev_month, ENT_QUOTES, 'UTF-8') ?>月
        </a>
        <a class="angled_button_right" href="<?php echo htmlspecialchars(Uri::create('gamepaylog') . '?year=' . $next_year . '&month=' . $next_month, ENT_QUOTES, 'UTF-8'); ?>">
            <?= htmlspecialchars($next_month, ENT_QUOTES, 'UTF-8') ?>月
        </a>
    </div>

    <!--目標課金額の設定-->
    <div class="box_center box_border">
        <div class="title  box_left">
            <p class="number_text"><?php echo htmlspecialchars($month, ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="nomal_text_small">月の目標課金額を設定しよう！</p>
        </div>

        <form action="<?php echo Uri::create('gamepaylog/setgoal'); ?>" method="post">
            <div class="box_left">
                <label>
                    <input type="number" name="goal_amount" min="0" required>
                    <p class="nomal_text_small">円</p>
                </label>
                <input type="hidden" name="year" value="<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="month" value="<?php echo htmlspecialchars($month, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="box_right">
                <button type="submit">設定</button>
            </div>
        </form>

    </div>


</body>

</html>