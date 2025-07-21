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

    <!-- 課金データなしの時 -->
    <?php if ($no_payments): ?>
        <div class="box_center box_border">
            <div class="monthly_goal">
                <p class="number_text"><?php echo $month; ?></p>
                <p class="nomal_text">月の目標額　</p>
                <p class="number_text_big"><?php echo $monthly_payment['goal_amount']; ?></p>
                <p class="nomal_text">円</p>
            </div>
            <p class="no_payments message">今月はまだ課金してないよ！</p>
            <a class="button" href="<?php echo Uri::create("gamepaylog/create?year={$year}&month={$month}"); ?>">＋ 追加</a>
        </div>
    <?php endif; ?>

    <div class="result_view">
        <div>
            <!-- 目標額表示 -->
            <div class="monthly_goal">
                <?php if ($no_payments): ?>
                <?php else: ?>
                    <p class="number_text"><?php echo $month; ?></p>
                    <p class="nomal_text">月の目標額　</p>
                    <p class="number_text_big"><?php echo $monthly_payment['goal_amount']; ?></p>
                    <p class="nomal_text">円</p>
                <?php endif; ?>
            </div>


            <!-- 円グラフ表示 -->
            <?php if (!$no_payments): ?>
                <div style="width: 400px; height: 400px;">
                    <canvas id="paymentChart"></canvas>
                </div>
            <?php endif; ?>
        </div>


        <div>
            <div class="box_1">
                <!-- メッセージ表示 -->
                <div>
                    <?php if ($over_goal): ?>
                        <div class="caution message">
                            <?= Asset::img('sentiment_very_dissatisfied_57dp_FC8D8D.png') ?>
                            <p>目標額を超過しています！</p>
                        </div>
                    <?php else: ?>
                        <div class="none message"></div>
                    <?php endif; ?>
                </div>

                <!-- 新規追加ボタン -->
                <?php if ($no_payments): ?>
                <?php else: ?>
                    <a class="button" href="<?php echo Uri::create("gamepaylog/create?year={$year}&month={$month}"); ?>">＋ 追加</a>
                <?php endif; ?>

            </div>

            <!-- 一覧表示 -->
            <?php if (!$no_payments): ?>

                <table>
                    <colgroup>
                        <col style="width: 20%;">
                        <col style="width: 40%;">
                        <col style="width: 20%;">
                        <col style="width: 20%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>日付</th>
                            <th>タイトル</th>
                            <th>金額</th>
                            <th>　</th>
                        </tr>
                    </thead>
                    <tbody data-bind="foreach: payments">
                        <tr>
                            <td data-bind="text: payment_date"></td>
                            <td data-bind="text: name"></td>
                            <td data-bind="text: '¥' + amount()"></td>
                            <td>
                                <a data-bind="attr: { href: '<?= Uri::create("gamepaylog/edit/") ?>' + id }"><?= Asset::img('_i_icon_11095_icon_110950_16.png') ?></a> |
                                <a data-bind="attr: { href: '<?= Uri::create("gamepaylog/delete/") ?>' + id + '?year=<?= htmlspecialchars($year, ENT_QUOTES, 'UTF-8') ?>&month=<?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?>' }"><?= Asset::img('_i_icon_11988_icon_119880_16.png') ?></a>
                            </td>

                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- 合計金額表示 -->
            <?php if (!$no_payments): ?>
                <div class="monthly_result">
                    <p class="number_text"><?php echo $month; ?></p>
                    <p class="nomal_text">月の合計　</p>
                    <p class="number_text_big">
                        <?php echo $monthly_payment['total_amount']; ?>
                    </p>
                    <p class="nomal_text">円</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script>
        const dataFromPHP = <?php echo html_entity_decode($json_data); ?>;

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const yyyy = date.getFullYear();
            const mm = ('0' + (date.getMonth() + 1)).slice(-2);
            const dd = ('0' + date.getDate()).slice(-2);
            return `${yyyy}/${mm}/${dd}`;
        }

        function Payment(data) {
            this.id = data.id;
            this.name = ko.observable(data.name);
            this.payment_date = ko.observable(formatDate(data.payment_date));
            this.amount = ko.observable(data.amount);
        }


        function ViewModel(paymentsData) {
            const self = this;
            self.payments = ko.observableArray(paymentsData.map(d => new Payment(d)));
        }

        ko.applyBindings(new ViewModel(dataFromPHP));

        //円グラフ生成
        const gameSums = <?= html_entity_decode($json_game_sums, ENT_QUOTES, 'UTF-8') ?>;

        const ctx = document.getElementById('paymentChart').getContext('2d');
        const labels = gameSums.map(g => g.name);
        const data = gameSums.map(g => Number(g.total));

        const chart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        '#B8ECE3', '#F7D6D6', '#ffffbc', '#bcddff', '#bcbcff', '#ffdbb7'
                    ],
                }]
            },
            options: {
                responsive: true,
            }
        });
    </script>

</body>

</html>