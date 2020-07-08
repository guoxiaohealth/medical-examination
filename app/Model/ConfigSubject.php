<?php

namespace App\Model;


class ConfigSubject extends Model
{
    protected $table = 'config_subjects';

    protected $fillable = [
        'mechanism_id', 'name', 'config_project_id'
    ];

    public function merits()
    {
        return $this->hasMany(ConfigMerit::class, 'config_subject_id', 'id');
    }
}
