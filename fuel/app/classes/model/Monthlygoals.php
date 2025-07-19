<?php

namespace Model;

class Monthlygoals extends \Orm\Model
{
    protected static $_table_name = 'monthly_goals';  

    protected static $_properties = [
        'id',
        'goal_amount',
    ];

    protected static $_has_one = [
        'monthlypayment' => [
            'model_to' => 'Model\Monthlypayment',
            'key_from' => 'id',
            'key_to' => 'goals_id',
        ],
    ];
}
