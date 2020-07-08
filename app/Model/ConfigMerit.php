<?php

namespace App\Model;

class ConfigMerit extends Model
{
    protected $table = 'config_merits';

    protected $fillable = [
        'mechanism_id', 'config_subject_id', 'name', 'unit', 'range', 'type', 'expression'
    ];

    protected $casts = [
        'expression' => 'json'
    ];
}
