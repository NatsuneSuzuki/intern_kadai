<?php

namespace Model;

class Monthlypayment extends \Orm\Model
{
    protected static $_table_name = 'monthly_payment';  


    protected static $_properties = [
        'id',
        'goals_id',
        'year',
        'month',
        'total_amount',
    ];

    protected static $_belongs_to = [
        'goal' => [
            'model_to' => 'Model\Monthlygoals',
            'key_from' => 'goals_id',
            'key_to' => 'id',
        ],
    ];
}
