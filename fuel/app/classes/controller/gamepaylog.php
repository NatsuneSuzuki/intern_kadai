<?php

use Model\Monthlygoals;
use Model\Monthlypayment;
use Model\Payment;
use Model\Game;

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

        $monthly_payment = Monthlypayment::find('first', [
            'where' => [
                ['year', $year],
                ['month', $month],
            ],
            'related' => ['goal'],
        ]);

        if (empty($monthly_payment)) {
            \Response::redirect("gamepaylog/setgoal?year={$year}&month={$month}");
        }

        $payments = Payment::query()
            ->related('game')
            ->where(\DB::expr('YEAR(payment_date)'), '=', $year)
            ->where(\DB::expr('MONTH(payment_date)'), '=', $month)
            ->order_by('payment_date', 'asc')
            ->get();

        $json_data = [];
        foreach ($payments as $p) {
            $json_data[] = [
                'id' => $p->id,
                'name' => $p->game ? $p->game->name : '（未登録）',
                'payment_date' => $p->payment_date,
                'amount' => $p->amount,
            ];
        }

        $json_data = json_encode($json_data, JSON_UNESCAPED_UNICODE);

        $game_sums = \DB::select('games.name', \DB::expr('SUM(payments.amount) AS total'))
            ->from('payments')
            ->join('games', 'LEFT')->on('payments.games_id', '=', 'games.id')
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
            'over_goal' => $monthly_payment->total_amount > $monthly_payment->goal->goal_amount,
        ]);
    }


    //課金データの追加
    public function action_create()
    {
        //ゲームのリストを取得（select）
        /*モデル使用 */
        $games = Game::find('all', [
            'select' => ['id', 'name'],
        ]);

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
                    //新規ゲームIDを使用*/
                    /* モデル使用時*/
                    $new_game = Game::forge([
                        'name' => $new_game_name,
                    ]);
                    $new_game->save();
                    $games_id = $new_game->id;
                }
            }

            // モデルで登録
            $payment = Payment::forge([
                'games_id' => $games_id,
                'payment_date' => \Input::post('payment_date'),
                'amount' => \Input::post('amount'),
            ]);
            $payment->save();


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


        $payment = Payment::find($id);

        if (!$payment) {
            \Response::redirect('gamepaylog');
        }


        $games = Game::find('all', [
            'select' => ['id', 'name'],
        ]);


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


            $payment = Payment::find($id);
            $payment->games_id = \Input::post('games_id');
            $payment->payment_date = \Input::post('payment_date');
            $payment->amount = \Input::post('amount');
            $payment->save();


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

            $payment = Payment::find($id);
            if ($payment) {
                $year = (int) date('Y', strtotime($payment->payment_date));
                $month = (int) date('n', strtotime($payment->payment_date));
                $payment->delete();
                $this->update_monthly_total($year, $month);
            }
        }
        //年月が未指定の場合、日付から取得
        if (!$year || !$month) {
            $year = (int) date('Y');
            $month = (int) date('n');
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
            /*モデル使用時 */
            $goal = Monthlygoals::forge([
                'goal_amount' => $goal_amount,
            ]);
            $goal->save();

            $monthly = Monthlypayment::forge([
                'goals_id' => $goal->id,
                'year' => $year,
                'month' => $month,
                'total_amount' => 0,
            ]);
            $monthly->save();

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
