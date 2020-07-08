<?php

namespace App\Model;


class Mechanism extends Model
{
    protected $table = 'mechanisms';

    protected $fillable = [
        'name', 'remarks', 'print_msg',
    ];
}
