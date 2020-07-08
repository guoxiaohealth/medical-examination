<?php

namespace App\Model;


class ConfigKind extends Model
{
    protected $table = 'config_kinds';

    protected $fillable = [
        'mechanism_id', 'name',
    ];

    public function projects()
    {
        return $this->hasMany(ConfigProject::class, 'config_kind_id', 'id');
    }
}
