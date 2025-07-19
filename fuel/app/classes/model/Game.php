<?php
namespace Model;

use Orm\Model;

class Game extends Model

{
    protected static $_table_name = 'games';
    protected static $_primary_key = ['id'];
    protected static $_properties = [
        'id',
        'name',
    ];
}