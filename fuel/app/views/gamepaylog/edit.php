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
<p class="number_text">課金情報を修正</p>

<?php if (isset($errors)): ?>
<ul style="color:red;">
    <?php foreach ($errors as $field => $error): ?>
        <li><?php echo $field . ': ' . $error->get_message(); ?></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

<form action="<?php echo Uri::create('gamepaylog/update/'.$payment['id']); ?>" method="post">
    <!-- hiddenで年月送信 -->
    <input type="hidden" name="year" value="<?= htmlspecialchars($year, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="month" value="<?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?>">

    <label class="nomal_text_small">課金日：
        <input type="date" name="payment_date" value="<?php echo $payment['payment_date']; ?>">
    </label><br>

    <label class="nomal_text_small">ゲーム名：
        <select name="games_id">
            <?php foreach ($games as $game): ?>
                <option value="<?php echo $game['id']; ?>"
                    <?php echo ($game['id'] == $payment['games_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($game['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br>

    <label class="nomal_text_small">金額：
        <input type="number" name="amount" min="0" value="<?php echo $payment['amount']; ?>">
    </label><br>

    <button type="submit">更新</button>
</form>

<p><a href="<?php echo Uri::create("gamepaylog?year={$year}&month={$month}"); ?>">一覧に戻る</a></p>

