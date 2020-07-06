<?php

namespace App\Model;


class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'name', 'desc', 'parent_id'
    ];

    public function children()
    {
        return $this->hasMany(Permission::class, 'parent_id', 'id');
    }
}
