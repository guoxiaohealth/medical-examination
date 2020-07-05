<?php

namespace App\Model;


class Doctor extends Model
{
    protected $fillable = [
        'name', 'image', 'department_id', 'desc', 'meet'
    ];
}
