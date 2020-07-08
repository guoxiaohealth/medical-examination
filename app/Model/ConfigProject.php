<?php

namespace App\Model;


class ConfigProject extends Model
{
    protected $table = 'config_projects';

    protected $fillable = [
        'mechanism_id', 'name', 'config_kind_id'
    ];

    public function subjects()
    {
        return $this->hasMany(ConfigSubject::class, 'config_project_id', 'id');
    }
}
