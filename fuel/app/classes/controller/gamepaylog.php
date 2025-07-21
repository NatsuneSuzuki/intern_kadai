<?php

class Controller_Gamepaylog extends Controller
{
    protected $year;
    protected $month;

    public function before()
    {
        parent::before();
        //タイムゾーンをセット
        date_default_timezone_set(Config::get('default_timezone', 'UTC'));

        //指定された年月を取得(指定がなければ今年、今月を取得)
        $this->year = (int) \Input::get('year', date('Y'));
        $this->month = (int) \Input::get('month', date('n'));

        //月末、月初の補正
        if ($this->month < 1) {
            $this->month = 12;
            $this->year--;
        } elseif ($this->month > 12) {
            $this->month = 1;
            $this->year++;
        }
    }




    public function action_index()
    {
        $year = $this->year;
        $month = $this->month;

        //月末、月初の補正
        if ($month < 1) {
            $month = 12;
            $year--;
        } elseif ($month > 12) {
            $month = 1;
            $year++;
        }

        $monthly_payment = \DB::select('mp.*', 'mg.goal_amount')
            ->from(['monthly_payment', 'mp'])
            ->join(['monthly_goals', 'mg'], 'INNER')
            ->on('mp.goals_id', '=', 'mg.id')
            ->where('mp.year', $year)
            ->where('mp.month', $month)
            ->execute()
            ->current();

        if (empty($monthly_payment)) {
            \Response::redirect("gamepaylog/setgoal?year={$year}&month={$month}");
        }

        $payments = \DB::select('payments.id', 'games.name', 'payments.payment_date', 'payments.amount')
            ->from('payments')
            ->join('games', 'LEFT')
            ->on('payments.games_id', '=', 'games.id')
            ->where(\DB::expr('YEAR(payment_date)'), '=', $year)
            ->and_where(\DB::expr('MONTH(payment_date)'), '=', $month)
            ->execute()
            ->as_array();

        $json_data = json_encode($payments, JSON_UNESCAPED_UNICODE);

        $game_sums = \DB::select('games.name', \DB::expr('SUM(payments.amount) as total'))
            ->from('payments')
            ->join('games', 'LEFT')
            ->on('payments.games_id', '=', 'games.id')
            ->where(\DB::expr('YEAR(payment_date)'), '=', $year)
            ->and_where(\DB::expr('MONTH(payment_date)'), '=', $month)
            ->group_by('games.name')
            ->execute()
            ->as_array();

        $json_game_sums = json_encode($game_sums, JSON_UNESCAPED_UNICODE);

        return View::forge('gamepaylog/index', [
            'year' => $year,
            'month' => $month,
            'monthly_payment' => $monthly_payment,
            'payments' => $payments,
            'json_data' => $json_data,
            'json_game_sums' => $json_game_sums,
            'no_payments' => empty($payments),
            'over_goal' => $monthly_payment['total_amount'] > $monthly_payment['goal_amount'],
        ]);
    }


    //課金データの追加
    public function action_create()
    {
        //ゲームのリストを取得（select）
        $games = \DB::select('id', 'name')->from('games')->execute()->as_array();

        return View::forge('gamepaylog/create', [
            'games' => $games,
            'year' => $this->year,
            'month' => $this->month,
        ]);
    }

    //登録処理
    public function action_store()
    {
        if (\Input::method() === 'POST') {
            $games_id = \Input::post('games_id');
            $new_game_name = trim(\Input::post('new_game_name'));

            $val = \Validation::forge();
            $val->add_field('games_id', 'Game', 'required|match_pattern[/^\d+$/]');
            $val->add_field('new_game_name', 'New Game Name', 'max_length[255]');
            $val->add_field('payment_date', 'Payment Date', 'required|valid_date');
            $val->add_field('amount', 'Amount', 'required|valid_string[numeric]');
            $val->add_field('year', 'Year', 'required|match_pattern[/^\d{4}$/]');
            $val->add_field('month', 'Month', 'required|match_pattern[/^\d{1,2}$/]');


            if (!$val->run()) {
                \Session::set_flash('error', '入力に誤りがあります');
                \Response::redirect('gamepaylog/create');
            }

            //新規ゲームの追加
            if ($new_game_name !== '') {
                $exists = \DB::select()->from('games')->where('name', $new_game_name)->execute()->count();
                if ($exists > 0) {
                    //重複時は登録しない
                    \Session::set_flash('error', '新しいタイトルが既に存在します。');
                    \Response::redirect('gamepaylog/create');
                } else {
                    list($insert_id,) = \DB::insert('games')->set(['name' => $new_game_name])->execute();
                    $games_id = $insert_id;  //新規ゲームIDを使用
                }
            }
            //paymentsテーブルに登録
            \DB::insert('payments')->set([
                'games_id' => $games_id,
                'payment_date' => \Input::post('payment_date'),
                'amount' => \Input::post('amount'),
            ])->execute();

            //合計金額更新
            $payment_date = \Input::post('payment_date');
            $year = (int)\Input::post('year');
            $month = (int)\Input::post('month');
            $this->update_monthly_total($year, $month);

            \Response::redirect("gamepaylog?year={$year}&month={$month}");
        }
    }

