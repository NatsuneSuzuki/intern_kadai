<?php

namespace Model;

class Payment extends \Orm\Model
{
    protected static $_table_name = 'payments';

    protected static $_properties = [
        'id',
        'games_id',
        'payment_date',
        'amount',
    ];

    protected static $_belongs_to = [
        'game' => [
            'key_from' => 'games_id',
            'model_to' => 'Model\Game',
            'key_to' => 'id',
        ]
    ];
}