    //課金データの編集
    public function action_edit($id = null)
    {
        if ($id === null) {
            \Response::redirect('gamepaylog');
        }

        $payment = \DB::select()->from('payments')->where('id', $id)->execute()->current();
        if (!$payment) {
            \Response::redirect('gamepaylog');
        }

        $games = \DB::select('id', 'name')->from('games')->execute()->as_array();

        //年月取得(年月が未指定の場合、日付から取得)
        $year = (int) \Input::get('year');
        $month = (int) \Input::get('month');
        if (!$year || !$month) {
            $year = (int) date('Y', strtotime($payment['payment_date']));
            $month = (int) date('n', strtotime($payment['payment_date']));
        }

        return View::forge('gamepaylog/edit', [
            'payment' => $payment,
            'games'   => $games,
            'year'    => $year,
            'month'   => $month,
        ]);
    }

    //課金データ更新
    public function action_update($id = null)
    {
        if ($id === null || \Input::method() !== 'POST') {
            \Response::redirect('gamepaylog');
        }

        $val = \Validation::forge();
        $val->add_field('games_id', 'Game', 'required|valid_string[numeric]');
        $val->add_field('payment_date', 'Payment Date', 'required|valid_date');
        $val->add_field('amount', 'Amount', 'required|valid_string[numeric]');

        if ($val->run()) {
            \DB::update('payments')
                ->set([
                    'games_id' => \Input::post('games_id'),
                    'payment_date' => \Input::post('payment_date'),
                    'amount' => \Input::post('amount'),
                ])
                ->where('id', $id)
                ->execute();

            //年月取得
            $year = (int) \Input::post('year');
            $month = (int) \Input::post('month');

            $this->update_monthly_total($year, $month);

            \Response::redirect("gamepaylog?year={$year}&month={$month}");
        } else {
            //バリデーションエラーの場合、修正ページに戻る
            $payment = \DB::select()->from('payments')->where('id', $id)->execute()->current();
            $games = \DB::select('id', 'name')->from('games')->execute()->as_array();

            $year = (int) \Input::post('year');
            $month = (int) \Input::post('month');

            return View::forge('gamepaylog/edit', [
                'payment' => $payment,
                'games'   => $games,
                'errors'  => $val->error(),
                'year'    => $year,
                'month'   => $month,
            ]);
        }
    }


    //課金データの削除
    public function action_delete($id = null)
    {
        if ($id !== null) {
            //削除前に該当レコード取得
            $payment = \DB::select()->from('payments')->where('id', $id)->execute()->current();
            if ($payment) {
                $year = (int) \Input::get('year');
                $month = (int) \Input::get('month');

                //年月が未指定の場合、日付から取得
                if (!$year || !$month) {
                    $year = (int) date('Y', strtotime($payment['payment_date']));
                    $month = (int) date('n', strtotime($payment['payment_date']));
                }

                \DB::delete('payments')->where('id', $id)->execute();

                $this->update_monthly_total($year, $month);
            }
        }
        \Response::redirect("gamepaylog?year={$year}&month={$month}");
    }


    //目標課金額設定
    public function action_setgoal()
    {
        if (\Input::method() === 'POST') {
            $year = (int)\Input::post('year');
            $month = (int)\Input::post('month');
            $goal_amount = (int)\Input::post('goal_amount');

            //目標額をmonthly_goalsに登録
            list($result, $goals_id) = \DB::insert('monthly_goals')
                ->set(['goal_amount' => $goal_amount])
                ->execute();

            //monthly_paymentに登録
            \DB::insert('monthly_payment')
                ->set([
                    'goals_id' => $goals_id,
                    'year' => $year,
                    'month' => $month,
                    'total_amount' => 0,
                ])
                ->execute();

            \Response::redirect("gamepaylog?year={$year}&month={$month}");
        } else {
            $year = (int)\Input::get('year', date('Y'));
            $month = (int)\Input::get('month', date('n'));

            return View::forge('gamepaylog/setgoal', [
                'year' => $year,
                'month' => $month,
            ]);
        }
    }



    protected function update_monthly_total($year, $month)
    {
        //合計課金額を取得
        $total = \DB::select(\DB::expr('SUM(amount) as total'))
            ->from('payments')
            ->where(\DB::expr('YEAR(payment_date)'), '=', $year)
            ->and_where(\DB::expr('MONTH(payment_date)'), '=', $month)
            ->execute()
            ->get('total');

        $total = $total ?: 0;

        //更新
        \DB::update('monthly_payment')
            ->value('total_amount', $total)
            ->where('year', $year)
            ->and_where('month', $month)
            ->execute();
    }
}
